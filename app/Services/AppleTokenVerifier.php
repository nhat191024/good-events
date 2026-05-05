<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AppleTokenVerifier
{
    private const APPLE_KEYS_URL = 'https://appleid.apple.com/auth/keys';
    private const APPLE_ISSUER = 'https://appleid.apple.com';

    /**
     * @return array{sub: string, email?: string, aud?: string}
     */
    public function verify(string $identityToken): array
    {
        $payload = JWT::decode($identityToken, JWK::parseKeySet($this->publicKeys(), 'RS256'));

        if (($payload->iss ?? null) !== self::APPLE_ISSUER) {
            throw new RuntimeException('Invalid Apple token issuer.');
        }

        $audiences = array_filter([
            config('services.apple.service_id'),
            config('services.apple.ios_bundle_id'),
        ]);

        $tokenAudiences = is_array($payload->aud ?? null)
            ? $payload->aud
            : [($payload->aud ?? null)];

        if (!array_intersect($audiences, $tokenAudiences)) {
            throw new RuntimeException('Invalid Apple token audience.');
        }

        if (empty($payload->sub)) {
            throw new RuntimeException('Invalid Apple token subject.');
        }

        return array_filter([
            'sub' => $payload->sub,
            'email' => $payload->email ?? null,
            'aud' => $payload->aud ?? null,
        ], static fn($value) => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function publicKeys(): array
    {
        return Cache::remember('apple_sign_in_public_keys', now()->addDay(), function () {
            $response = Http::timeout(10)->get(self::APPLE_KEYS_URL);

            if (!$response->successful()) {
                throw new RuntimeException('Unable to fetch Apple public keys.');
            }

            return $response->json();
        });
    }
}
