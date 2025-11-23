<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave;

use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Services\FlutterwaveAuthService;
use Gowelle\Flutterwave\Services\FlutterwaveBanksService;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveCustomerService;
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;
use Gowelle\Flutterwave\Services\FlutterwaveMobileNetworkService;
use Gowelle\Flutterwave\Services\FlutterwaveOrderService;
use Gowelle\Flutterwave\Services\FlutterwavePaymentsService;
use Gowelle\Flutterwave\Services\FlutterwaveRefundService;
use Gowelle\Flutterwave\Services\FlutterwaveSettlementService;
use Gowelle\Flutterwave\Services\FlutterwaveTransferService;
use Gowelle\Flutterwave\Services\FlutterwaveWebhookService;
use Gowelle\Flutterwave\Events\DirectChargeCreated;
use Gowelle\Flutterwave\Events\DirectChargeUpdated;
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Gowelle\Flutterwave\Listeners\CreateChargeSession;
use Gowelle\Flutterwave\Listeners\UpdateChargeSession;
use Gowelle\Flutterwave\Listeners\UpdateChargeSessionFromWebhook;
use Gowelle\Flutterwave\Console\Commands\CleanupChargeSessionsCommand;
use Gowelle\Flutterwave\Support\HeaderBuilder;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FlutterwaveServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('flutterwave')
            ->hasConfigFile('flutterwave')
            ->hasMigration('create_flutterwave_charge_sessions_table')
            ->hasCommand(CleanupChargeSessionsCommand::class)
            ->hasRoutes('webhook');
    }

    /**
     * Register package services
     */
    public function register(): void
    {
        parent::register();

        // Bind configuration
        $this->app->singleton(FlutterwaveConfig::class, fn() => FlutterwaveConfig::fromConfig());
        $this->app->alias(FlutterwaveConfig::class, 'flutterwave.config');

        // Bind support classes
        $this->registerSupportClasses();

        // Bind authentication service
        $this->registerAuthService();

        // Bind webhook service
        $this->registerWebhookService();

        // Bind base service
        $this->registerBaseService();

        // Bind domain services
        $this->registerDomainServices();

        // Bind main facade
        $this->registerFacade();
    }

    /**
     * Bootstrap package services
     */
    public function boot(): void
    {
        parent::boot();

        

        // Register charge session listeners
        $this->registerChargeSessionListeners();
    }

    /**
     * Register support classes
     */
    private function registerSupportClasses(): void
    {
        $this->app->singleton(HeaderBuilder::class, fn(Application $app) => new HeaderBuilder(
            $app->make(FlutterwaveConfig::class)
        ));

        $this->app->singleton(RetryHandler::class, fn() => new RetryHandler(
            maxRetries: config('flutterwave.max_retries', 3),
            retryDelay: config('flutterwave.retry_delay', 1000),
        ));

        $this->app->singleton(RateLimiter::class, fn() => new RateLimiter(
            maxRequests: config('flutterwave.rate_limit.max_requests', 100),
            perSeconds: config('flutterwave.rate_limit.per_seconds', 60),
        ));
    }

    /**
     * Register authentication service
     */
    private function registerAuthService(): void
    {
        $this->app->singleton(FlutterwaveAuthService::class, fn(Application $app) => new FlutterwaveAuthService(
            $app->make(FlutterwaveConfig::class)
        ));

        $this->app->alias(FlutterwaveAuthService::class, 'flutterwave.auth');
    }

    /**
     * Register webhook service
     */
    private function registerWebhookService(): void
    {
        $this->app->singleton(FlutterwaveWebhookService::class, fn() => new FlutterwaveWebhookService(
            config('flutterwave.secret_hash')
        ));

        $this->app->alias(FlutterwaveWebhookService::class, 'flutterwave.webhook');
    }

    /**
     * Register base service
     */
    private function registerBaseService(): void
    {
        $this->app->singleton(FlutterwaveBaseService::class, fn(Application $app) => new FlutterwaveBaseService(
            $app->make(FlutterwaveConfig::class),
            $app->make(FlutterwaveAuthService::class),
            $app->make(FlutterwaveWebhookService::class),
            $app->make(HeaderBuilder::class),
        ));

        $this->app->alias(FlutterwaveBaseService::class, 'flutterwave.base');
    }

    /**
     * Register domain services
     */
    private function registerDomainServices(): void
    {
        // Payments service
        $this->app->singleton(FlutterwavePaymentsService::class, fn(Application $app) => new FlutterwavePaymentsService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwavePaymentsService::class, 'flutterwave.payments');

        // Direct charge service
        $this->app->singleton(FlutterwaveDirectChargeService::class, fn(Application $app) => new FlutterwaveDirectChargeService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveDirectChargeService::class, 'flutterwave.direct_charge');

        // Banks service
        $this->app->singleton(FlutterwaveBanksService::class, fn(Application $app) => new FlutterwaveBanksService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveBanksService::class, 'flutterwave.banks');

        // Customer service
        $this->app->singleton(FlutterwaveCustomerService::class, fn(Application $app) => new FlutterwaveCustomerService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveCustomerService::class, 'flutterwave.customers');

        // Mobile networks service
        $this->app->singleton(FlutterwaveMobileNetworkService::class, fn(Application $app) => new FlutterwaveMobileNetworkService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveMobileNetworkService::class, 'flutterwave.mobile_networks');

        // Order service
        $this->app->singleton(FlutterwaveOrderService::class, fn(Application $app) => new FlutterwaveOrderService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveOrderService::class, 'flutterwave.orders');

        // Refund service
        $this->app->singleton(FlutterwaveRefundService::class, fn(Application $app) => new FlutterwaveRefundService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveRefundService::class, 'flutterwave.refunds');

        // Transfer service
        $this->app->singleton(FlutterwaveTransferService::class, fn(Application $app) => new FlutterwaveTransferService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveTransferService::class, 'flutterwave.transfers');

        // Settlement service
        $this->app->singleton(FlutterwaveSettlementService::class, fn(Application $app) => new FlutterwaveSettlementService(
            $app->make(FlutterwaveBaseService::class)
        ));
        $this->app->alias(FlutterwaveSettlementService::class, 'flutterwave.settlements');
    }

    /**
     * Register main facade binding
     */
    private function registerFacade(): void
    {
        $this->app->singleton('flutterwave', function (Application $app) {
            return new class($app)
            {
                public function __construct(private readonly Application $app) {}

                public function payments(): FlutterwavePaymentsService
                {
                    return $this->app->make(FlutterwavePaymentsService::class);
                }

                public function directCharge(): FlutterwaveDirectChargeService
                {
                    return $this->app->make(FlutterwaveDirectChargeService::class);
                }

                public function banks(): FlutterwaveBanksService
                {
                    return $this->app->make(FlutterwaveBanksService::class);
                }

                public function customers(): FlutterwaveCustomerService
                {
                    return $this->app->make(FlutterwaveCustomerService::class);
                }

                public function mobileNetworks(): FlutterwaveMobileNetworkService
                {
                    return $this->app->make(FlutterwaveMobileNetworkService::class);
                }

                public function orders(): FlutterwaveOrderService
                {
                    return $this->app->make(FlutterwaveOrderService::class);
                }

                public function refunds(): FlutterwaveRefundService
                {
                    return $this->app->make(FlutterwaveRefundService::class);
                }

                public function transfers(): FlutterwaveTransferService
                {
                    return $this->app->make(FlutterwaveTransferService::class);
                }

                public function settlements(): FlutterwaveSettlementService
                {
                    return $this->app->make(FlutterwaveSettlementService::class);
                }

                public function webhook(): FlutterwaveWebhookService
                {
                    return $this->app->make(FlutterwaveWebhookService::class);
                }

                public function api(): FlutterwaveBaseService
                {
                    return $this->app->make(FlutterwaveBaseService::class);
                }
            };
        });
    }

    /**
     * Register charge session event listeners
     */
    private function registerChargeSessionListeners(): void
    {
        // Always register webhook listener if charge sessions are enabled
        if (config('flutterwave.charge_sessions.enabled', true)) {
            Event::listen(
                FlutterwaveWebhookReceived::class,
                UpdateChargeSessionFromWebhook::class
            );
        }

        // Register service listeners only if auto_create is enabled
        if (config('flutterwave.charge_sessions.auto_create', false)) {
            Event::listen(
                DirectChargeCreated::class,
                CreateChargeSession::class
            );

            Event::listen(
                DirectChargeUpdated::class,
                UpdateChargeSession::class
            );
        }
    }

}
