<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Console\Commands;

use Gowelle\Flutterwave\Models\ChargeSession;
use Illuminate\Console\Command;

/**
 * Cleanup Charge Sessions Command
 *
 * Deletes charge sessions older than the configured number of days.
 * This command should be scheduled to run periodically (e.g., daily).
 */
final class CleanupChargeSessionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutterwave:cleanup-sessions
                            {--days= : Number of days to keep sessions (overrides config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old Flutterwave charge sessions based on configured retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if charge sessions are enabled
        if (! config('flutterwave.charge_sessions.enabled', true)) {
            $this->warn('Charge sessions are disabled. Skipping cleanup.');

            return self::SUCCESS;
        }

        // Get the number of days from option or config
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : config('flutterwave.charge_sessions.cleanup_after_days', 30);

        if ($days <= 0) {
            $this->error('Invalid number of days. Must be greater than 0.');

            return self::FAILURE;
        }

        $isDryRun = $this->option('dry-run');

        // Calculate the cutoff date
        $cutoffDate = now()->subDays($days);

        // Query old sessions
        $oldSessions = ChargeSession::where('created_at', '<', $cutoffDate);

        $count = $oldSessions->count();

        if ($count === 0) {
            $this->info('No charge sessions found older than '.$days.' days.');

            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->info("Would delete {$count} charge session(s) older than {$days} days (created before {$cutoffDate->toDateString()}).");

            return self::SUCCESS;
        }

        // Delete old sessions
        $deleted = $oldSessions->delete();

        $this->info("Successfully deleted {$deleted} charge session(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
