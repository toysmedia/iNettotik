@extends('admin.layouts.app')
@section('title', 'ISP Settings')

@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">ISP Settings</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.isp.dashboard') }}">ISP</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="col-sm-12 mb-3">
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="col-sm-12 mb-3">
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                {{-- Nav Tabs --}}
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab','company') === 'company' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-company" type="button">
                            <i class="bx bx-building-house me-1"></i> Company
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'radius' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-radius" type="button">
                            <i class="bx bx-server me-1"></i> RADIUS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'mpesa' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-mpesa" type="button">
                            <i class="bx bx-mobile-alt me-1"></i> M-Pesa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'sms' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-sms" type="button">
                            <i class="bx bx-message-rounded me-1"></i> SMS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'billing' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-billing" type="button">
                            <i class="bx bx-dollar-circle me-1"></i> Billing
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- ===== COMPANY TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab','company') === 'company' ? 'show active' : '' }}" id="tab-company">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab" value="company">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}" placeholder="My ISP Ltd">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Billing Domain</label>
                                    <input type="text" name="billing_domain" class="form-control" value="{{ $settings['billing_domain'] ?? '' }}" placeholder="billing.myisp.co.ke">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Company Logo</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <div class="form-text">Upload a new logo (optional)</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '' }}" placeholder="+254700000000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? '' }}" placeholder="info@myisp.co.ke">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Physical address">{{ $settings['address'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Company Settings</button>
                        </form>
                    </div>

                    {{-- ===== RADIUS TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'radius' ? 'show active' : '' }}" id="tab-radius">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="radius">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">RADIUS Server IP</label>
                                    <input type="text" name="radius_server_ip" class="form-control" value="{{ $settings['radius_server_ip'] ?? '' }}" placeholder="127.0.0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Global RADIUS Secret</label>
                                    <input type="text" name="radius_secret" class="form-control" value="{{ $settings['radius_secret'] ?? '' }}" placeholder="Default shared secret">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Auth Port</label>
                                    <input type="number" name="radius_port" class="form-control" value="{{ $settings['radius_port'] ?? '1812' }}" min="1" max="65535">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Accounting Port</label>
                                    <input type="number" name="radius_acct_port" class="form-control" value="{{ $settings['radius_acct_port'] ?? '1813' }}" min="1" max="65535">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Interim Update Interval (s)</label>
                                    <input type="number" name="interim_update_interval" class="form-control" value="{{ $settings['interim_update_interval'] ?? '300' }}" min="60">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save RADIUS Settings</button>
                        </form>
                    </div>

                    {{-- ===== M-PESA TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'mpesa' ? 'show active' : '' }}" id="tab-mpesa">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="mpesa">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Environment</label>
                                    <select name="mpesa_environment" class="form-select">
                                        <option value="sandbox"    {{ ($settings['mpesa_environment'] ?? 'sandbox') === 'sandbox'    ? 'selected' : '' }}>Sandbox</option>
                                        <option value="production" {{ ($settings['mpesa_environment'] ?? '') === 'production' ? 'selected' : '' }}>Production</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">STK Shortcode</label>
                                    <input type="text" name="mpesa_shortcode" class="form-control" value="{{ $settings['mpesa_shortcode'] ?? '' }}" placeholder="174379">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Consumer Key</label>
                                    <input type="text" name="mpesa_consumer_key" class="form-control" value="{{ $settings['mpesa_consumer_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Consumer Secret</label>
                                    <input type="password" name="mpesa_consumer_secret" class="form-control" value="{{ $settings['mpesa_consumer_secret'] ?? '' }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Passkey</label>
                                    <input type="password" name="mpesa_passkey" class="form-control" value="{{ $settings['mpesa_passkey'] ?? '' }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">STK Push Callback URL</label>
                                    <input type="url" name="mpesa_stk_callback_url" class="form-control" value="{{ $settings['mpesa_stk_callback_url'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Shortcode</label>
                                    <input type="text" name="mpesa_c2b_shortcode" class="form-control" value="{{ $settings['mpesa_c2b_shortcode'] ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Validation URL</label>
                                    <input type="url" name="mpesa_c2b_validation_url" class="form-control" value="{{ $settings['mpesa_c2b_validation_url'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">C2B Confirmation URL</label>
                                    <input type="url" name="mpesa_c2b_confirmation_url" class="form-control" value="{{ $settings['mpesa_c2b_confirmation_url'] ?? '' }}" placeholder="https://...">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save M-Pesa Settings</button>
                        </form>
                    </div>

                    {{-- ===== SMS TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'sms' ? 'show active' : '' }}" id="tab-sms">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="sms">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Provider</label>
                                    <input type="text" name="at_provider" class="form-control" value="{{ $settings['at_provider'] ?? '' }}" placeholder="Africa's Talking">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">AT Username</label>
                                    <input type="text" name="at_username" class="form-control" value="{{ $settings['at_username'] ?? '' }}" placeholder="sandbox">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">AT API Key</label>
                                    <input type="password" name="at_api_key" class="form-control" value="{{ $settings['at_api_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Sender ID</label>
                                    <input type="text" name="at_sender_id" class="form-control" value="{{ $settings['at_sender_id'] ?? '' }}" placeholder="MyISP">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="sms_enabled"
                                               {{ ($settings['sms_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="sms_enabled">Enable SMS Notifications</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save SMS Settings</button>
                        </form>
                    </div>

                    {{-- ===== BILLING TAB ===== --}}
                    <div class="tab-pane fade {{ session('active_tab') === 'billing' ? 'show active' : '' }}" id="tab-billing">
                        <form action="{{ route('admin.isp.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tab" value="billing">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Default PPPoE Expiry (days)</label>
                                    <input type="number" name="default_pppoe_expiry_days" class="form-control" value="{{ $settings['default_pppoe_expiry_days'] ?? '30' }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Default Hotspot Expiry (hours)</label>
                                    <input type="number" name="default_hotspot_expiry_hours" class="form-control" value="{{ $settings['default_hotspot_expiry_hours'] ?? '24' }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Grace Period (hours)</label>
                                    <input type="number" name="grace_period_hours" class="form-control" value="{{ $settings['grace_period_hours'] ?? '0' }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Auto Disconnect</label>
                                    <select name="auto_disconnect" class="form-select">
                                        <option value="yes" {{ ($settings['auto_disconnect'] ?? 'yes') === 'yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="no"  {{ ($settings['auto_disconnect'] ?? '') === 'no'  ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Currency</label>
                                    <input type="text" name="currency" class="form-control" value="{{ $settings['currency'] ?? 'KES' }}" placeholder="KES">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Paybill Display Text</label>
                                    <input type="text" name="paybill_display_text" class="form-control" value="{{ $settings['paybill_display_text'] ?? '' }}" placeholder="Pay to Account...">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Save Billing Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
