@extends('admin.layouts.app')
@section('title', 'Router: ' . $router->name)

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Router: {{ $router->name }}</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.routers.index') }}">Routers</a></li>
                        <li class="breadcrumb-item active">{{ $router->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-test-connection"
                        data-router-id="{{ $router->id }}"
                        data-router-name="{{ $router->name }}"
                        data-url="{{ route('admin.isp.routers.test_connection', $router) }}">
                    <i class="bx bx-wifi me-1"></i> Test Connection
                </button>
                <a href="{{ route('admin.isp.routers.script', $router) }}" class="btn btn-warning" target="_blank">
                    <i class="bx bx-code-alt me-1"></i> Generate Script
                </a>
                <a href="{{ route('admin.isp.routers.hotspot_files', $router) }}" class="btn btn-secondary">
                    <i class="bx bx-download me-1"></i> Hotspot Files
                </a>
                <a href="{{ route('admin.isp.routers.edit', $router) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Router Details --}}
    <div class="col-sm-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Basic Information</h6>
                @if($router->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" width="40%">Name</th>
                        <td>{{ $router->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">WAN IP</th>
                        <td><code>{{ $router->wan_ip }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">RADIUS Secret</th>
                        <td>
                            <span id="secretMask">••••••••</span>
                            <button class="btn btn-sm btn-link p-0 ms-2" onclick="toggleSecret()">Show</button>
                            <span id="secretValue" class="d-none"><code>{{ $router->radius_secret }}</code></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Billing Domain</th>
                        <td>{{ $router->billing_domain ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created</th>
                        <td>{{ $router->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Updated</th>
                        <td>{{ $router->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Network Config --}}
    <div class="col-sm-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Network Configuration</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" width="40%">WAN Interface</th>
                        <td><code>{{ $router->wan_interface }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Customer Interface</th>
                        <td><code>{{ $router->customer_interface }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">PPPoE Pool</th>
                        <td><code>{{ $router->pppoe_pool_range }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Hotspot Pool</th>
                        <td><code>{{ $router->hotspot_pool_range }}</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @if($router->notes)
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Notes</h6></div>
            <div class="card-body">{{ $router->notes }}</div>
        </div>
    </div>
    @endif

    {{-- Danger Zone --}}
    <div class="col-sm-12">
        <div class="card border-danger">
            <div class="card-header text-danger"><h6 class="mb-0"><i class="bx bx-error-circle me-1"></i> Danger Zone</h6></div>
            <div class="card-body">
                <p class="mb-3 text-muted">Permanently delete this router. All associated subscribers and sessions must be removed first.</p>
                <form action="{{ route('admin.isp.routers.destroy', $router) }}" method="POST"
                      onsubmit="return confirm('Permanently delete router &quot;{{ addslashes($router->name) }}&quot;? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Delete Router
                    </button>
                </form>
            </div>
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
                            <tr>
                                <th class="text-muted">Router Identity</th>
                                <td id="tcIdentity"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">RouterOS Version</th>
                                <td id="tcVersion"></td>
                            </tr>
                            <tr>
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
function toggleSecret() {
    const mask  = document.getElementById('secretMask');
    const value = document.getElementById('secretValue');
    const btn   = event.target;
    if (value.classList.contains('d-none')) {
        mask.classList.add('d-none');
        value.classList.remove('d-none');
        btn.textContent = 'Hide';
    } else {
        mask.classList.remove('d-none');
        value.classList.add('d-none');
        btn.textContent = 'Show';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var btn = document.querySelector('.btn-test-connection');
    if (btn) {
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

                document.getElementById('tcIdentity').textContent = data.router_identity || '—';
                document.getElementById('tcVersion').textContent  = data.version || '—';
                document.getElementById('tcUptime').textContent   = data.uptime || '—';

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
    }
});
</script>
@endpush
