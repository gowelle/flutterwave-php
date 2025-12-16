<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Console\Commands;

use Exception;
use Gowelle\Flutterwave\Services\FlutterwaveAuthService;
use Illuminate\Console\Command;

/**
 * Verify Flutterwave Credentials Command
 *
 * Tests the configured Flutterwave API credentials by attempting
 * to authenticate and retrieve an access token.
 */
final class VerifyCredentialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutterwave:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Flutterwave API credentials are correctly configured';

    /**
     * Execute the console command.
     */
    public function handle(FlutterwaveAuthService $authService): int
    {
        $this->info('Verifying Flutterwave API credentials...');
        $this->newLine();

        // Display configured environment
        $environment = config('flutterwave.environment');
        $this->line("Environment: <fg=cyan>{$environment}</>");

        // Check if credentials are set
        $clientId = config('flutterwave.client_id');
        $clientSecret = config('flutterwave.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('❌ Flutterwave credentials are not configured.');
            $this->newLine();
            $this->line('Please set the following environment variables:');
            $this->line('  - FLUTTERWAVE_CLIENT_ID');
            $this->line('  - FLUTTERWAVE_CLIENT_SECRET');

            return self::FAILURE;
        }

        $this->line('Client ID: <fg=green>✓ Set</>');
        $this->line('Client Secret: <fg=green>✓ Set</>');
        $this->newLine();

        // Attempt to get access token
        $this->line('Testing authentication...');

        try {
            $accessToken = $authService->getAccessToken();

            if (! empty($accessToken)) {
                $this->newLine();
                $this->info('✅ Authentication successful!');
                $this->line('Access token retrieved: '.substr($accessToken, 0, 20).'...');
                $this->newLine();
                $this->line('<fg=green>Your Flutterwave API credentials are correctly configured.</>');

                return self::SUCCESS;
            }

            $this->error('❌ Authentication failed: Empty access token received.');

            return self::FAILURE;
        } catch (Exception $e) {
            $this->newLine();
            $this->error('❌ Authentication failed!');
            $this->newLine();
            $this->line('<fg=red>Error:</> '.$e->getMessage());
            $this->newLine();
            $this->line('Please verify:');
            $this->line('  1. Your credentials are correct');
            $this->line('  2. Your Flutterwave account is active');
            $this->line('  3. You have network connectivity to Flutterwave API');

            return self::FAILURE;
        }
    }
}
