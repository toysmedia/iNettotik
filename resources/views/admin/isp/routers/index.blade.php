@extends('admin.layouts.app')
@section('title', 'Routers')

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

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>WAN IP</th>
                                <th>WAN Interface</th>
                                <th>Customer Interface</th>
                                <th>Billing Domain</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routers as $router)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $router->name }}</strong></td>
                                <td><code>{{ $router->wan_ip }}</code></td>
                                <td>{{ $router->wan_interface }}</td>
                                <td>{{ $router->customer_interface }}</td>
                                <td>{{ $router->billing_domain ?? '-' }}</td>
                                <td>
                                    @if($router->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.isp.routers.show', $router) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('admin.isp.routers.edit', $router) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.isp.routers.script', $router) }}" class="btn btn-sm btn-outline-warning me-1" title="Generate Script" target="_blank">
                                        <i class="bx bx-code-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.isp.routers.hotspot_files', $router) }}" class="btn btn-sm btn-outline-secondary me-1" title="Download Hotspot Files">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-success me-1 btn-test-connection"
                                            data-router-id="{{ $router->id }}"
                                            data-router-name="{{ $router->name }}"
                                            data-url="{{ route('admin.isp.routers.test_connection', $router) }}"
                                            title="Test Connection">
                                        <i class="bx bx-wifi"></i>
                                    </button>
                                    <form action="{{ route('admin.isp.routers.destroy', $router) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete router {{ addslashes($router->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No routers found. <a href="{{ route('admin.isp.routers.create') }}">Add one now.</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($routers instanceof \Illuminate\Pagination\LengthAwarePaginator && $routers->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $routers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Test Connection Modal --}}
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">
                    <i class="bx bx-wifi me-1"></i> Test Connection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="tcRouterName"></p>
                <div id="tcLoading" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Testing connection…</p>
                </div>
                <div id="tcResults" class="d-none">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted" width="45%">API Reachable</th>
                                <td id="tcApiStatus"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">RADIUS Configured</th>
                                <td id="tcRadiusStatus"></td>
                            </tr>
                            <tr id="tcIdentityRow">
                                <th class="text-muted">Router Identity</th>
                                <td id="tcIdentity"></td>
                            </tr>
                            <tr id="tcVersionRow">
                                <th class="text-muted">RouterOS Version</th>
                                <td id="tcVersion"></td>
                            </tr>
                            <tr id="tcUptimeRow">
                                <th class="text-muted">Uptime</th>
                                <td id="tcUptime"></td>
                            </tr>
                            <tr id="tcErrorRow" class="d-none">
                                <th class="text-muted text-danger">Error</th>
                                <td id="tcError" class="text-danger small"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-test-connection').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var routerName = this.dataset.routerName;
            var url        = this.dataset.url;
            var modal      = new bootstrap.Modal(document.getElementById('testConnectionModal'));

            document.getElementById('tcRouterName').textContent = 'Router: ' + routerName;
            document.getElementById('tcLoading').classList.remove('d-none');
            document.getElementById('tcResults').classList.add('d-none');
            modal.show();

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                document.getElementById('tcLoading').classList.add('d-none');
                document.getElementById('tcResults').classList.remove('d-none');

                document.getElementById('tcApiStatus').innerHTML = data.api_reachable
                    ? '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Online</span>'
                    : '<span class="badge bg-danger"><i class="bx bx-x me-1"></i>Unreachable</span>';

                document.getElementById('tcRadiusStatus').innerHTML = data.radius_configured
                    ? '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Configured</span>'
                    : '<span class="badge bg-warning text-dark"><i class="bx bx-error me-1"></i>Not Found in NAS</span>';

                document.getElementById('tcIdentity').textContent  = data.router_identity || '—';
                document.getElementById('tcVersion').textContent   = data.version || '—';
                document.getElementById('tcUptime').textContent    = data.uptime || '—';

                if (data.error) {
                    document.getElementById('tcErrorRow').classList.remove('d-none');
                    document.getElementById('tcError').textContent = data.error;
                } else {
                    document.getElementById('tcErrorRow').classList.add('d-none');
                }
            })
            .catch(function (err) {
                document.getElementById('tcLoading').classList.add('d-none');
                document.getElementById('tcResults').classList.remove('d-none');
                document.getElementById('tcApiStatus').innerHTML = '<span class="badge bg-danger">Error</span>';
                document.getElementById('tcErrorRow').classList.remove('d-none');
                document.getElementById('tcError').textContent = err.message;
            });
        });
    });
});
</script>
@endpush
