<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Get list of enabled gateways.
     */
    public function getActiveGateways(): array
    {
        $gateways = [];
        $config   = config('payment_gateways.gateways', []);

        foreach ($config as $key => $gateway) {
            if (!empty($gateway['enabled'])) {
                $gateways[$key] = $gateway;
            }
        }

        return $gateways;
    }

    /**
     * Initiate Kopokopo Till payment.
     */
    public function kopopokoInitiate(string $phone, float $amount): array
    {
        Log::info('Kopokopo initiate', compact('phone', 'amount'));
        throw new \RuntimeException('Kopokopo gateway not yet fully implemented');
    }

    /**
     * Initiate Equity Bank payment.
     */
    public function equityInitiate(string $phone, float $amount): array
    {
        Log::info('Equity initiate', compact('phone', 'amount'));
        throw new \RuntimeException('Equity Bank gateway not yet fully implemented');
    }

    /**
     * Initiate KCB Bank payment.
     */
    public function kcbInitiate(string $phone, float $amount): array
    {
        Log::info('KCB initiate', compact('phone', 'amount'));
        throw new \RuntimeException('KCB Bank gateway not yet fully implemented');
    }

    /**
     * Initiate Cooperative Bank payment.
     */
    public function coopInitiate(string $phone, float $amount): array
    {
        Log::info('Coop Bank initiate', compact('phone', 'amount'));
        throw new \RuntimeException('Cooperative Bank gateway not yet fully implemented');
    }

    /**
     * Initiate M-Pesa Till (STK Push via Till Number).
     */
    public function mpesaTillPush(string $phone, float $amount): array
    {
        Log::info('M-Pesa Till push', compact('phone', 'amount'));
        throw new \RuntimeException('M-Pesa Till gateway not yet fully implemented');
    }
}
