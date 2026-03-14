<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IspSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IspSettingController extends Controller
{
    protected array $settingKeys = [
        // Company
        'company_name', 'billing_domain', 'address', 'phone', 'email',
        // RADIUS
        'radius_server_ip', 'radius_secret', 'radius_port', 'radius_acct_port', 'interim_update_interval',
        // M-Pesa
        'mpesa_environment', 'mpesa_consumer_key', 'mpesa_consumer_secret',
        'mpesa_shortcode', 'mpesa_passkey', 'mpesa_stk_callback_url',
        'mpesa_c2b_shortcode', 'mpesa_c2b_validation_url', 'mpesa_c2b_confirmation_url',
        // SMS
        'at_provider', 'at_username', 'at_api_key', 'at_sender_id', 'sms_enabled',
        // Billing
        'default_pppoe_expiry_days', 'default_hotspot_expiry_hours', 'grace_period_hours',
        'auto_disconnect', 'currency', 'paybill_display_text',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->settingKeys as $key) {
            $settings[$key] = IspSetting::getValue($key, '');
        }
        return view('admin.isp.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $tab = $request->get('tab', 'company');

        $rules = [];
        switch ($tab) {
            case 'company':
                $rules = [
                    'company_name'   => 'nullable|string|max:100',
                    'billing_domain' => 'nullable|string|max:255',
                    'address'        => 'nullable|string|max:500',
                    'phone'          => 'nullable|string|max:30',
                    'email'          => 'nullable|email|max:100',
                ];
                break;
            case 'radius':
                $rules = [
                    'radius_server_ip'         => 'nullable|ip',
                    'radius_secret'            => 'nullable|string|max:100',
                    'radius_port'              => 'nullable|integer|min:1|max:65535',
                    'radius_acct_port'         => 'nullable|integer|min:1|max:65535',
                    'interim_update_interval'  => 'nullable|integer|min:60',
                ];
                break;
            case 'mpesa':
                $rules = [
                    'mpesa_environment'          => 'nullable|in:sandbox,production',
                    'mpesa_consumer_key'         => 'nullable|string|max:255',
                    'mpesa_consumer_secret'      => 'nullable|string|max:255',
                    'mpesa_shortcode'            => 'nullable|string|max:20',
                    'mpesa_passkey'              => 'nullable|string|max:255',
                    'mpesa_stk_callback_url'     => 'nullable|url|max:500',
                    'mpesa_c2b_shortcode'        => 'nullable|string|max:20',
                    'mpesa_c2b_validation_url'   => 'nullable|url|max:500',
                    'mpesa_c2b_confirmation_url' => 'nullable|url|max:500',
                ];
                break;
            case 'sms':
                $rules = [
                    'at_provider'  => 'nullable|string|max:50',
                    'at_username'  => 'nullable|string|max:100',
                    'at_api_key'   => 'nullable|string|max:255',
                    'at_sender_id' => 'nullable|string|max:50',
                    'sms_enabled'  => 'boolean',
                ];
                break;
            case 'billing':
                $rules = [
                    'default_pppoe_expiry_days'    => 'nullable|integer|min:1',
                    'default_hotspot_expiry_hours' => 'nullable|integer|min:1',
                    'grace_period_hours'           => 'nullable|integer|min:0',
                    'auto_disconnect'              => 'nullable|in:yes,no',
                    'currency'                     => 'nullable|string|max:10',
                    'paybill_display_text'         => 'nullable|string|max:255',
                ];
                break;
        }

        $data = $request->validate($rules);

        // Handle boolean sms_enabled
        if ($tab === 'sms') {
            $data['sms_enabled'] = $request->boolean('sms_enabled') ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            IspSetting::setValue($key, $value ?? '');
        }

        AuditLog::record('settings.updated', IspSetting::class, null, [], array_merge(['tab' => $tab], $data));

        return back()->with('success', ucfirst($tab) . ' settings saved successfully.')->with('active_tab', $tab);
    }
}
