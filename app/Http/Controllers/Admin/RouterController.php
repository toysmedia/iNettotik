<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Nas;
use App\Models\AuditLog;
use App\Models\IspSetting;
use App\Services\MikrotikScriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class RouterController extends Controller
{
    public function __construct(protected MikrotikScriptService $scriptService) {}

    public function index()
    {
        $routers = Router::orderBy('name')->get();
        return view('admin.isp.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('admin.isp.routers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'model'            => 'nullable|string|max:100',
            'routeros_version' => 'nullable|string|max:50',
            'wan_ip'           => 'nullable|ip',
            'vpn_ip'           => 'nullable|ip',
            'notes'            => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        // Auto-generate fields (pool ranges updated after creation with real ID)
        $validated['radius_secret']      = Str::random(16);
        $validated['wan_interface']      = 'ether1';
        $validated['customer_interface'] = 'bridge1';
        $validated['pppoe_pool_range']   = '10.10.1.1-10.10.1.254';
        $validated['hotspot_pool_range'] = '10.20.1.1-10.20.1.254';
        $validated['billing_domain']     = IspSetting::getValue('billing_domain', '');
        $validated['is_active']          = $request->boolean('is_active', true);

        $router = Router::create($validated);

        // Set ref_code after we know the real ID
        // Ensure IP octet is valid (1–254); use modulo to wrap if ID exceeds 254
        $octet = (($router->id - 1) % 254) + 1;
        $router->update([
            'ref_code'           => 'RTR-' . str_pad($router->id, 3, '0', STR_PAD_LEFT),
            'pppoe_pool_range'   => "10.10.{$octet}.1-10.10.{$octet}.254",
            'hotspot_pool_range' => "10.20.{$octet}.1-10.20.{$octet}.254",
        ]);

        if ($router->wan_ip) {
            $this->syncNas($router);
        }

        AuditLog::record('router.created', Router::class, $router->id, [], $router->fresh()->toArray());

        return redirect()->route('admin.isp.routers.index')
            ->with('success', "Router '{$router->name}' created successfully.");
    }

    public function edit(Router $router)
    {
        return view('admin.isp.routers.edit', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $old  = $router->toArray();
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'model'              => 'nullable|string|max:100',
            'routeros_version'   => 'nullable|string|max:50',
            'wan_ip'             => 'nullable|ip',
            'vpn_ip'             => 'nullable|ip',
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

        if ($router->wan_ip) {
            $this->syncNas($router);
        }

        AuditLog::record('router.updated', Router::class, $router->id, $old, $router->fresh()->toArray());

        return redirect()->route('admin.isp.routers.index')
            ->with('success', "Router '{$router->name}' updated.");
    }

    public function destroy(Router $router)
    {
        AuditLog::record('router.deleted', Router::class, $router->id, $router->toArray(), []);
        if ($router->wan_ip) {
            Nas::where('nasname', $router->wan_ip)->delete();
        }
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
        $script   = $this->scriptService->generate($router);
        $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $router->name)) . '-mikrotik.rsc';
        return response($script, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
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

        $zip->addFromString('login.html',  view('hotspot.login',  compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('alogin.html', view('hotspot.alogin', compact('router', 'billingDomain', 'appName'))->render());
        $zip->addFromString('status.html', view('hotspot.status', compact('router', 'billingDomain', 'appName'))->render());
        $zip->close();

        return response()->download($zipPath, "hotspot-files-{$router->id}.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function testConnection(Router $router)
    {
        $result = [
            'api_reachable'      => false,
            'radius_configured'  => false,
            'router_identity'    => null,
            'uptime'             => null,
            'version'            => null,
            'error'              => null,
        ];

        // Check NAS / RADIUS configuration
        $result['radius_configured'] = Nas::where('nasname', $router->wan_ip)->exists();

        // Try to reach RouterOS REST API
        $ip      = $router->vpn_ip ?: $router->wan_ip;
        $apiPort = $router->api_port ?? 80;
        $apiUser = $router->api_username ?? 'admin';
        $apiPass = $router->api_password ?? '';

        if (!$ip) {
            $result['error'] = 'No IP address configured for this router (set WAN IP or VPN IP).';
            return response()->json($result);
        }

        try {
            $scheme   = ($apiPort == 443) ? 'https' : 'http';
            $url      = "{$scheme}://{$ip}:{$apiPort}/rest/system/resource";
            $response = Http::withBasicAuth($apiUser, $apiPass)
                ->timeout(5)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $result['api_reachable']   = true;
                $result['router_identity'] = $data['board-name'] ?? null;
                $result['uptime']          = $data['uptime'] ?? null;
                $result['version']         = $data['version'] ?? null;
            } else {
                $result['error'] = 'API responded with HTTP ' . $response->status() . '. Check credentials or API port.';
            }
        } catch (\Exception $e) {
            $result['error'] = 'Could not connect to router API: ' . $e->getMessage();
        }

        return response()->json($result);
    }

    protected function syncNas(Router $router): void
    {
        Nas::updateOrCreate(
            ['nasname' => $router->wan_ip],
            [
                'shortname'   => $router->name,
                'type'        => 'mikrotik',
                'secret'      => $router->radius_secret,
                'description' => $router->name . ' - MikroTik',
            ]
        );
    }
}
