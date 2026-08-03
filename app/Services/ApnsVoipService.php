<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApnsVoipService
{
    /** @param array<string, mixed> $payload */
    public function send(string $deviceToken, array $payload): Response
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('APNs VoIP credentials are not configured.');
        }

        return Http::withToken($this->providerToken())
            ->acceptJson()
            ->contentType('application/json')
            ->withHeaders([
                'apns-topic' => config('services.apns_voip.bundle_id').'.voip',
                'apns-push-type' => 'voip',
                'apns-priority' => '10',
                'apns-expiration' => '0',
            ])
            ->withOptions(['version' => 2.0])
            ->connectTimeout(5)
            ->timeout(10)
            ->post($this->endpoint().'/3/device/'.$deviceToken, $payload);
    }

    public function isConfigured(): bool
    {
        return collect([
            config('services.apns_voip.team_id'),
            config('services.apns_voip.key_id'),
            config('services.apns_voip.bundle_id'),
            config('services.apns_voip.private_key'),
        ])->every(fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    private function providerToken(): string
    {
        $teamId = (string) config('services.apns_voip.team_id');
        $keyId = (string) config('services.apns_voip.key_id');

        return Cache::remember(
            "apns-voip-provider-token:{$teamId}:{$keyId}",
            now()->addMinutes(50),
            fn (): string => JWT::encode(
                ['iss' => $teamId, 'iat' => time()],
                $this->privateKey(),
                'ES256',
                $keyId,
            ),
        );
    }

    private function privateKey(): string
    {
        $configuredKey = (string) config('services.apns_voip.private_key');

        if (str_contains($configuredKey, 'BEGIN PRIVATE KEY')) {
            return str_replace('\\n', "\n", $configuredKey);
        }

        $keyPath = base_path($configuredKey);

        if (! is_file($keyPath) || ! is_readable($keyPath)) {
            throw new RuntimeException('The configured APNs private key is not readable.');
        }

        $privateKey = file_get_contents($keyPath);

        if ($privateKey === false) {
            throw new RuntimeException('Could not read the configured APNs private key.');
        }

        return $privateKey;
    }

    private function endpoint(): string
    {
        return config('services.apns_voip.environment') === 'production'
            ? 'https://api.push.apple.com'
            : 'https://api.sandbox.push.apple.com';
    }
}
