<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Banks\BankAccountResolveRequest;

describe('BankAccountResolveRequest', function () {
    it('creates NGN requests from the convenience constructor', function () {
        $request = BankAccountResolveRequest::forNgn('044', '0123456789');

        expect($request)
            ->toBeInstanceOf(BankAccountResolveRequest::class)
            ->variant->toBe('ngn')
            ->account->toBe([
                'code' => '044',
                'number' => '0123456789',
            ]);
    });

    it('creates USD NG requests from the convenience constructor', function () {
        $request = BankAccountResolveRequest::forUsdNg('044', '0690000031');

        expect($request)
            ->variant->toBe('usd_ng')
            ->account->toBe([
                'code' => '044',
                'number' => '0690000031',
            ]);
    });

    it('creates GBP corporate requests from the convenience constructor', function () {
        $request = BankAccountResolveRequest::forGbpCorporate('044', '0690000031', 'Ajadi & Sons Ltd.');

        expect($request)
            ->variant->toBe('gbp_corporate')
            ->account->toBe([
                'code' => '044',
                'number' => '0690000031',
                'business_name' => 'Ajadi & Sons Ltd.',
            ]);
    });

    it('creates GBP individual requests from the convenience constructor', function () {
        $request = BankAccountResolveRequest::forGbpIndividual('044', '0690000031', 'King', 'LeBron', 'Leo');

        expect($request)
            ->variant->toBe('gbp_individual')
            ->account->toBe([
                'code' => '044',
                'number' => '0690000031',
                'name' => [
                    'first' => 'King',
                    'last' => 'LeBron',
                    'middle' => 'Leo',
                ],
            ]);
    });

    it('converts variant requests to API payload', function () {
        $request = BankAccountResolveRequest::forGbpCorporate('044', '0690000031', 'Ajadi & Sons Ltd.');

        $payload = $request->toApiPayload();

        expect($payload)->toBe([
            'account' => [
                'code' => '044',
                'number' => '0690000031',
                'business_name' => 'Ajadi & Sons Ltd.',
            ],
        ]);
    });

    it('validates NGN number length exactly', function () {
        expect(fn () => BankAccountResolveRequest::forNgn('044', '069000031'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('validates GBP corporate business name', function () {
        expect(fn () => BankAccountResolveRequest::forGbpCorporate('044', '0690000031', 'A'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('validates GBP individual first and last names', function () {
        expect(fn () => BankAccountResolveRequest::forGbpIndividual('044', '0690000031', '', 'LeBron'))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => BankAccountResolveRequest::forGbpIndividual('044', '0690000031', 'King', ''))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => BankAccountResolveRequest::forGbpIndividual('044', '0690000031', 'King', 'LeBron', ''))
            ->toThrow(\InvalidArgumentException::class);
    });
});
