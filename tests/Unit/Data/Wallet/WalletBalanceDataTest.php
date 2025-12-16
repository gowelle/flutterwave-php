<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Wallet\WalletBalanceData;

it('can create wallet balance data from api response', function () {
    $data = [
        'currency' => 'NGN',
        'available_balance' => 1200.09,
    ];

    $balanceData = WalletBalanceData::fromApiResponse($data);

    expect($balanceData->currency)->toBe('NGN');
    expect($balanceData->availableBalance)->toBe(1200.09);
});

it('handles missing fields in api response', function () {
    $data = [];

    $balanceData = WalletBalanceData::fromApiResponse($data);

    expect($balanceData->currency)->toBe('');
    expect($balanceData->availableBalance)->toBe(0.0);
});

it('can create collection from api response array', function () {
    $data = [
        [
            'currency' => 'NGN',
            'available_balance' => 1200.09,
        ],
        [
            'currency' => 'USD',
            'available_balance' => 3.29,
        ],
    ];

    $collection = WalletBalanceData::collection($data);

    expect($collection)->toBeArray();
    expect(\count($collection))->toBe(2);
    expect($collection[0])->toBeInstanceOf(WalletBalanceData::class);
    expect($collection[0]->currency)->toBe('NGN');
    expect($collection[1]->currency)->toBe('USD');
});

it('can convert to array', function () {
    $balanceData = new WalletBalanceData(
        currency: 'NGN',
        availableBalance: 1200.09,
    );

    $array = $balanceData->toArray();

    expect($array)->toBe([
        'currency' => 'NGN',
        'available_balance' => 1200.09,
    ]);
});
