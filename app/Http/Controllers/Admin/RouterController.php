<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Nas;
use App\Models\AuditLog;
use App\Services\MikrotikScriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class RouterController extends Controller
{
    public function __construct(protected MikrotikScriptService $scriptService) {}

    public function index()
    {
        $routers = Router::orderBy('name')->paginate(15);
        return view('admin.isp.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('admin.isp.routers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'wan_ip'             => 'required|ip',
            'radius_secret'      => 'required|string|max:100',
            'wan_interface'      => 'required|string|max:50',
            'customer_interface' => 'required|string|max:50',
            'pppoe_pool_range'   => 'required|string',
            'hotspot_pool_range' => 'required|string',
            'billing_domain'     => 'nullable|string|max:255',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $router = Router::create($data);

        // Sync to NAS table
        $this->syncNas($router);

        AuditLog::record('router.created', Router::class, $router->id, [], $router->toArray());

        return redirect()->route('admin.isp.routers.index')->with('success', "Router '{$router->name}' created.");
    }

    public function edit(Router $router)
    {
        return view('admin.isp.routers.edit', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $old = $router->toArray();
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'wan_ip'             => 'required|ip',
            'radius_secret'      => 'required|string|max:100',
            'wan_interface'      => 'required|string|max:50',
            'customer_interface' => 'required|string|max:50',
            'pppoe_pool_range'   => 'required|string',
            'hotspot_pool_range' => 'required|string',
            'billing_domain'     => 'nullable|string|max:255',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $router->update($data);
        $this->syncNas($router);

        AuditLog::record('router.updated', Router::class, $router->id, $old, $router->fresh()->toArray());

        return redirect()->route('admin.isp.routers.index')->with('success', "Router '{$router->name}' updated.");
    }

    public function destroy(Router $router)
    {
        AuditLog::record('router.deleted', Router::class, $router->id, $router->toArray(), []);
        Nas::where('nasname', $router->wan_ip)->delete();
        $router->delete();
        return redirect()->route('admin.isp.routers.index')->with('success', 'Router deleted.');
    }

    public function show(Router $router)
    {
        return view('admin.isp.routers.show', compact('router'));
    }

    public function script(Router $router)
    {
        $script = $this->scriptService->generate($router);
        return view('admin.isp.routers.mikrotik_script', compact('router', 'script'));
    }

    public function downloadScript(Router $router)
    {
        $script = $this->scriptService->generate($router);
        $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $router->name)) . '-mikrotik.rsc';
        return response($script, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function testConnection(Router $router)
    {
        $host     = $router->vpn_ip ?: $router->wan_ip;
        $port     = $router->api_port ?? 80;
        $username = $router->api_username ?? 'admin';
        $password = $router->api_password ?? '';

        $apiReachable   = false;
        $routerIdentity = null;
        $uptime         = null;
        $version        = null;
        $error          = null;

        try {
            $url      = "http://{$host}:{$port}/rest/system/resource";
            $response = Http::withBasicAuth($username, $password)
                ->timeout(5)
                ->get($url);

            if ($response->successful()) {
                $apiReachable   = true;
                $data           = $response->json();
                $uptime         = $data['uptime'] ?? null;
                $version        = $data['version'] ?? null;
            } else {
                $error = 'HTTP ' . $response->status() . ': ' . $response->reason();
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        // Check for router identity via REST if reachable
        if ($apiReachable) {
            try {
                $idResp = Http::withBasicAuth($username, $password)
                    ->timeout(5)
                    ->get("http://{$host}:{$port}/rest/system/identity");
                if ($idResp->successful()) {
                    $routerIdentity = $idResp->json('name');
                }
            } catch (\Exception $e) {
                // ignore — identity is optional
            }
        }

        // Check NAS table for RADIUS configuration
        $radiusConfigured = Nas::where('nasname', $router->wan_ip)->exists();

        return response()->json([
            'api_reachable'    => $apiReachable,
            'radius_configured' => $radiusConfigured,
            'router_identity'  => $routerIdentity,
            'uptime'           => $uptime,
            'version'          => $version,
            'error'            => $error,
        ]);
    }

    public function downloadHotspotFiles(Router $router)
    {
        $tmpDir  = sys_get_temp_dir();
        $zipPath = $tmpDir . '/hotspot-' . $router->id . '-' . time() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create ZIP file.');
        }

        $billingDomain = $router->billing_domain ?: parse_url(config('app.url'), PHP_URL_HOST);
        $appName       = config('app.name', 'iNettotik');

        $zip->addFromString('login.html',   view('hotspot.login',   compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('alogin.html',  view('hotspot.alogin',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('status.html',  view('hotspot.status',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->close();

        return response()->download($zipPath, "hotspot-files-{$router->id}.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    protected function syncNas(Router $router): void
    {
        Nas::updateOrCreate(
            ['nasname' => $router->wan_ip],
            [
                'shortname'   => $router->name,
                'type'        => 'other',
                'secret'      => $router->radius_secret,
                'description' => $router->name . ' - MikroTik',
            ]
        );
    }
}
