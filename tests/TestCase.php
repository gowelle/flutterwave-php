<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Tests;

use Gowelle\Flutterwave\FlutterwaveServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [FlutterwaveServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('flutterwave.client_id', 'test_client_id');
        $app['config']->set('flutterwave.client_secret', 'test_client_secret');
        $app['config']->set('flutterwave.secret_hash', 'test_secret_hash');
        $app['config']->set('flutterwave.environment', 'staging');
        $app['config']->set('flutterwave.max_retries', 1);
        $app['config']->set('flutterwave.rate_limit.enabled', false);
    }
}
