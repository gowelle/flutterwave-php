<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Jobs;

use Gowelle\Flutterwave\Models\ChargeSession;
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Poll Charge Session Status Job
 *
 * Utility job class for polling Flutterwave API to check the status of
 * a charge session. Applications can dispatch this job for async payment
 * methods that require periodic status checks.
 *
 * Example usage:
 * PollChargeSessionStatus::dispatch($sessionId)->delay(now()->addSeconds(5));
 */
final class PollChargeSessionStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $sessionId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FlutterwaveDirectChargeService $chargeService): void
    {
        $session = ChargeSession::find($this->sessionId);

        if (! $session) {
            // Session not found, stop polling
            return;
        }

        if ($session->isTerminal()) {
            // Session is in terminal state, no need to poll further
            return;
        }

        // Poll Flutterwave API for latest status
        $status = $chargeService->status($session->remote_charge_id);

        // Update session status
        $session->updateStatus($status);
        $session->refresh();

        // If still pending, schedule another poll
        // Applications can customize the delay and max retries
        if (! $session->isTerminal()) {
            $maxPolls = config('flutterwave.charge_sessions.max_polls', 60);
            $pollCount = $session->getMeta('poll_count', 0) + 1;

            if ($pollCount < $maxPolls) {
                $session->setMeta('poll_count', $pollCount);
                $session->save();

                // Schedule next poll
                self::dispatch($this->sessionId)->delay(now()->addSeconds(5));
            } else {
                // Max polls reached, mark as timeout
                $session->setMeta('poll_timeout', true);
                $session->save();
            }
        }
    }
}
