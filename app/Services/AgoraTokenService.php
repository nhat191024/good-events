<?php

namespace App\Services;

use RuntimeException;

final class AgoraTokenService
{
    public function generateRtcToken(
        string $channel,
        int $uid,
    ): array {
        require_once app_path(
            'Support/Agora/RtcTokenBuilder2.php'
        );

        $appId = (string) config('services.agora.app_id');
        $certificate = (string) config(
            'services.agora.app_certificate'
        );

        $ttl = (int) config('services.agora.token_ttl', 3600);

        if ($appId === '' || $certificate === '') {
            throw new RuntimeException(
                'Agora credentials are not configured.'
            );
        }

        if ($channel === '' || strlen($channel) > 64) {
            throw new RuntimeException(
                'Agora channel must contain between 1 and 64 bytes.'
            );
        }

        if ($uid < 1 || $uid > 4294967295) {
            throw new RuntimeException(
                'Agora UID must be between 1 and 4294967295.'
            );
        }

        if ($ttl < 60) {
            throw new RuntimeException(
                'Agora token TTL must be at least 60 seconds.'
            );
        }

        $token = \RtcTokenBuilder2::buildTokenWithUid(
            $appId,
            $certificate,
            $channel,
            $uid,
            \RtcTokenBuilder2::ROLE_PUBLISHER,
            $ttl,
            $ttl,
        );

        if ($token === '') {
            throw new RuntimeException(
                'Could not generate Agora token.'
            );
        }

        return [
            'app_id' => $appId,
            'channel' => $channel,
            'uid' => $uid,
            'token' => $token,
            'expires_in' => $ttl,
            'expires_at' => now()->addSeconds($ttl)->toIso8601String(),
        ];
    }
}
