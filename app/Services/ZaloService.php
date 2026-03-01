<?php

namespace App\Services;

use App\Settings\AppSettings;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaloService
{
    protected AppSettings $appSettings;
    protected string $zaloToken;
    protected string $refreshToken;
    protected string $zaloAppSecret;
    protected string $zaloZNSTemplateApiUrl = 'https://business.openapi.zalo.me/message/template';
    protected string $zaloGetAccessTokenApiUrl = 'https://oauth.zaloapp.com/v4/oa/access_token';
    protected string $adminPhone;

    public function __construct(AppSettings $appSettings)
    {
        $this->appSettings = $appSettings;
        $this->zaloToken = $appSettings->app_zalo_token;
        $this->refreshToken = $appSettings->app_zalo_refresh_token;
        $this->zaloAppSecret = $appSettings->app_zalo_app_secret;
        $this->adminPhone = $appSettings->app_zalo_admin_phone;
    }

    /**
     * Send a template message to a Zalo user via phone number
     *
     * @param  string  $phone
     * @param  string  $mode
     * @param  string  $templateId
     * @param  array  $templateData
     * @return array
     */
    public function sendMessage(string $phone, string $mode, string $templateId, array $templateData): array
    {
        $payload = [
            'mode' => $mode,
            'phone' => $phone,
            'template_id' => $templateId,
            'template_data' => $templateData,
            'tracking_id' => uniqid('msg_'),
        ];

        try {
            $response = Http::withHeaders([
                'access_token' => $this->zaloToken,
            ])
                ->asJson()
                ->post($this->zaloZNSTemplateApiUrl, $payload);

            $result = $response->json() ?? [];

            //try again if error = -124 (invalid access token), after refreshing token
            if (isset($result['error']) && $result['error'] === -124) {
                $this->getNewAccessToken();
                return $this->sendMessage($phone, $mode, $templateId, $templateData);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception while sending Zalo message error code (' . ($result['error'] ?? 'N/A') . ') -  error message: ' . $e->getMessage());
            return [
                'error' => 'Exception while sending Zalo message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get a new access token using the refresh token
     *
     * @return array
     */
    public function getNewAccessToken(): array
    {
        $payload = [
            'refresh_token' => $this->refreshToken,
            'app_id' => $this->appSettings->app_zalo_app_id,
            'grant_type' => 'refresh_token',
        ];

        $response = Http::withHeaders([
            'secret_key' => $this->zaloAppSecret,
        ])
            ->asForm()
            ->post($this->zaloGetAccessTokenApiUrl, $payload);

        $data = $response->json() ?? [];

        if (isset($data['access_token'])) {
            $this->appSettings->app_zalo_token = $data['access_token'];

            // Also update refresh token if provided in response
            if (isset($data['refresh_token'])) {
                $this->appSettings->app_zalo_refresh_token = $data['refresh_token'];
            }

            $this->appSettings->save();
        }

        return $data;
    }
}
