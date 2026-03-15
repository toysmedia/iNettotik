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
        $validated['wan_ip']             = null;
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
