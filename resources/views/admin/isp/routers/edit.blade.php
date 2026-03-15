@extends('admin.layouts.app')
@section('title', 'Edit Router')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Edit Router: {{ $router->name }}</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.routers.index') }}">Routers</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

        <form action="{{ route('admin.isp.routers.update', $router) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                {{-- Basic Info --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Basic Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Router Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $router->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">WAN IP Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" value="{{ $router->wan_ip ?? '' }}" readonly disabled>
                                    <span class="input-group-text">
                                        @if($router->wan_ip)
                                            <span class="badge bg-success"><i class="bx bx-check me-1"></i>Auto-detected</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Awaiting Script</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="form-text">Detected automatically when the MikroTik script runs.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">RADIUS Secret <span class="text-danger">*</span></label>
                                <input type="text" name="radius_secret" class="form-control @error('radius_secret') is-invalid @enderror"
                                       value="{{ old('radius_secret', $router->radius_secret) }}" required>
                                @error('radius_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Billing Domain</label>
                                <input type="text" name="billing_domain" class="form-control @error('billing_domain') is-invalid @enderror"
                                       value="{{ old('billing_domain', $router->billing_domain) }}">
                                @error('billing_domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', $router->is_active) ? 'checked' : '' }}>
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
                                       value="{{ old('wan_interface', $router->wan_interface) }}">
                                @error('wan_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Interface</label>
                                <input type="text" name="customer_interface" class="form-control @error('customer_interface') is-invalid @enderror"
                                       value="{{ old('customer_interface', $router->customer_interface) }}">
                                @error('customer_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PPPoE Pool Range</label>
                                <input type="text" name="pppoe_pool_range" class="form-control @error('pppoe_pool_range') is-invalid @enderror"
                                       value="{{ old('pppoe_pool_range', $router->pppoe_pool_range) }}">
                                @error('pppoe_pool_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hotspot Pool Range</label>
                                <input type="text" name="hotspot_pool_range" class="form-control @error('hotspot_pool_range') is-invalid @enderror"
                                       value="{{ old('hotspot_pool_range', $router->hotspot_pool_range) }}">
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
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $router->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- WireGuard Tunnel Info --}}
                <div class="col-sm-12 mb-4">
                    <div class="card border-info">
                        <div class="card-header bg-info bg-opacity-10">
                            <h6 class="mb-0"><i class="bx bx-shield-quarter me-2 text-info"></i>WireGuard Tunnel</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Router VPN IP</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light" value="{{ $router->vpn_ip ?? '' }}" readonly disabled>
                                        <span class="input-group-text">
                                            @if($router->vpn_ip)
                                                <span class="badge bg-success"><i class="bx bx-check me-1"></i>Auto-detected</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Pending Setup</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Tunnel Status</label>
                                    <div>
                                        @if($router->wg_public_key)
                                            <span class="badge bg-success fs-6"><i class="bx bx-lock me-1"></i>Connected</span>
                                        @else
                                            <span class="badge bg-secondary fs-6"><i class="bx bx-lock-open me-1"></i>Pending Setup</span>
                                        @endif
                                    </div>
                                    <div class="form-text">Set when the MikroTik script runs and registers back.</div>
                                </div>
                                <div class="col-sm-12">
                                    <label class="form-label fw-semibold">Router WG Public Key</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light font-monospace"
                                               id="wgPubKeyField"
                                               value="{{ $router->wg_public_key ?? '' }}"
                                               readonly disabled
                                               placeholder="Not yet registered — run the MikroTik script first">
                                        @if($router->wg_public_key)
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="(function(btn){var txt='{{ addslashes($router->wg_public_key ?? '') }}';if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(txt).then(function(){btn.innerHTML='<i class=\'bx bx-check\'></i> Copied';setTimeout(function(){btn.innerHTML='<i class=\'bx bx-copy\'></i>';},2000);}).catch(function(){});}else{var ta=document.createElement('textarea');ta.value=txt;document.body.appendChild(ta);ta.select();try{document.execCommand('copy');btn.innerHTML='<i class=\'bx bx-check\'></i> Copied';setTimeout(function(){btn.innerHTML='<i class=\'bx bx-copy\'></i>';},2000);}catch(e){}document.body.removeChild(ta);}})(this)"
                                                title="Copy to clipboard">
                                            <i class="bx bx-copy"></i>
                                        </button>
                                        @endif
                                    </div>
                                    <div class="form-text">Auto-populated when the router runs the generated script.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Update Router
                    </button>
                    <a href="{{ route('admin.isp.routers.show', $router) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
