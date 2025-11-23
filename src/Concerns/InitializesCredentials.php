<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Concerns;

trait InitializesCredentials
{
    private string $clientId;

    private string $clientSecret;

    private string $environment;

    public function initializeCredentials(): void
    {
        $this->clientId = config('services.flutterwave.client_id');
        $this->clientSecret = config('services.flutterwave.client_secret');
        $this->environment = config('services.flutterwave.environment');
    }
}
