<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Carbon\Carbon;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Exceptions\FlutterwaveAuthException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FlutterwaveAuthService
{
    private const TOKEN_CACHE_KEY = 'flutterwave_access_token';

    private const TOKEN_EXPIRY_BUFFER = 60; // Refresh token 60 seconds before expiry

    public function __construct(
        private readonly FlutterwaveConfig $config,
    ) {}

    /**
     * Get a valid access token (from cache or refresh)
     */
    public function getAccessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cached && $this->isTokenValid($cached)) {
            return $cached['access_token'];
        }

        return $this->refreshAccessToken();
    }

    /**
     * Clear the cached access token
     */
    public function clearTokenCache(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    /**
     * Check if cached token is still valid
     */
    private function isTokenValid(array $tokenData): bool
    {
        if (! isset($tokenData['expires_at'], $tokenData['access_token'])) {
            return false;
        }

        $expiresAt = Carbon::parse($tokenData['expires_at']);
        $now = Carbon::now();

        // Return true if token expires more than TOKEN_EXPIRY_BUFFER seconds from now
        return $expiresAt->gt($now->addSeconds(self::TOKEN_EXPIRY_BUFFER));
    }

    /**
     * Refresh access token from Flutterwave OAuth endpoint
     */
    private function refreshAccessToken(): string
    {
        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post($this->config->getIdpUrl(), [
                    'client_id' => $this->config->clientId,
                    'client_secret' => $this->config->clientSecret,
                    'grant_type' => 'client_credentials',
                ])
                ->throw();

            $data = $response->json();

            if (! isset($data['access_token'], $data['expires_in'])) {
                throw FlutterwaveAuthException::invalidCredentials('Invalid OAuth response from Flutterwave');
            }

            // Cache the token with expiry time
            $tokenData = [
                'access_token' => $data['access_token'],
                'expires_at' => Carbon::now()->addSeconds($data['expires_in'])->toDateTimeString(),
            ];

            Cache::put(self::TOKEN_CACHE_KEY, $tokenData, $data['expires_in']);

            Log::info('Flutterwave access token refreshed', [
                'expires_in' => $data['expires_in'],
                'environment' => $this->config->environment->value,
            ]);

            return $data['access_token'];
        } catch (RequestException $e) {
            Log::error('Failed to refresh Flutterwave access token', [
                'error' => $e->getMessage(),
                'status' => $e->response?->status(),
                'environment' => $this->config->environment->value,
            ]);

            throw FlutterwaveAuthException::tokenRefreshFailed($e->getMessage());
        }
    }
}
