@extends('admin.layouts.app')
@section('title', 'Add Subscriber')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Add Subscriber</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.isp.subscribers.index') }}">Subscribers</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.isp.subscribers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.subscribers.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Personal Info --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Personal Information</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="John Doe" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="0712345678" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="john@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                          placeholder="Physical address">{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                          placeholder="Optional notes">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Info --}}
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Account Configuration</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username') }}" placeholder="e.g. john.doe or 0712345678" required>
                                <div class="form-text">Used for PPPoE/Hotspot login. Must be unique.</div>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           value="{{ old('password') }}" placeholder="Minimum 6 characters" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="genPassword()">Generate</button>
                                </div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Package <span class="text-danger">*</span></label>
                                <select name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                                    <option value="">-- Select Package --</option>
                                    @foreach($packages ?? [] as $pkg)
                                        <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                            {{ $pkg->name }} — KES {{ number_format($pkg->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Router <span class="text-danger">*</span></label>
                                <select name="router_id" class="form-select @error('router_id') is-invalid @enderror" required>
                                    <option value="">-- Select Router --</option>
                                    @foreach($routers ?? [] as $router)
                                        <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                            {{ $router->name }} ({{ $router->wan_ip }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('router_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Connection Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="pppoe"   {{ old('type') == 'pppoe'   ? 'selected' : '' }}>PPPoE</option>
                                    <option value="hotspot" {{ old('type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Expires At</label>
                                <input type="datetime-local" name="expires_at"
                                       class="form-control @error('expires_at') is-invalid @enderror"
                                       value="{{ old('expires_at') }}">
                                <div class="form-text">Leave blank to auto-calculate from package validity.</div>
                                @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Static IP (Optional)</label>
                                <input type="text" name="static_ip" class="form-control @error('static_ip') is-invalid @enderror"
                                       value="{{ old('static_ip') }}" placeholder="Leave blank for dynamic">
                                @error('static_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Save Subscriber
                    </button>
                    <a href="{{ route('admin.isp.subscribers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function genPassword() {
    const chars = 'abcdefghijkmnpqrstuvwxyz23456789';
    let pass = '';
    for (let i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('password').value = pass;
}
</script>
@endpush
