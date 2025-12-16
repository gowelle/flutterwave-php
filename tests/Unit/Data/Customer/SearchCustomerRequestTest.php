<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Customer\SearchCustomerRequest;

describe('SearchCustomerRequest', function () {
    it('creates with email', function () {
        $request = new SearchCustomerRequest(
            email: 'john@example.com',
        );

        expect($request)
            ->toBeInstanceOf(SearchCustomerRequest::class)
            ->email->toBe('john@example.com');
    });

    it('creates with no email', function () {
        $request = new SearchCustomerRequest;

        expect($request->email)->toBeNull();
    });

    it('converts to API payload with email', function () {
        $request = new SearchCustomerRequest(
            email: 'john@example.com',
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('email', 'john@example.com');
    });

    it('converts to empty API payload when no email', function () {
        $request = new SearchCustomerRequest;

        $payload = $request->toApiPayload();

        expect($payload)->toBeEmpty();
    });
});
