@extends('admin.layouts.app')
@section('title', 'ISP Settings')

@push('styles')
<style>
    .nav-pills .nav-link { color: #566a7f; }
    .nav-pills .nav-link.active { background-color: #696cff; }
    .settings-section { display: none; }
    .settings-section.active { display: block; }
</style>
@endpush

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

    <div class="col-sm-12">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.isp.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                {{-- Sidebar nav --}}
                <div class="col-sm-3 mb-4">
                    <div class="card">
                        <div class="card-body p-2">
                            <ul class="nav nav-pills flex-column" id="settingsNav">
                                <li class="nav-item">
                                    <a class="nav-link active d-flex align-items-center" href="#" data-section="company">
                                        <i class="bx bx-building-house me-2"></i> Company
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="#" data-section="radius">
                                        <i class="bx bx-server me-2"></i> RADIUS
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="#" data-section="mpesa">
                                        <i class="bx bx-mobile-alt me-2"></i> M-Pesa
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="#" data-section="sms">
                                        <i class="bx bx-message-rounded me-2"></i> SMS
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="#" data-section="billing">
                                        <i class="bx bx-dollar-circle me-2"></i> Billing
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Settings content --}}
                <div class="col-sm-9 mb-4">

                    {{-- Company --}}
                    <div class="settings-section active" id="section-company">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0"><i class="bx bx-building-house me-2"></i>Company Information</h6></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                               value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Short Name / Abbreviation</label>
                                        <input type="text" name="company_short_name" class="form-control @error('company_short_name') is-invalid @enderror"
                                               value="{{ old('company_short_name', $settings['company_short_name'] ?? '') }}" placeholder="e.g. MyISP">
                                        @error('company_short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Phone Number</label>
                                        <input type="text" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror"
                                               value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" placeholder="0712345678">
                                        @error('company_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" name="company_email" class="form-control @error('company_email') is-invalid @enderror"
                                               value="{{ old('company_email', $settings['company_email'] ?? '') }}" placeholder="info@myisp.co.ke">
                                        @error('company_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Website</label>
                                        <input type="url" name="company_website" class="form-control @error('company_website') is-invalid @enderror"
                                               value="{{ old('company_website', $settings['company_website'] ?? '') }}" placeholder="https://www.myisp.co.ke">
                                        @error('company_website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Physical Address</label>
                                        <input type="text" name="company_address" class="form-control @error('company_address') is-invalid @enderror"
                                               value="{{ old('company_address', $settings['company_address'] ?? '') }}" placeholder="Nairobi, Kenya">
                                        @error('company_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label fw-semibold">Logo</label>
                                        <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror" accept="image/*">
                                        @if(!empty($settings['company_logo']))
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" height="50">
                                            </div>
                                        @endif
                                        @error('company_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RADIUS --}}
                    <div class="settings-section" id="section-radius">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0"><i class="bx bx-server me-2"></i>RADIUS Server Configuration</h6></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">RADIUS Host <span class="text-danger">*</span></label>
                                        <input type="text" name="radius_host" class="form-control @error('radius_host') is-invalid @enderror"
                                               value="{{ old('radius_host', $settings['radius_host'] ?? '127.0.0.1') }}" placeholder="127.0.0.1">
                                        @error('radius_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">RADIUS Port</label>
                                        <input type="number" name="radius_port" class="form-control @error('radius_port') is-invalid @enderror"
                                               value="{{ old('radius_port', $settings['radius_port'] ?? 1812) }}" placeholder="1812">
                                        @error('radius_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">RADIUS Secret <span class="text-danger">*</span></label>
                                        <input type="password" name="radius_secret" class="form-control @error('radius_secret') is-invalid @enderror"
                                               value="{{ old('radius_secret', $settings['radius_secret'] ?? '') }}" placeholder="Shared secret">
                                        @error('radius_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">NAS Identifier</label>
                                        <input type="text" name="radius_nas_identifier" class="form-control @error('radius_nas_identifier') is-invalid @enderror"
                                               value="{{ old('radius_nas_identifier', $settings['radius_nas_identifier'] ?? '') }}" placeholder="nas01">
                                        @error('radius_nas_identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Accounting Port</label>
                                        <input type="number" name="radius_acct_port" class="form-control @error('radius_acct_port') is-invalid @enderror"
                                               value="{{ old('radius_acct_port', $settings['radius_acct_port'] ?? 1813) }}" placeholder="1813">
                                        @error('radius_acct_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">COA Port (Disconnect)</label>
                                        <input type="number" name="radius_coa_port" class="form-control @error('radius_coa_port') is-invalid @enderror"
                                               value="{{ old('radius_coa_port', $settings['radius_coa_port'] ?? 3799) }}" placeholder="3799">
                                        @error('radius_coa_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- M-Pesa --}}
                    <div class="settings-section" id="section-mpesa">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0"><i class="bx bx-mobile-alt me-2"></i>M-Pesa (Daraja API) Configuration</h6></div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Obtain these credentials from the <a href="https://developer.safaricom.co.ke" target="_blank">Safaricom Developer Portal</a>.
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Consumer Key</label>
                                        <input type="text" name="mpesa_consumer_key" class="form-control @error('mpesa_consumer_key') is-invalid @enderror"
                                               value="{{ old('mpesa_consumer_key', $settings['mpesa_consumer_key'] ?? '') }}" placeholder="Daraja Consumer Key">
                                        @error('mpesa_consumer_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Consumer Secret</label>
                                        <input type="password" name="mpesa_consumer_secret" class="form-control @error('mpesa_consumer_secret') is-invalid @enderror"
                                               value="{{ old('mpesa_consumer_secret', $settings['mpesa_consumer_secret'] ?? '') }}" placeholder="Daraja Consumer Secret">
                                        @error('mpesa_consumer_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Shortcode (Business)</label>
                                        <input type="text" name="mpesa_shortcode" class="form-control @error('mpesa_shortcode') is-invalid @enderror"
                                               value="{{ old('mpesa_shortcode', $settings['mpesa_shortcode'] ?? '') }}" placeholder="e.g. 600000">
                                        @error('mpesa_shortcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Passkey (STK Push)</label>
                                        <input type="password" name="mpesa_passkey" class="form-control @error('mpesa_passkey') is-invalid @enderror"
                                               value="{{ old('mpesa_passkey', $settings['mpesa_passkey'] ?? '') }}" placeholder="Lipa Na M-Pesa Passkey">
                                        @error('mpesa_passkey')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Callback URL</label>
                                        <input type="url" name="mpesa_callback_url" class="form-control @error('mpesa_callback_url') is-invalid @enderror"
                                               value="{{ old('mpesa_callback_url', $settings['mpesa_callback_url'] ?? url('/api/mpesa/callback')) }}"
                                               placeholder="https://yoursite.co.ke/api/mpesa/callback">
                                        @error('mpesa_callback_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Environment</label>
                                        <select name="mpesa_environment" class="form-select @error('mpesa_environment') is-invalid @enderror">
                                            <option value="sandbox"    {{ old('mpesa_environment', $settings['mpesa_environment'] ?? 'sandbox') == 'sandbox'    ? 'selected' : '' }}>Sandbox (Testing)</option>
                                            <option value="production" {{ old('mpesa_environment', $settings['mpesa_environment'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Production (Live)</option>
                                        </select>
                                        @error('mpesa_environment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SMS --}}
                    <div class="settings-section" id="section-sms">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0"><i class="bx bx-message-rounded me-2"></i>SMS Gateway Configuration</h6></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">SMS Provider</label>
                                        <select name="sms_provider" class="form-select @error('sms_provider') is-invalid @enderror">
                                            <option value="">-- Disabled --</option>
                                            <option value="africastalking" {{ old('sms_provider', $settings['sms_provider'] ?? '') == 'africastalking' ? 'selected' : '' }}>Africa's Talking</option>
                                            <option value="zettatel"       {{ old('sms_provider', $settings['sms_provider'] ?? '') == 'zettatel'       ? 'selected' : '' }}>ZettaTel</option>
                                            <option value="infobip"        {{ old('sms_provider', $settings['sms_provider'] ?? '') == 'infobip'        ? 'selected' : '' }}>Infobip</option>
                                            <option value="twilio"         {{ old('sms_provider', $settings['sms_provider'] ?? '') == 'twilio'         ? 'selected' : '' }}>Twilio</option>
                                        </select>
                                        @error('sms_provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Sender ID</label>
                                        <input type="text" name="sms_sender_id" class="form-control @error('sms_sender_id') is-invalid @enderror"
                                               value="{{ old('sms_sender_id', $settings['sms_sender_id'] ?? '') }}" placeholder="e.g. MYISP">
                                        @error('sms_sender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">API Key / Username</label>
                                        <input type="text" name="sms_api_key" class="form-control @error('sms_api_key') is-invalid @enderror"
                                               value="{{ old('sms_api_key', $settings['sms_api_key'] ?? '') }}" placeholder="API Key or Username">
                                        @error('sms_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">API Secret / Password</label>
                                        <input type="password" name="sms_api_secret" class="form-control @error('sms_api_secret') is-invalid @enderror"
                                               value="{{ old('sms_api_secret', $settings['sms_api_secret'] ?? '') }}" placeholder="API Secret or Password">
                                        @error('sms_api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label fw-semibold">Payment Confirmation SMS Template</label>
                                        <textarea name="sms_payment_template" rows="3"
                                                  class="form-control @error('sms_payment_template') is-invalid @enderror"
                                                  placeholder="Dear {name}, your payment of KES {amount} has been received. Your service expires on {expires_at}. Ref: {ref}">{{ old('sms_payment_template', $settings['sms_payment_template'] ?? '') }}</textarea>
                                        <div class="form-text">Available variables: {name}, {username}, {amount}, {package}, {expires_at}, {ref}</div>
                                        @error('sms_payment_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label fw-semibold">Expiry Reminder SMS Template</label>
                                        <textarea name="sms_expiry_template" rows="3"
                                                  class="form-control @error('sms_expiry_template') is-invalid @enderror"
                                                  placeholder="Dear {name}, your {package} service expires on {expires_at}. Please renew to avoid disconnection.">{{ old('sms_expiry_template', $settings['sms_expiry_template'] ?? '') }}</textarea>
                                        @error('sms_expiry_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Send Expiry Reminder (days before)</label>
                                        <input type="number" min="0" max="30" name="sms_reminder_days"
                                               class="form-control @error('sms_reminder_days') is-invalid @enderror"
                                               value="{{ old('sms_reminder_days', $settings['sms_reminder_days'] ?? 3) }}" placeholder="3">
                                        @error('sms_reminder_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Billing --}}
                    <div class="settings-section" id="section-billing">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0"><i class="bx bx-dollar-circle me-2"></i>Billing Configuration</h6></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Currency</label>
                                        <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                                            <option value="KES" {{ old('currency', $settings['currency'] ?? 'KES') == 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                                            <option value="USD" {{ old('currency', $settings['currency'] ?? 'KES') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                            <option value="UGX" {{ old('currency', $settings['currency'] ?? 'KES') == 'UGX' ? 'selected' : '' }}>UGX - Ugandan Shilling</option>
                                            <option value="TZS" {{ old('currency', $settings['currency'] ?? 'KES') == 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                        </select>
                                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Currency Symbol</label>
                                        <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror"
                                               value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'KES') }}" placeholder="KES">
                                        @error('currency_symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Auto Renew Subscriptions</label>
                                        <select name="auto_renew" class="form-select @error('auto_renew') is-invalid @enderror">
                                            <option value="0" {{ old('auto_renew', $settings['auto_renew'] ?? '0') == '0' ? 'selected' : '' }}>Disabled</option>
                                            <option value="1" {{ old('auto_renew', $settings['auto_renew'] ?? '0') == '1' ? 'selected' : '' }}>Enabled</option>
                                        </select>
                                        @error('auto_renew')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Grace Period (hours after expiry)</label>
                                        <input type="number" min="0" name="grace_period_hours"
                                               class="form-control @error('grace_period_hours') is-invalid @enderror"
                                               value="{{ old('grace_period_hours', $settings['grace_period_hours'] ?? 0) }}" placeholder="0">
                                        @error('grace_period_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-semibold">Disable On Expiry</label>
                                        <select name="disable_on_expiry" class="form-select @error('disable_on_expiry') is-invalid @enderror">
                                            <option value="1" {{ old('disable_on_expiry', $settings['disable_on_expiry'] ?? '1') == '1' ? 'selected' : '' }}>Yes – Disconnect client</option>
                                            <option value="0" {{ old('disable_on_expiry', $settings['disable_on_expiry'] ?? '1') == '0' ? 'selected' : '' }}>No – Allow continued access</option>
                                        </select>
                                        @error('disable_on_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-save me-1"></i> Save Settings
                        </button>
                        <a href="{{ route('admin.isp.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const navLinks = document.querySelectorAll('#settingsNav .nav-link');
const sections = document.querySelectorAll('.settings-section');

navLinks.forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        navLinks.forEach(l => l.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));
        this.classList.add('active');
        const target = document.getElementById('section-' + this.dataset.section);
        if (target) target.classList.add('active');
    });
});
</script>
@endpush
