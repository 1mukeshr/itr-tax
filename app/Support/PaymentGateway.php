<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentGateway
{
    public static function razorpayKey(): ?string
    {
        $key = trim((string) Setting::getValue('razorpay_key', env('RAZORPAY_KEY', '')));

        return $key !== '' ? $key : null;
    }

    public static function razorpaySecret(): ?string
    {
        $secret = trim((string) Setting::getValue('razorpay_secret', env('RAZORPAY_SECRET', '')));

        return $secret !== '' ? $secret : null;
    }

    public static function isLive(): bool
    {
        return self::razorpayKey() && self::razorpaySecret();
    }

    /**
     * Create Razorpay order (amount in INR). Returns order id or null on failure.
     *
     * @return array{id: string, amount: int, currency: string}|null
     */
    public static function createOrder(float $amountInr, string $receipt, array $notes = []): ?array
    {
        if (! self::isLive()) {
            return null;
        }

        $paise = (int) round($amountInr * 100);
        $response = Http::withBasicAuth((string) self::razorpayKey(), (string) self::razorpaySecret())
            ->asJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $paise,
                'currency' => 'INR',
                'receipt' => Str::limit($receipt, 40, ''),
                'notes' => $notes,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
            'id' => (string) ($data['id'] ?? ''),
            'amount' => (int) ($data['amount'] ?? $paise),
            'currency' => (string) ($data['currency'] ?? 'INR'),
        ];
    }

    public static function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = self::razorpaySecret();
        if (! $secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }
}
