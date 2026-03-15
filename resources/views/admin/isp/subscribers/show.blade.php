@extends('admin.layouts.app')
@section('title', 'Subscriber: ' . $subscriber->name)

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ $subscriber->name }}</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.subscribers.index') }}">Subscribers</a></li>
                        <li class="breadcrumb-item active">{{ $subscriber->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.isp.subscribers.edit', $subscriber) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.isp.subscribers.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Subscriber Info Cards --}}
    <div class="col-sm-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Account Details</h6>
                @if($subscriber->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted" width="40%">Name</th><td>{{ $subscriber->name }}</td></tr>
                    <tr><th class="text-muted">Username</th><td><code>{{ $subscriber->username }}</code></td></tr>
                    <tr><th class="text-muted">Phone</th><td>{{ $subscriber->phone ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ $subscriber->email ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Address</th><td>{{ $subscriber->address ?? '-' }}</td></tr>
                    <tr>
                        <th class="text-muted">Type</th>
                        <td>
                            <span class="badge bg-label-{{ $subscriber->type === 'pppoe' ? 'primary' : 'warning' }}">
                                {{ strtoupper($subscriber->type ?? 'N/A') }}
                            </span>
                        </td>
                    </tr>
                    <tr><th class="text-muted">Package</th><td>{{ $subscriber->package->name ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Router</th><td>{{ $subscriber->router->name ?? '-' }}</td></tr>
                    <tr><th class="text-muted">Static IP</th><td>{{ $subscriber->static_ip ?? '<span class="text-muted">Dynamic</span>' }}</td></tr>
                    <tr>
                        <th class="text-muted">Expires At</th>
                        <td>
                            @if($subscriber->expires_at)
                                @if($subscriber->expires_at->isPast())
                                    <span class="text-danger"><i class="bx bx-error-circle"></i> {{ $subscriber->expires_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-success">{{ $subscriber->expires_at->format('d M Y H:i') }}</span>
                                    <small class="text-muted d-block">{{ $subscriber->expires_at->diffForHumans() }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th class="text-muted">Created</th><td>{{ $subscriber->created_at->format('d M Y H:i') }}</td></tr>
                </table>
                @if($subscriber->notes)
                <hr>
                <p class="text-muted small mb-1">Notes:</p>
                <p class="mb-0">{{ $subscriber->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-sm-7 mb-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('admin.isp.subscribers.renew'))
                    <form action="{{ route('admin.isp.subscribers.renew', $subscriber) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-refresh me-1"></i> Renew Subscription
                        </button>
                    </form>
                    @endif
                    @if(Route::has('admin.isp.subscribers.toggle'))
                    <form action="{{ route('admin.isp.subscribers.toggle', $subscriber) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn {{ $subscriber->is_active ? 'btn-warning' : 'btn-outline-success' }}">
                            <i class="bx bx-{{ $subscriber->is_active ? 'pause' : 'play' }} me-1"></i>
                            {{ $subscriber->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    @endif
                    @if(Route::has('admin.isp.subscribers.disconnect'))
                    <form action="{{ route('admin.isp.subscribers.disconnect', $subscriber) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bx bx-wifi-off me-1"></i> Disconnect Session
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('admin.isp.subscribers.edit', $subscriber) }}" class="btn btn-outline-primary">
                        <i class="bx bx-edit me-1"></i> Edit Subscriber
                    </a>
                </div>
            </div>
        </div>

        {{-- Package info --}}
        @if($subscriber->package)
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Current Package</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3 border-end">
                        <div class="text-muted small">Price</div>
                        <div class="fw-bold">KES {{ number_format($subscriber->package->price, 2) }}</div>
                    </div>
                    <div class="col-3 border-end">
                        <div class="text-muted small">Speed ↑</div>
                        <div class="fw-bold">{{ $subscriber->package->speed_upload }} Mbps</div>
                    </div>
                    <div class="col-3 border-end">
                        <div class="text-muted small">Speed ↓</div>
                        <div class="fw-bold">{{ $subscriber->package->speed_download }} Mbps</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Validity</div>
                        <div class="fw-bold">{{ $subscriber->package->validity_days }}d</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Payment History --}}
    <div class="col-sm-7 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Payment History</h6>
                <span class="badge bg-label-primary">{{ $payments->count() ?? 0 }} records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Amount (KES)</th>
                                <th>Method</th>
                                <th>Ref / Trans ID</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments ?? [] as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method ?? 'M-Pesa' }}</td>
                                <td><code>{{ $payment->transaction_id ?? '-' }}</code></td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-label-success">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-label-warning">Pending</span>
                                    @else
                                        <span class="badge bg-label-danger">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">No payment records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions --}}
    <div class="col-sm-5 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Sessions</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Framed IP</th>
                                <th>Duration</th>
                                <th>Start</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions ?? [] as $session)
                            <tr>
                                <td><code>{{ $session->framedipaddress ?? '-' }}</code></td>
                                <td>{{ gmdate('H:i:s', $session->acctsessiontime ?? 0) }}</td>
                                <td>{{ isset($session->acctstarttime) ? \Carbon\Carbon::parse($session->acctstarttime)->format('d M H:i') : '-' }}</td>
                                <td>
                                    @if(!$session->acctstoptime)
                                        <span class="badge bg-label-success">Online</span>
                                    @else
                                        <span class="badge bg-label-secondary">Offline</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No sessions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="col-sm-12">
        <div class="card border-danger">
            <div class="card-header text-danger"><h6 class="mb-0"><i class="bx bx-error-circle me-1"></i> Danger Zone</h6></div>
            <div class="card-body">
                <p class="mb-3 text-muted">Permanently delete this subscriber and all associated data.</p>
                <form action="{{ route('admin.isp.subscribers.destroy', $subscriber) }}" method="POST"
                      onsubmit="return confirm('Permanently delete subscriber &quot;{{ addslashes($subscriber->name) }}&quot;?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Delete Subscriber
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
