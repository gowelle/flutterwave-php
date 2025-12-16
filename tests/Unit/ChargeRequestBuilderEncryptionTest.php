<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Builders\ChargeRequestBuilder;
use Gowelle\Flutterwave\Exceptions\EncryptionException;
use Gowelle\Flutterwave\Support\EncryptionService;

$testEncryptionKey = 'dGVzdF9rZXlfMzJfYnl0ZXNfbG9uZ19leGFjdGx5ISE=';

describe('ChargeRequestBuilder with Encryption', function () use ($testEncryptionKey) {
    describe('Card Method', function () use ($testEncryptionKey) {
        it('builds charge request with encrypted card data', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $builder = ChargeRequestBuilder::for('ORD-123')
                ->amount(150, 'NGN')
                ->customer('customer@example.com', 'John', 'Doe')
                ->card('5531886652142950', '09', '32', '564')
                ->redirectUrl('https://example.com/callback');

            $dto = $builder->build();
            $request = $dto->toArray();

            expect($request)->toHaveKeys(['reference', 'amount', 'currency', 'customer', 'payment_method', 'redirect_url']);
            expect($request['payment_method'])->toHaveKeys(['type', 'card']);
            expect($request['payment_method']['type'])->toBe('card');
            expect($request['payment_method']['card'])->toHaveKeys([
                'nonce',
                'encrypted_card_number',
                'encrypted_expiry_month',
                'encrypted_expiry_year',
                'encrypted_cvv',
            ]);
        });

        it('includes nonce in encrypted card data', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $builder = ChargeRequestBuilder::for('ORD-456')
                ->amount(100, 'TZS')
                ->customer('user@example.com', 'Jane', 'Doe')
                ->redirectUrl('https://example.com/callback')
                ->card('4532015112830366', '12', '25', '123');

            $dto = $builder->build();
            $request = $dto->toArray();
            $card = $request['payment_method']['card'];

            expect($card['nonce'])->toHaveLength(12);
        });

        it('encrypts card data fields', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $builder = ChargeRequestBuilder::for('ORD-789')
                ->amount(500, 'USD')
                ->customer('test@example.com', 'Test', 'User')
                ->redirectUrl('https://example.com/callback')
                ->card('5531886652142950', '09', '32', '564');

            $dto = $builder->build();
            $request = $dto->toArray();
            $card = $request['payment_method']['card'];

            // Encrypted fields should be base64-encoded strings
            expect($card['encrypted_card_number'])->toBeString();
            expect($card['encrypted_expiry_month'])->toBeString();
            expect($card['encrypted_expiry_year'])->toBeString();
            expect($card['encrypted_cvv'])->toBeString();

            // Should not contain original card data
            $json = json_encode($request);
            expect($json)->not->toContain('5531886652142950');
            expect($json)->not->toContain('564');
        });

        it('handles optional CVV', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $builder = ChargeRequestBuilder::for('ORD-111')
                ->amount(250, 'KES')
                ->customer('customer@test.com', 'Test', 'Customer')
                ->redirectUrl('https://example.com/callback')
                ->card('4532015112830366', '06', '28', '');

            $dto = $builder->build();
            $request = $dto->toArray();
            $card = $request['payment_method']['card'];

            // Should not have encrypted_cvv if CVV was empty
            expect($card)->toHaveKeys([
                'nonce',
                'encrypted_card_number',
                'encrypted_expiry_month',
                'encrypted_expiry_year',
            ]);
        });

        it('includes billing address when provided', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $billingAddress = [
                'city' => 'Lagos',
                'country' => 'NG',
                'line1' => '123 Main St',
                'state' => 'Lagos',
                'postal_code' => '100001',
            ];

            $builder = ChargeRequestBuilder::for('ORD-222')
                ->amount(1000, 'NGN')
                ->customer('user@test.com', 'Test', 'User')
                ->redirectUrl('https://example.com/callback')
                ->card(
                    '5531886652142950',
                    '09',
                    '32',
                    '564',
                    $billingAddress,
                );

            $dto = $builder->build();
            $request = $dto->toArray();
            $card = $request['payment_method']['card'];

            expect($card)->toHaveKey('billing_address');
            expect($card['billing_address'])->toEqual($billingAddress);
        });

        it('chains with other builder methods', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);
            $builder = ChargeRequestBuilder::for('CHAIN-TEST')
                ->amount(750, 'ZAR')
                ->customer('chain@test.com', 'Chain', 'Tester')
                ->card('4532015112830366', '03', '30', '789')
                ->redirectUrl('https://example.com/success')
                ->meta(['order_id' => '12345'])
                ->customizations('Test Shop', 'Payment for order #12345');

            $dto = $builder->build();
            $request = $dto->toArray();

            expect($request)
                ->toHaveKeys([
                    'reference',
                    'amount',
                    'currency',
                    'customer',
                    'payment_method',
                    'redirect_url',
                    'meta',
                    'customizations',
                ]);
            expect($request['payment_method'])->toHaveKeys(['type', 'card']);
        });

        it('throws exception when encryption key is missing', function () {
            config(['flutterwave.encryption_key' => null]);

            expect(fn () => ChargeRequestBuilder::for('ORD-ERR')
                ->amount(100, 'NGN')
                ->customer('err@test.com', 'Error', 'Test')
                ->card('5531886652142950', '09', '32', '564'))
                ->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid card data', function () {
            expect(fn () => ChargeRequestBuilder::for('ORD-BAD')
                ->amount(100, 'NGN')
                ->customer('bad@test.com', 'Bad', 'Card')
                ->card('invalid', '09', '32', '564'))
                ->toThrow(EncryptionException::class);
        });
    });

    describe('Encryption Service Injection', function () use ($testEncryptionKey) {
        it('allows custom encryption service injection', function () use ($testEncryptionKey) {
            $customService = new EncryptionService($testEncryptionKey);

            $builder = ChargeRequestBuilder::for('CUSTOM-ENC')
                ->amount(200, 'GHS')
                ->customer('custom@test.com', 'Custom', 'User')
                ->redirectUrl('https://example.com/callback')
                ->withEncryptionService($customService)
                ->card('5531886652142950', '09', '32', '564');

            $dto = $builder->build();
            $request = $dto->toArray();

            expect($request['payment_method']['card'])->toHaveKeys([
                'nonce',
                'encrypted_card_number',
                'encrypted_expiry_month',
                'encrypted_expiry_year',
                'encrypted_cvv',
            ]);
        });
    });

    describe('Complete Charge Request', function () use ($testEncryptionKey) {
        it('builds a complete card charge request', function () use ($testEncryptionKey) {
            config(['flutterwave.encryption_key' => $testEncryptionKey]);

            $dto = ChargeRequestBuilder::for('ORDER-2024-001')
                ->amount(15000, 'NGN')
                ->customer('john.doe@example.com', 'John', 'Doe', '+234812345678')
                ->card(
                    cardNumber: '5531886652142950',
                    expiryMonth: '09',
                    expiryYear: '32',
                    cvv: '564',
                    billingAddress: [
                        'city' => 'Lagos',
                        'country' => 'NG',
                        'line1' => '123 Victoria Island',
                        'state' => 'Lagos',
                        'postal_code' => '106104',
                    ],
                )
                ->redirectUrl('https://example.com/callback')
                ->meta(['order_id' => 'ORDER-2024-001', 'customer_id' => 'CUST-123'])
                ->customizations(
                    title: 'Payment Gateway',
                    description: 'Complete your purchase securely',
                    logo: 'https://example.com/logo.png',
                )
                ->build();

            $request = $dto->toArray();

            // Verify structure
            expect($request)->toHaveKeys([
                'reference',
                'amount',
                'currency',
                'customer',
                'payment_method',
                'redirect_url',
                'meta',
                'customizations',
            ]);
            expect($request['reference'])->toBe('ORDER-2024-001');
            expect($request['amount'])->toBe(15000.0);
            expect($request['currency'])->toBe('NGN');
            expect($request['customer'])->toHaveKeys(['email', 'first_name', 'last_name', 'phone_number']);
            expect($request['payment_method'])->toHaveKeys(['type', 'card']);
            expect($request['payment_method']['card'])->toHaveKeys([
                'nonce',
                'encrypted_card_number',
                'encrypted_expiry_month',
                'encrypted_expiry_year',
                'encrypted_cvv',
                'billing_address',
            ]);

            // Verify sensitive data is encrypted
            $json = json_encode($request);
            expect($json)->not->toContain('5531886652142950');
            expect($json)->not->toContain('564');
        });
    });
});
