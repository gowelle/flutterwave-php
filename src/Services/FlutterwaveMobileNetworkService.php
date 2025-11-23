<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\MobileNetworkData;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;

final class FlutterwaveMobileNetworkService
{
    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Get mobile networks by country
     *
     * @param  string  $country  country code
     * @return MobileNetworkData[]
     *
     * @throws FlutterwaveApiException
     */
    public function list(string $country): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::MOBILE_NETWORKS, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var MobileNetworksApi $api */  
        $response = $api->retrieveByCountry($country);

        return MobileNetworkData::collection($response->data)->toArray();
    }
}
