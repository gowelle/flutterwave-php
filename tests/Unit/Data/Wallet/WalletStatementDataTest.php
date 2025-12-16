<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Wallet\WalletStatementCursor;
use Gowelle\Flutterwave\Data\Wallet\WalletStatementData;

it('can create wallet statement data from api response', function () {
    $data = [
        'cursor' => [
            'next' => 'next_cursor',
            'previous' => 'prev_cursor',
            'limit' => 10,
            'total' => 100,
            'has_more_items' => true,
        ],
        'transactions' => [
            [
                'transaction_direction' => 'credit',
                'amount' => ['value' => 1000, 'currency' => 'NGN'],
            ],
        ],
    ];

    $statementData = WalletStatementData::fromApiResponse($data);

    expect($statementData->cursor)->toBeInstanceOf(WalletStatementCursor::class);
    expect($statementData->cursor->next)->toBe('next_cursor');
    expect($statementData->cursor->previous)->toBe('prev_cursor');
    expect($statementData->cursor->limit)->toBe(10);
    expect($statementData->cursor->total)->toBe(100);
    expect($statementData->cursor->hasMoreItems)->toBeTrue();
    expect($statementData->transactions)->toBeArray();
    expect(\count($statementData->transactions))->toBe(1);
});

it('handles missing fields in api response', function () {
    $data = [];

    $statementData = WalletStatementData::fromApiResponse($data);

    expect($statementData->cursor)->toBeInstanceOf(WalletStatementCursor::class);
    expect($statementData->cursor->next)->toBeNull();
    expect($statementData->transactions)->toBe([]);
});

it('can create wallet statement cursor from api response', function () {
    $data = [
        'next' => 'next_cursor',
        'previous' => 'prev_cursor',
        'limit' => 10,
        'total' => 100,
        'has_more_items' => true,
    ];

    $cursor = WalletStatementCursor::fromApiResponse($data);

    expect($cursor->next)->toBe('next_cursor');
    expect($cursor->previous)->toBe('prev_cursor');
    expect($cursor->limit)->toBe(10);
    expect($cursor->total)->toBe(100);
    expect($cursor->hasMoreItems)->toBeTrue();
});

it('handles missing cursor fields in api response', function () {
    $data = [];

    $cursor = WalletStatementCursor::fromApiResponse($data);

    expect($cursor->next)->toBeNull();
    expect($cursor->previous)->toBeNull();
    expect($cursor->limit)->toBe(10);
    expect($cursor->total)->toBe(0);
    expect($cursor->hasMoreItems)->toBeFalse();
});

it('can convert wallet statement data to array', function () {
    $cursor = new WalletStatementCursor(
        next: 'next_cursor',
        previous: 'prev_cursor',
        limit: 10,
        total: 100,
        hasMoreItems: true,
    );

    $statementData = new WalletStatementData(
        cursor: $cursor,
        transactions: [['id' => 'txn_1']],
    );

    $array = $statementData->toArray();

    expect($array)->toHaveKeys(['cursor', 'transactions']);
    expect($array['transactions'])->toBe([['id' => 'txn_1']]);
});

it('can convert wallet statement cursor to array', function () {
    $cursor = new WalletStatementCursor(
        next: 'next_cursor',
        previous: 'prev_cursor',
        limit: 10,
        total: 100,
        hasMoreItems: true,
    );

    $array = $cursor->toArray();

    expect($array)->toBe([
        'next' => 'next_cursor',
        'previous' => 'prev_cursor',
        'limit' => 10,
        'total' => 100,
        'has_more_items' => true,
    ]);
});
