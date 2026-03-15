<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\MpesaPayment;
use App\Models\Radacct;
use App\Models\IspPackage;
use App\Models\Router;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Active users (PPPoE + Hotspot)
        $activeUsers = Subscriber::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count();

        // Revenue stats
        $todayRevenue  = MpesaPayment::where('status', 'completed')->whereDate('created_at', today())->sum('amount');
        $weekRevenue   = MpesaPayment::where('status', 'completed')->where('created_at', '>=', now()->startOfWeek())->sum('amount');
        $monthRevenue  = MpesaPayment::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount');

        // Active sessions
        $activeSessions = Radacct::whereNull('acctstoptime')->count();

        // New registrations today
        $newToday = Subscriber::whereDate('created_at', today())->count();

        // Recent payments
        $recentPayments = MpesaPayment::with('package')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent sessions
        $recentSessions = Radacct::whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->limit(10)
            ->get();

        // Revenue chart (last 30 days)
        $revenueChart = MpesaPayment::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[]   = $revenueChart[$date]->total ?? 0;
        }

        // Sessions by router
        $sessionsByRouter = Radacct::whereNull('acctstoptime')
            ->selectRaw('nasipaddress, COUNT(*) as count')
            ->groupBy('nasipaddress')
            ->get();

        // Map NAS IP to router name
        $routers = Router::pluck('name', 'wan_ip');
        $pieLabels = $sessionsByRouter->map(fn($r) => $routers[$r->nasipaddress] ?? $r->nasipaddress)->values();
        $pieData   = $sessionsByRouter->pluck('count')->values();

        return view('admin.isp.dashboard', compact(
            'activeUsers', 'todayRevenue', 'weekRevenue', 'monthRevenue',
            'activeSessions', 'newToday', 'recentPayments', 'recentSessions',
            'chartLabels', 'chartData', 'pieLabels', 'pieData'
        ));
    }
}
