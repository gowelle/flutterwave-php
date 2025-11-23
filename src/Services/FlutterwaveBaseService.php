<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\Wavable;
use Gowelle\Flutterwave\Support\HeaderBuilder;

/**
 * Flutterwave base service for Flutterwave v4 API
 */
class FlutterwaveBaseService
{
    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct(
        private readonly FlutterwaveConfig $config,
        private readonly FlutterwaveAuthService $authService,
        private readonly FlutterwaveWebhookService $webhookService,
        private readonly HeaderBuilder $headerBuilder,
    ) {}

    /**
     * Get a valid access token (from cache or refresh)
     */
    public function getAccessToken(): string
    {
        return $this->authService->getAccessToken();
    }

    /**
     * Get the header builder
     */
    public function getHeaderBuilder(): HeaderBuilder
    {
        return $this->headerBuilder;
    }

    /**
     * Get the config
     */
    public function getConfig(): FlutterwaveConfig
    {
        return $this->config;
    }

    /**
     * List the items
     *
     * @throws Exception
     */
    public function list(FlutterwaveApi $api, Wavable $wavable): ApiResponse
    {
        $headers = $this->headerBuilder->build($wavable);

        return app(FlutterwaveApiProvider::class)
            ->useApi($api, $this->getAccessToken(), $headers)
            ->list();
    }

    /**
     * Update the item
     *
     * @throws Exception
     */
    public function update(FlutterwaveApi $api, Wavable $wavable, string $id, array $data): ApiResponse
    {
        $headers = $this->headerBuilder->build($wavable);

        return app(FlutterwaveApiProvider::class)
            ->useApi($api, $this->getAccessToken(), $headers)
            ->update($id, $data);
    }

    /**
     * Create the item
     *
     * @throws Exception
     */
    public function create(FlutterwaveApi $api, Wavable $wavable, array $data): ApiResponse
    {
        $headers = $this->headerBuilder->build($wavable);

        return app(FlutterwaveApiProvider::class)
            ->useApi($api, $this->getAccessToken(), $headers)
            ->create($data);
    }

    /**
     * Retrieve the item
     *
     * @throws Exception
     */
    public function retrieve(FlutterwaveApi $api, Wavable $wavable, string $id): ApiResponse
    {
        $headers = $this->headerBuilder->build($wavable);

        return app(FlutterwaveApiProvider::class)
            ->useApi($api, $this->getAccessToken(), $headers)
            ->retrieve($id);
    }

    /**
     * Search the items
     *
     * @throws Exception
     */
    public function search(FlutterwaveApi $api, Wavable $wavable, array $data): ApiResponse
    {
        $headers = $this->headerBuilder->build($wavable);

        return app(FlutterwaveApiProvider::class)
            ->useApi($api, $this->getAccessToken(), $headers)
            ->search($data);
    }
}
