<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $username;
    protected string $apiKey;
    protected string $senderId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = config('sms.africastalking.username', 'sandbox');
        $this->apiKey   = config('sms.africastalking.api_key', '');
        $this->senderId = config('sms.africastalking.sender_id', '');
        $this->baseUrl  = config('sms.africastalking.base_url');
    }

    /**
     * Send an SMS via Africa's Talking API.
     */
    public function send(string $to, string $message): array
    {
        if (empty($this->apiKey)) {
            Log::info('SMS skipped (no API key configured)', compact('to', 'message'));
            return ['status' => 'skipped'];
        }

        $to = $this->formatPhone($to);

        $payload = [
            'username' => $this->username,
            'to'       => $to,
            'message'  => $message,
        ];

        if ($this->senderId) {
            $payload['from'] = $this->senderId;
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post("{$this->baseUrl}/messaging", $payload);

            Log::info('SMS sent', ['to' => $to, 'response' => $response->json()]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('SMS send error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Format phone number for Africa's Talking (international format with +).
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '254')) {
            $phone = '+' . $phone;
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+254' . $phone;
        }
        return $phone;
    }
}
