<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Api\Fees\FeesApi;
use Gowelle\Flutterwave\Data\Fees\FeesData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Illuminate\Support\Str;

final class FlutterwaveFeesService
{
    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Calculate transaction fees.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws FlutterwaveApiException
     */
    public function calculate(array $params): FeesData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::FEES, $this->flutterwaveBaseService->getAccessToken(), $this->buildFeesHeaders());

        /** @var FeesApi $api */
        $response = $api->getFees($params);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve fees: '.($response->message ?? 'Unknown error'));
        }

        /** @var array<string, mixed> $data */
        $data = \is_array($response->data) ? $response->data : [];

        return FeesData::fromApi($data);
    }

    /**
     * Build headers for fees requests.
     *
     * Flutterwave's fees endpoint requires a trace ID but not idempotency.
     *
     * @return array<string, string>
     */
    private function buildFeesHeaders(): array
    {
        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => Str::uuid()->toString(),
        ]);
    }
}
