@extends('admin.layouts.app')
@section('title', 'ISP Dashboard')

@push('styles')
<style>
    .stat-card { border-left: 4px solid; }
    .stat-card.primary { border-left-color: #696cff; }
    .stat-card.success { border-left-color: #71dd37; }
    .stat-card.warning { border-left-color: #ffab00; }
    .stat-card.info { border-left-color: #03c3ec; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">ISP Dashboard</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">ISP Dashboard</li>
            </ol>
        </nav>
    </div>

    {{-- Summary Cards --}}
    <div class="col-sm-3 mb-4">
        <div class="card h-100 stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted small">Active Subscribers</p>
                        <h4 class="mb-0">{{ $activeSubscribers ?? 0 }}</h4>
                    </div>
                    <div class="text-primary"><i class="bx bx-user-check" style="font-size:2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3 mb-4">
        <div class="card h-100 stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted small">Today's Revenue (KES)</p>
                        <h4 class="mb-0">{{ number_format($todayRevenue ?? 0, 2) }}</h4>
                    </div>
                    <div class="text-success"><i class="bx bx-dollar-circle" style="font-size:2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3 mb-4">
        <div class="card h-100 stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted small">Active Sessions</p>
                        <h4 class="mb-0">{{ $activeSessions ?? 0 }}</h4>
                    </div>
                    <div class="text-warning"><i class="bx bx-wifi" style="font-size:2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3 mb-4">
        <div class="card h-100 stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted small">New Today</p>
                        <h4 class="mb-0">{{ $newToday ?? 0 }}</h4>
                    </div>
                    <div class="text-info"><i class="bx bx-user-plus" style="font-size:2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Chart --}}
    <div class="col-sm-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Revenue (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Package Distribution --}}
    <div class="col-sm-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Subscribers by Package</h5>
            </div>
            <div class="card-body">
                <canvas id="packageChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="col-sm-7 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Payments</h5>
                <a href="{{ route('admin.isp.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subscriber</th>
                                <th>Package</th>
                                <th>Amount (KES)</th>
                                <th>Method</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments ?? [] as $payment)
                            <tr>
                                <td>{{ $payment->subscriber->name ?? '-' }}</td>
                                <td>{{ $payment->package->name ?? '-' }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td><span class="badge bg-label-success">{{ $payment->payment_method ?? 'M-Pesa' }}</span></td>
                                <td>{{ $payment->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">No payments today</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Sessions --}}
    <div class="col-sm-5 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Sessions</h5>
                <a href="{{ route('admin.isp.sessions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>IP</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSessions ?? [] as $session)
                            <tr>
                                <td>{{ $session->username }}</td>
                                <td>{{ $session->framedipaddress ?? '-' }}</td>
                                <td>{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">No active sessions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const revenueLabels = @json($revenueChartLabels ?? []);
    const revenueData   = @json($revenueChartData ?? []);
    const packageLabels = @json($packageChartLabels ?? []);
    const packageData   = @json($packageChartData ?? []);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (KES)',
                data: revenueData,
                fill: true,
                backgroundColor: 'rgba(105,108,255,0.15)',
                borderColor: '#696cff',
                tension: 0.4,
                pointRadius: 3,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('packageChart'), {
        type: 'doughnut',
        data: {
            labels: packageLabels,
            datasets: [{
                data: packageData,
                backgroundColor: ['#696cff','#71dd37','#ffab00','#03c3ec','#ff3e1d','#20c997'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
@endpush
