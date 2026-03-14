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
@endsection
