<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Exceptions\FlutterwaveAuthException;
use InvalidArgumentException;

final readonly class FlutterwaveConfig
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $secretHash,
        public FlutterwaveEnvironment $environment,
        public ?string $encryptionKey = null,
    ) {
        $this->validate();
    }

    /**
     * Create configuration from Laravel config
     */
    public static function fromConfig(): self
    {
        return new self(
            clientId: config('flutterwave.client_id', ''),
            clientSecret: config('flutterwave.client_secret', ''),
            secretHash: config('flutterwave.secret_hash', ''),
            environment: FlutterwaveEnvironment::fromConfig(),
            encryptionKey: config('flutterwave.encryption_key'),
        );
    }

    /**
     * Get the base API URL for this environment
     */
    public function getBaseUrl(): string
    {
        return $this->environment->getBaseUrl();
    }

    /**
     * Get the IDP URL for OAuth
     */
    public function getIdpUrl(): string
    {
        return $this->environment->getIdpUrl();
    }

    /**
     * Check if in production
     */
    public function isProduction(): bool
    {
        return $this->environment->isProduction();
    }

    /**
     * Validate configuration values
     */
    private function validate(): void
    {
        if (empty($this->clientId)) {
            throw FlutterwaveAuthException::credentialsNotConfigured();
        }

        if (empty($this->clientSecret)) {
            throw FlutterwaveAuthException::credentialsNotConfigured();
        }

        if (empty($this->secretHash)) {
            throw new InvalidArgumentException(
                'Flutterwave secret hash not configured. '
                .'Please set FLW_SECRET_HASH in your .env file.',
            );
        }
    }
}
