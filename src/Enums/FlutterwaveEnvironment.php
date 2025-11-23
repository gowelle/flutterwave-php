<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

use InvalidArgumentException;
use ValueError;

enum FlutterwaveEnvironment: string
{
    case PRODUCTION = 'production';
    case STAGING = 'staging';

    /**
     * Create from configuration
     */
    public static function fromConfig(): self
    {
        $env = config('flutterwave.environment', 'staging');

        try {
            return self::from($env);
        } catch (ValueError $e) {
            throw new InvalidArgumentException(
                "Invalid Flutterwave environment: {$env}. Must be 'production' or 'staging'.",
                previous: $e,
            );
        }
    }

    /**
     * Get the base API URL for this environment
     */
    public function getBaseUrl(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://f4bexperience.flutterwave.com',
            self::STAGING => 'https://developersandbox-api.flutterwave.com',
        };
    }

    /**
     * Get the IDP (Identity Provider) URL for OAuth token requests
     */
    public function getIdpUrl(): string
    {
        // Same for both environments
        return 'https://idp.flutterwave.com/realms/flutterwave/protocol/openid-connect/token';
    }

    /**
     * Check if this is production environment
     */
    public function isProduction(): bool
    {
        return $this === self::PRODUCTION;
    }

    /**
     * Check if this is staging environment
     */
    public function isStaging(): bool
    {
        return $this === self::STAGING;
    }
}
