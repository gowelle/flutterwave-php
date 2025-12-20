<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Tests;

use Gowelle\Flutterwave\FlutterwaveServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FlutterwaveServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Laravel encryption key (required for Livewire) - 32 bytes for AES-256-CBC
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        $app['config']->set('flutterwave.client_id', 'test_client_id');
        $app['config']->set('flutterwave.client_secret', 'test_client_secret');
        $app['config']->set('flutterwave.secret_hash', 'test_secret_hash');
        $app['config']->set('flutterwave.encryption_key', 'test-encryption-key-32-chars-long');
        $app['config']->set('flutterwave.environment', 'staging');
        $app['config']->set('flutterwave.max_retries', 1);
        $app['config']->set('flutterwave.rate_limit.enabled', false);
        $app['config']->set('flutterwave.default_currency', 'TZS');
    }
}
