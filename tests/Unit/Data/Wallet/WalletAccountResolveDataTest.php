<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Wallet\WalletAccountResolveData;

it('can create wallet account resolve data from api response', function () {
    $data = [
        'provider' => 'flutterwave',
        'identifier' => 'wallet_123',
        'name' => 'John Doe',
    ];

    $walletData = WalletAccountResolveData::fromApiResponse($data);

    expect($walletData->provider)->toBe('flutterwave');
    expect($walletData->identifier)->toBe('wallet_123');
    expect($walletData->name)->toBe('John Doe');
});

it('handles missing fields in api response', function () {
    $data = [];

    $walletData = WalletAccountResolveData::fromApiResponse($data);

    expect($walletData->provider)->toBe('');
    expect($walletData->identifier)->toBe('');
    expect($walletData->name)->toBe('');
});

it('can convert to array', function () {
    $walletData = new WalletAccountResolveData(
        provider: 'flutterwave',
        identifier: 'wallet_123',
        name: 'John Doe',
    );

    $array = $walletData->toArray();

    expect($array)->toBe([
        'provider' => 'flutterwave',
        'identifier' => 'wallet_123',
        'name' => 'John Doe',
    ]);
});
