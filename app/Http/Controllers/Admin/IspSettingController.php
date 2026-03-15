<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IspSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IspSettingController extends Controller
{
    protected array $settingKeys = [
        'company_name', 'billing_domain', 'radius_server_ip',
        'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode',
        'mpesa_passkey', 'at_username', 'at_api_key', 'at_sender_id',
        'management_ips', 'sms_enabled',
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
        $data = $request->validate([
            'company_name'          => 'nullable|string|max:100',
            'billing_domain'        => 'nullable|string|max:255',
            'radius_server_ip'      => 'nullable|ip',
            'mpesa_consumer_key'    => 'nullable|string|max:255',
            'mpesa_consumer_secret' => 'nullable|string|max:255',
            'mpesa_shortcode'       => 'nullable|string|max:20',
            'mpesa_passkey'         => 'nullable|string|max:255',
            'at_username'           => 'nullable|string|max:100',
            'at_api_key'            => 'nullable|string|max:255',
            'at_sender_id'          => 'nullable|string|max:50',
            'management_ips'        => 'nullable|string|max:500',
            'sms_enabled'           => 'boolean',
        ]);

        foreach ($data as $key => $value) {
            IspSetting::setValue($key, $value ?? '');
        }

        AuditLog::record('settings.updated', IspSetting::class, null, [], $data);

        return back()->with('success', 'Settings saved successfully.');
    }
}
