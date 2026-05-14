<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Get OAuth2 access token for FCM V1 using service account JSON.
     */
    private static function getAccessToken()
    {
        Log::info('FCM: Entering getAccessToken()');
        $path = storage_path('app/firebase-auth.json');
        if (!file_exists($path)) {
            Log::error('FCM: Service account file not found at ' . $path);
            return null;
        }

        $jsonContent = file_get_contents($path);
        if (!$jsonContent) {
            Log::error('FCM: Failed to read service account file.');
            return null;
        }

        $credentials = json_decode($jsonContent, true);
        if (!$credentials) {
            Log::error('FCM: Failed to decode service account JSON. Error: ' . json_last_error_msg());
            return null;
        }
        $now = time();
        
        // 1. JWT Header
        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        // 2. JWT Payload
        $payload = self::base64UrlEncode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600
        ]));

        Log::info('FCM JWT Header: ' . $header);
        Log::info('FCM JWT Payload: ' . $payload);

        // 3. JWT Signature
        $signature = '';
        $privateKey = str_replace("\\n", "\n", $credentials['private_key']);
        if (!openssl_sign("$header.$payload", $signature, $privateKey, 'sha256WithRSAEncryption')) {
            Log::error('FCM: Failed to sign JWT. Check private key format.');
            return null;
        }
        $signature = self::base64UrlEncode($signature);

        $jwt = "$header.$payload.$signature";

        // 4. Request Access Token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        Log::info('FCM OAuth2 Status: ' . $response->status());
        Log::info('FCM OAuth2 Body: ' . $response->body());

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        return $data['access_token'] ?? null;
    }

    /**
     * Send a push notification to a single device via FCM V1.
     */
    public static function sendToDevice(
        string $fcmToken,
        string $title,
        string $body,
        array $data = []
    ): bool {
        Log::info("FCM: Attempting to send to token: " . substr($fcmToken, 0, 20) . "...");
        $accessToken = self::getAccessToken();
        if (!$accessToken) return false;

        $projectId = 'eventhub-bf56e'; // ID من ملف الـ JSON
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => array_map('strval', $data), // FCM V1 requires string values
                'android' => [
                    'notification' => [
                        'channel_id'   => 'eventhub_high_importance',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
            ]
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('FCM V1 Error: ' . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a push notification to multiple devices.
     */
    public static function sendToDevices(
        array $fcmTokens,
        string $title,
        string $body,
        array $data = []
    ): int {
        $successCount = 0;
        foreach (array_unique(array_filter($fcmTokens)) as $token) {
            if (self::sendToDevice($token, $title, $body, $data)) {
                $successCount++;
            }
        }
        return $successCount;
    }
}

