<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\IspPackage;
use App\Models\Router;
use App\Models\AuditLog;
use App\Models\Radacct;
use App\Models\MpesaPayment;
use App\Services\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function __construct(protected RadiusService $radius) {}

    public function index(Request $request)
    {
        $query = Subscriber::with(['package', 'router']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('username', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type')) $query->where('connection_type', $request->type);

        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        return view('admin.isp.subscribers.index', compact('subscribers'));
    }

    public function pppoe(Request $request)
    {
        $request->merge(['type' => 'pppoe']);
        return $this->index($request);
    }

    public function hotspot(Request $request)
    {
        $request->merge(['type' => 'hotspot']);
        return $this->index($request);
    }

    public function create()
    {
        $packages = IspPackage::where('is_active', true)->orderBy('price')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.create', compact('packages', 'routers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'required|string|max:20',
            'username'        => 'required|string|max:64|unique:subscribers,username',
            'password'        => 'required|string|min:6|max:64',
            'isp_package_id'  => 'nullable|exists:isp_packages,id',
            'router_id'       => 'nullable|exists:routers,id',
            'connection_type' => 'required|in:pppoe,hotspot',
            'status'          => 'required|in:active,suspended,expired',
            'expires_at'      => 'nullable|date',
        ]);

        $plainPassword = $data['password'];
        $data['password_hash']    = bcrypt($plainPassword);
        $data['radius_password']  = encrypt($plainPassword);
        $data['created_by']       = 'admin';
        unset($data['password']);

        $subscriber = Subscriber::create($data);

        // Provision RADIUS
        if ($subscriber->isp_package_id) {
            $this->radius->provisionUser($subscriber->username, $plainPassword, $subscriber->package);
        }

        AuditLog::record('subscriber.created', Subscriber::class, $subscriber->id, [], $subscriber->only(['name','username','phone','status']));

        return redirect()->route('admin.isp.subscribers.index')->with('success', "Subscriber '{$subscriber->name}' created.");
    }

    public function show(Subscriber $subscriber)
    {
        $subscriber->load(['package', 'router', 'payments']);

        // Usage stats from radacct
        $radacctStats = Radacct::where('username', $subscriber->username)
            ->selectRaw('
                SUM(acctoutputoctets) as total_download,
                SUM(acctinputoctets)  as total_upload,
                SUM(acctsessiontime)  as total_time,
                COUNT(*)             as total_sessions
            ')
            ->first();

        // Session history
        $sessions = Radacct::where('username', $subscriber->username)
            ->orderBy('acctstarttime', 'desc')
            ->limit(20)
            ->get();

        // Payment history
        $payments = MpesaPayment::where('phone', $subscriber->phone)
            ->orWhere('transaction_desc', 'like', '%' . $subscriber->username . '%')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Active session
        $activeSession = Radacct::where('username', $subscriber->username)
            ->whereNull('acctstoptime')
            ->first();

        // Usage chart data (last 30 days)
        $chartData = $this->getUsageChartData($subscriber->username);

        return view('admin.isp.subscribers.show', compact(
            'subscriber', 'sessions', 'payments', 'radacctStats', 'activeSession', 'chartData'
        ));
    }

    public function usageData(Subscriber $subscriber)
    {
        $data = $this->getUsageChartData($subscriber->username);
        return response()->json($data);
    }

    protected function getUsageChartData(string $username): array
    {
        $labels   = [];
        $download = [];
        $upload   = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M d');

            $stats = Radacct::where('username', $username)
                ->whereDate('acctstarttime', $date)
                ->selectRaw('SUM(acctoutputoctets) as dl, SUM(acctinputoctets) as ul')
                ->first();

            $download[] = round(($stats->dl ?? 0) / 1048576, 2); // MB
            $upload[]   = round(($stats->ul ?? 0) / 1048576, 2); // MB
        }

        return compact('labels', 'download', 'upload');
    }

    public function edit(Subscriber $subscriber)
    {
        $packages = IspPackage::where('is_active', true)->orderBy('price')->get();
        $routers  = Router::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.subscribers.edit', compact('subscriber', 'packages', 'routers'));
    }

    public function update(Request $request, Subscriber $subscriber)
    {
        $old = $subscriber->only(['name','username','phone','status','isp_package_id']);
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'required|string|max:20',
            'username'        => "required|string|max:64|unique:subscribers,username,{$subscriber->id}",
            'password'        => 'nullable|string|min:6|max:64',
            'isp_package_id'  => 'nullable|exists:isp_packages,id',
            'router_id'       => 'nullable|exists:routers,id',
            'connection_type' => 'required|in:pppoe,hotspot',
            'status'          => 'required|in:active,suspended,expired',
            'expires_at'      => 'nullable|date',
        ]);

        $plainPassword = null;
        if (!empty($data['password'])) {
            $plainPassword = $data['password'];
            $data['password_hash']   = bcrypt($plainPassword);
            $data['radius_password'] = encrypt($plainPassword);
        }
        unset($data['password']);

        $subscriber->update($data);

        // Re-provision RADIUS if package or password changed
        if ($subscriber->isp_package_id) {
            $radPass = $plainPassword ?? decrypt($subscriber->radius_password);
            if ($data['status'] === 'suspended') {
                $this->radius->suspendUser($subscriber->username);
            } else {
                $this->radius->provisionUser($subscriber->username, $radPass, $subscriber->package);
            }
        }

        AuditLog::record('subscriber.updated', Subscriber::class, $subscriber->id, $old, $subscriber->fresh()->only(['name','username','phone','status']));
        return redirect()->route('admin.isp.subscribers.index')->with('success', 'Subscriber updated.');
    }

    public function destroy(Subscriber $subscriber)
    {
        // Check for related records
        $paymentCount = MpesaPayment::where('phone', $subscriber->phone)->count();
        $sessionCount = Radacct::where('username', $subscriber->username)->count();

        if ($paymentCount > 0 || $sessionCount > 0) {
            // Only soft-delete if there are related records
            $this->radius->removeUser($subscriber->username);
            AuditLog::record('subscriber.deleted', Subscriber::class, $subscriber->id, $subscriber->toArray(), []);
            $subscriber->update(['status' => 'suspended']);
            $subscriber->delete();
            return redirect()->route('admin.isp.subscribers.index')
                ->with('success', "Subscriber deleted (had {$paymentCount} payments, {$sessionCount} sessions - records preserved).");
        }

        $this->radius->removeUser($subscriber->username);
        AuditLog::record('subscriber.deleted', Subscriber::class, $subscriber->id, $subscriber->toArray(), []);
        $subscriber->delete();
        return redirect()->route('admin.isp.subscribers.index')->with('success', 'Subscriber deleted.');
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:suspend,activate,delete',
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
        ]);

        $subscribers = Subscriber::whereIn('id', $data['ids'])->get();

        foreach ($subscribers as $subscriber) {
            match ($data['action']) {
                'suspend'  => $this->radius->suspendUser($subscriber->username) && $subscriber->update(['status' => 'suspended']),
                'activate' => $subscriber->update(['status' => 'active']),
                'delete'   => $this->radius->removeUser($subscriber->username) ?: $subscriber->delete(),
                default    => null,
            };
        }

        return back()->with('success', ucfirst($data['action']) . ' applied to ' . count($data['ids']) . ' subscribers.');
    }
}

