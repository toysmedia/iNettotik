@extends('admin.layouts.app')
@section('title', 'Add Router')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Add Router</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.routers.index') }}">Routers</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.routers.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Basic Info --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Basic Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Router Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. Nairobi CBD Router" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">WAN IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="wan_ip" class="form-control @error('wan_ip') is-invalid @enderror"
                                       value="{{ old('wan_ip') }}" placeholder="e.g. 196.1.2.3" required>
                                @error('wan_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">RADIUS Secret <span class="text-danger">*</span></label>
                                <input type="text" name="radius_secret" class="form-control @error('radius_secret') is-invalid @enderror"
                                       value="{{ old('radius_secret') }}" placeholder="Shared secret between NAS and RADIUS" required>
                                @error('radius_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Billing Domain</label>
                                <input type="text" name="billing_domain" class="form-control @error('billing_domain') is-invalid @enderror"
                                       value="{{ old('billing_domain') }}" placeholder="e.g. billing.myisp.co.ke">
                                @error('billing_domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Network Config --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Network Configuration</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">WAN Interface</label>
                                <input type="text" name="wan_interface" class="form-control @error('wan_interface') is-invalid @enderror"
                                       value="{{ old('wan_interface', 'ether1') }}" placeholder="ether1">
                                @error('wan_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Interface</label>
                                <input type="text" name="customer_interface" class="form-control @error('customer_interface') is-invalid @enderror"
                                       value="{{ old('customer_interface', 'ether2') }}" placeholder="ether2">
                                @error('customer_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PPPoE Pool Range</label>
                                <input type="text" name="pppoe_pool_range" class="form-control @error('pppoe_pool_range') is-invalid @enderror"
                                       value="{{ old('pppoe_pool_range', '10.10.0.1-10.10.255.254') }}" placeholder="10.10.0.1-10.10.255.254">
                                <div class="form-text">IP address pool for PPPoE clients</div>
                                @error('pppoe_pool_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hotspot Pool Range</label>
                                <input type="text" name="hotspot_pool_range" class="form-control @error('hotspot_pool_range') is-invalid @enderror"
                                       value="{{ old('hotspot_pool_range', '192.168.1.1-192.168.1.254') }}" placeholder="192.168.1.1-192.168.1.254">
                                <div class="form-text">IP address pool for hotspot clients</div>
                                @error('hotspot_pool_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="col-sm-12 mb-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Notes</h6></div>
                        <div class="card-body">
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Optional notes about this router...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Save Router
                    </button>
                    <a href="{{ route('admin.isp.routers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
