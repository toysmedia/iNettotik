@extends('admin.layouts.app')
@section('title', 'Routers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Routers</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item active">Routers</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.isp.routers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Router
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="routersTable" class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>REF</th>
                                <th>IDENTITY</th>
                                <th>MODEL</th>
                                <th>VERSION</th>
                                <th>WAN / VPN IP</th>
                                <th>STATUS</th>
                                <th>CONNECTION</th>
                                <th>WEB</th>
                                <th>WINBOX</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routers as $router)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $router->ref_code ?? 'RTR-' . str_pad($router->id, 3, '0', STR_PAD_LEFT) }}</code></td>
                                <td><strong>{{ $router->name }}</strong></td>
                                <td>{{ $router->model ?? '-' }}</td>
                                <td>{{ $router->routeros_version ?? '-' }}</td>
                                <td>{{ $router->vpn_ip ?? ($router->wan_ip ?? '-') }}</td>
                                <td>
                                    @if($router->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $connIp = $router->vpn_ip ?: $router->wan_ip; @endphp
                                    @if($connIp)
                                        <span class="connection-badge" id="conn-{{ $router->id }}"
                                              data-url="{{ route('admin.isp.routers.test_connection', $router) }}"
                                              data-router-id="{{ $router->id }}">
                                            <span class="badge bg-label-secondary">
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                Checking…
                                            </span>
                                        </span>
                                    @else
                                        <span class="badge bg-label-secondary">No IP</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($router->wan_ip)
                                        <a href="http://{{ $router->wan_ip }}" target="_blank" class="btn btn-sm btn-outline-info" title="Open Web UI">
                                            <i class="bx bx-link-external"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($router->wan_ip)
                                        <code>winbox://{{ $router->wan_ip }}</code>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.edit', $router) }}">
                                                    <i class="bx bx-edit me-2 text-primary"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.script', $router) }}" target="_blank">
                                                    <i class="bx bx-code-alt me-2 text-warning"></i> Generate Script
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.download_script', $router) }}">
                                                    <i class="bx bx-download me-2 text-success"></i> Download Script
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.isp.routers.hotspot_files', $router) }}">
                                                    <i class="bx bx-wifi me-2 text-info"></i> Download Hotspot Files
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-success"
                                                        onclick="testConnection({{ $router->id }}, {{ json_encode($router->name) }}, '{{ route('admin.isp.routers.test_connection', $router) }}')">
                                                    <i class="bx bx-broadcast me-2"></i> Test Connection
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.isp.routers.destroy', $router) }}" method="POST"
                                                      onsubmit="return confirm('Delete router {{ addslashes($router->name) }}? This cannot be undone.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    No routers found. <a href="{{ route('admin.isp.routers.create') }}">Add one now.</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Test Connection Modal --}}
<div class="modal fade" id="testConnModal" tabindex="-1" aria-labelledby="testConnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnModalLabel">Test Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="testConnBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#routersTable').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [7, 8, 9, 10] }]
    });

    // Initial connection check + auto-refresh every 30 seconds
    checkAllConnectionStatuses();
    setInterval(checkAllConnectionStatuses, 30000);
});

function checkAllConnectionStatuses() {
    $('.connection-badge[data-url]').each(function () {
        var $badge = $(this);
        var url    = $badge.data('url');
        var e = function(s) {
            return $('<div>').text(s != null ? String(s) : '').html();
        };
        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                if (res.api_reachable) {
                    var label = '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Online</span>';
                    if (res.version) {
                        label += '<br><small class="text-muted">' + e(res.version) + '</small>';
                    }
                    if (res.uptime) {
                        label += '<br><small class="text-muted">' + e(res.uptime) + '</small>';
                    }
                    $badge.html(label);
                } else {
                    var errTip = res.error ? e(res.error) : 'Unreachable';
                    $badge.html('<span class="badge bg-label-danger" title="' + errTip + '"><i class="bx bx-x-circle me-1"></i>Offline</span>');
                }
            },
            error: function () {
                $badge.html('<span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>Offline</span>');
            }
        });
    });
}

function testConnection(routerId, routerName, url) {
    $('#testConnModalLabel').text('Test Connection — ' + routerName);
    $('#testConnBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Testing...</span></div><p class="mt-2 text-muted">Connecting to router…</p></div>');
    var modal = new bootstrap.Modal(document.getElementById('testConnModal'));
    modal.show();

    $.ajax({
        url: url,
        type: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            var e = function(s) {
                return $('<div>').text(s != null ? String(s) : '').html();
            };
            var html = '<ul class="list-group">';
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                  + '<span><i class="bx bx-wifi me-2"></i>API Reachable</span>'
                  + (res.api_reachable
                      ? '<span class="badge bg-success">Yes</span>'
                      : '<span class="badge bg-danger">No</span>')
                  + '</li>';
            html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                  + '<span><i class="bx bx-server me-2"></i>RADIUS Configured</span>'
                  + (res.radius_configured
                      ? '<span class="badge bg-success">Yes</span>'
                      : '<span class="badge bg-warning text-dark">Not in NAS table</span>')
                  + '</li>';
            if (res.router_identity) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-chip me-2"></i>Board</span>'
                      + '<span class="text-muted">' + e(res.router_identity) + '</span></li>';
            }
            if (res.version) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-code-alt me-2"></i>RouterOS</span>'
                      + '<span class="text-muted">' + e(res.version) + '</span></li>';
            }
            if (res.uptime) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                      + '<span><i class="bx bx-time me-2"></i>Uptime</span>'
                      + '<span class="text-muted">' + e(res.uptime) + '</span></li>';
            }
            html += '</ul>';
            if (res.error) {
                html += '<div class="alert alert-warning mt-3 mb-0"><i class="bx bx-info-circle me-1"></i>' + e(res.error) + '</div>';
            }
            $('#testConnBody').html(html);
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unknown error';
            $('#testConnBody').html('<div class="alert alert-danger mb-0"><i class="bx bx-error me-1"></i>Request failed: ' + $('<div>').text(msg).html() + '</div>');
        }
    });
}
</script>
@endpush
