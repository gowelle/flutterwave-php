<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Wallet\WalletStatementRequest;

it('can create wallet statement request', function () {
    $request = new WalletStatementRequest(
        currency: 'NGN',
        size: 20,
        from: '2024-01-01T00:00:00Z',
        to: '2024-12-31T23:59:59Z',
        next: 'next_cursor',
        previous: 'prev_cursor',
    );

    expect($request->currency)->toBe('NGN');
    expect($request->size)->toBe(20);
    expect($request->from)->toBe('2024-01-01T00:00:00Z');
    expect($request->to)->toBe('2024-12-31T23:59:59Z');
    expect($request->next)->toBe('next_cursor');
    expect($request->previous)->toBe('prev_cursor');
});

it('can create wallet statement request with only required currency', function () {
    $request = new WalletStatementRequest(currency: 'NGN');

    expect($request->currency)->toBe('NGN');
    expect($request->size)->toBeNull();
    expect($request->from)->toBeNull();
    expect($request->to)->toBeNull();
    expect($request->next)->toBeNull();
    expect($request->previous)->toBeNull();
});

it('can convert to array with all fields', function () {
    $request = new WalletStatementRequest(
        currency: 'NGN',
        size: 20,
        from: '2024-01-01T00:00:00Z',
        to: '2024-12-31T23:59:59Z',
        next: 'next_cursor',
        previous: 'prev_cursor',
    );

    $array = $request->toArray();

    expect($array)->toBe([
        'currency' => 'NGN',
        'size' => 20,
        'from' => '2024-01-01T00:00:00Z',
        'to' => '2024-12-31T23:59:59Z',
        'next' => 'next_cursor',
        'previous' => 'prev_cursor',
    ]);
});

it('can convert to array with only currency', function () {
    $request = new WalletStatementRequest(currency: 'NGN');

    $array = $request->toArray();

    expect($array)->toBe([
        'currency' => 'NGN',
    ]);
});

it('excludes null values from array', function () {
    $request = new WalletStatementRequest(
        currency: 'NGN',
        size: 20,
    );

    $array = $request->toArray();

    expect($array)->toHaveKeys(['currency', 'size']);
    expect($array)->not->toHaveKey('from');
    expect($array)->not->toHaveKey('to');
    expect($array)->not->toHaveKey('next');
    expect($array)->not->toHaveKey('previous');
});
