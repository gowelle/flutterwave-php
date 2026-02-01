<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Tests\Integration;

use Dotenv\Dotenv;
use Gowelle\Flutterwave\FlutterwaveServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for integration tests that make real API calls.
 *
 * Integration tests require actual Flutterwave staging credentials.
 * Set these environment variables before running:
 * - FLUTTERWAVE_CLIENT_ID
 * - FLUTTERWAVE_CLIENT_SECRET
 * - FLUTTERWAVE_SECRET_HASH
 */
abstract class IntegrationTestCase extends Orchestra
{
    protected function setUp(): void
    {
        // Load .env file if it exists
        $envPath = \dirname(__DIR__, 2);
        if (file_exists($envPath.'/.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->safeLoad();
        }

        parent::setUp();

        // Skip tests if credentials are not configured
        if (! $this->hasFlutterwaveCredentials()) {
            $this->markTestSkipped('Flutterwave staging credentials not configured. Set FLUTTERWAVE_CLIENT_ID, FLUTTERWAVE_CLIENT_SECRET, and FLUTTERWAVE_SECRET_HASH environment variables.');
        }
    }

    protected function getPackageProviders($app): array
    {
        return [FlutterwaveServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use real credentials from environment
        $clientId = env('FLUTTERWAVE_CLIENT_ID');
        $clientSecret = env('FLUTTERWAVE_CLIENT_SECRET');
        $secretHash = env('FLUTTERWAVE_SECRET_HASH');

        // Set config at flutterwave.* (used by FlutterwaveConfig and services)
        $app['config']->set('flutterwave.client_id', $clientId);
        $app['config']->set('flutterwave.client_secret', $clientSecret);
        $app['config']->set('flutterwave.secret_hash', $secretHash);
        $app['config']->set('flutterwave.environment', 'staging');
        $app['config']->set('flutterwave.timeout', 30);
        $app['config']->set('flutterwave.max_retries', 3);
        $app['config']->set('flutterwave.retry_delay', 1000);
        $app['config']->set('flutterwave.rate_limit.enabled', false);
        $app['config']->set('flutterwave.default_currency', 'TZS');

        // Also set config at services.flutterwave.* (used by InitializesCredentials trait)
        $app['config']->set('services.flutterwave.client_id', $clientId);
        $app['config']->set('services.flutterwave.client_secret', $clientSecret);
        $app['config']->set('services.flutterwave.environment', 'staging');
    }

    /**
     * Check if staging credentials are configured.
     */
    protected function hasFlutterwaveCredentials(): bool
    {
        return ! empty(env('FLUTTERWAVE_CLIENT_ID'))
            && ! empty(env('FLUTTERWAVE_CLIENT_SECRET'))
            && ! empty(env('FLUTTERWAVE_SECRET_HASH'));
    }

    /**
     * Generate a unique reference for test transactions.
     */
    protected function generateReference(string $prefix = 'TEST'): string
    {
        return \sprintf('%s-%s-%s', $prefix, date('YmdHis'), bin2hex(random_bytes(4)));
    }

    /**
     * Generate a test email address.
     */
    protected function generateTestEmail(): string
    {
        return \sprintf('test-%s@example.com', bin2hex(random_bytes(4)));
    }

    /**
     * Get test customer data.
     *
     * @return array<string, mixed>
     */
    /**
     * Get test customer data (v4: only email required).
     * Uses email + name only to avoid staging API phone-format differences.
     *
     * @return array<string, mixed>
     */
    protected function getTestCustomerData(): array
    {
        return [
            'email' => $this->generateTestEmail(),
            'name' => [
                'first' => 'Integration',
                'last' => 'TestUser',
            ],
        ];
    }

    /**
     * Get test card data (Flutterwave test card).
     *
     * @return array<string, mixed>
     */
    protected function getTestCardData(): array
    {
        return [
            'type' => 'card',
            'card_number' => '5531886652142950',
            'cvv' => '564',
            'expiry_month' => '09',
            'expiry_year' => '32',
        ];
    }
}
