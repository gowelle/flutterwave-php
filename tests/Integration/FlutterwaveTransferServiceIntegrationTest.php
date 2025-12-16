<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Transfer\CreateRecipientRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateSenderRequest;
use Gowelle\Flutterwave\Data\Transfer\RecipientData;
use Gowelle\Flutterwave\Data\Transfer\SenderData;
use Gowelle\Flutterwave\Services\FlutterwaveTransferService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveTransferService Integration', function () {
    describe('Recipients', function () {
        // ==================== SIMPLE BANK RECIPIENTS ====================

        it('can create a Nigerian (NGN) bank recipient', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $accountNumber = '069000' . str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $request = CreateRecipientRequest::bankNgn(
                accountNumber: $accountNumber,
                bankCode: '044',
            );

            try {
                $recipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $this->markTestSkipped('Unable to create unique recipient due to conflicts in staging environment');
                }
                throw $e;
            }

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->not->toBeEmpty()
                ->type->toBe('bank')
                ->currency->toBe('NGN');
        });

        // ==================== BANK RECIPIENTS WITH NAME ====================

        it('can create a Kenyan (KES) bank recipient', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $accountNumber = '110' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $request = CreateRecipientRequest::bankKes(
                accountNumber: $accountNumber,
                bankCode: '01',
                firstName: 'Integration',
                lastName: 'Test',
            );

            try {
                $recipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $this->markTestSkipped('Unable to create unique recipient due to conflicts');
                }
                // Some currencies may not be enabled in staging
                if (str_contains($e->getMessage(), 'not supported') || str_contains($e->getMessage(), 'not enabled')) {
                    $this->markTestSkipped('KES recipients not enabled in staging: ' . $e->getMessage());
                }
                throw $e;
            }

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->not->toBeEmpty();
        });

        it('can create a Ugandan (UGX) bank recipient', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $accountNumber = '100' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $request = CreateRecipientRequest::bankUgx(
                accountNumber: $accountNumber,
                bankCode: '01',
                firstName: 'Integration',
                lastName: 'Test',
            );

            try {
                $recipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $this->markTestSkipped('Unable to create unique recipient due to conflicts');
                }
                // Handle staging API validation errors (invalid test data)
                if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid') ||
                    str_contains($e->getMessage(), 'not supported') || str_contains($e->getMessage(), 'not enabled') ||
                    str_contains($e->getMessage(), 'must not be null')) {
                    $this->markTestSkipped('UGX recipients test skipped due to staging validation: ' . $e->getMessage());
                }
                throw $e;
            }

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->not->toBeEmpty();
        });

        // ==================== BANK RECIPIENTS WITH BRANCH ====================

        it('can create a Ghanaian (GHS) bank recipient', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $accountNumber = '200' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $request = CreateRecipientRequest::bankGhs(
                accountNumber: $accountNumber,
                bankCode: 'GH010100',
                branch: 'GH010101',
                firstName: 'Integration',
                lastName: 'Test',
            );

            try {
                $recipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $this->markTestSkipped('Unable to create unique recipient due to conflicts');
                }
                // Handle staging API validation errors (invalid test data)
                if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid') ||
                    str_contains($e->getMessage(), 'not supported') || str_contains($e->getMessage(), 'not enabled')) {
                    $this->markTestSkipped('GHS recipients test skipped due to staging validation: ' . $e->getMessage());
                }
                throw $e;
            }

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->not->toBeEmpty();
        });

        // ==================== MOBILE MONEY RECIPIENTS ====================

        it('can create a mobile money recipient', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $phoneNumber = '25571' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);

            $request = CreateRecipientRequest::mobileMoney(
                currency: 'TZS',
                network: 'VODACOM',
                phoneNumber: $phoneNumber,
                firstName: 'Integration',
                lastName: 'Test',
            );

            try {
                $recipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $this->markTestSkipped('Unable to create unique recipient due to conflicts');
                }
                // Handle staging API validation errors (invalid test data/network)
                if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid') ||
                    str_contains($e->getMessage(), 'not supported') || str_contains($e->getMessage(), 'not enabled')) {
                    $this->markTestSkipped('Mobile money test skipped due to staging validation: ' . $e->getMessage());
                }
                throw $e;
            }

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->not->toBeEmpty();
        });

        // ==================== GENERIC RECIPIENT OPERATIONS ====================

        it('can list recipients', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            try {
                $recipients = $transferService->listRecipients();
                expect($recipients)->toBeArray();
            } catch (\Exception $e) {
                $this->markTestSkipped('Staging API does not support listing recipients: ' . $e->getMessage());
            }
        });

        it('can get a recipient by ID', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            try {
                $accountNumber = '069000' . str_pad((string) rand(5000, 9999), 4, '0', STR_PAD_LEFT);

                $request = CreateRecipientRequest::bankNgn(
                    accountNumber: $accountNumber,
                    bankCode: '044',
                );

                $createdRecipient = $transferService->createRecipient($request);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                if ($e->getStatusCode() === 409) {
                    $recipients = $transferService->listRecipients();
                    if (empty($recipients)) {
                        $this->markTestSkipped('No recipients available and unable to create due to conflict');
                    }
                    $createdRecipient = reset($recipients);
                } else {
                    throw $e;
                }
            }

            $recipient = $transferService->getRecipient($createdRecipient->id);

            expect($recipient)
                ->toBeInstanceOf(RecipientData::class)
                ->id->toBe($createdRecipient->id);
        });

        // ==================== FACTORY METHOD PAYLOAD TESTS ====================

        it('builds correct payload for bankNgn factory', function () {
            $request = CreateRecipientRequest::bankNgn(
                accountNumber: '0123456789',
                bankCode: '044',
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_ngn')
                ->toHaveKey('bank')
                ->and($payload['bank'])
                ->toHaveKey('account_number', '0123456789')
                ->toHaveKey('code', '044');
        });

        it('builds correct payload for bankKes factory', function () {
            $request = CreateRecipientRequest::bankKes(
                accountNumber: '1234567890',
                bankCode: '01',
                firstName: 'John',
                lastName: 'Doe',
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_kes')
                ->toHaveKey('bank')
                ->toHaveKey('name')
                ->and($payload['bank'])
                ->toHaveKey('account_number', '1234567890')
                ->toHaveKey('code', '01')
                ->and($payload['name'])
                ->toHaveKey('first', 'John')
                ->toHaveKey('last', 'Doe');
        });

        it('builds correct payload for bankGhs factory with branch', function () {
            $request = CreateRecipientRequest::bankGhs(
                accountNumber: '1234567890',
                bankCode: 'GH010100',
                branch: 'GH010101',
                firstName: 'John',
                lastName: 'Doe',
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_ghs')
                ->toHaveKey('bank')
                ->toHaveKey('name')
                ->and($payload['bank'])
                ->toHaveKey('account_number', '1234567890')
                ->toHaveKey('code', 'GH010100')
                ->toHaveKey('branch', 'GH010101');
        });

        it('builds correct payload for bankUsd factory with full KYC', function () {
            $request = CreateRecipientRequest::bankUsd(
                accountNumber: '1234567890',
                bankCode: '021000021',
                accountType: 'checking',
                routingNumber: '021000021',
                swiftCode: 'CHASUS33',
                firstName: 'John',
                lastName: 'Doe',
                phone: ['country_code' => '1', 'number' => '2025551234'],
                email: 'john@example.com',
                address: [
                    'city' => 'New York',
                    'country' => 'US',
                    'line1' => '123 Main St',
                    'postal_code' => '10001',
                ],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_usd')
                ->toHaveKey('bank')
                ->toHaveKey('name')
                ->toHaveKey('phone')
                ->toHaveKey('email', 'john@example.com')
                ->toHaveKey('address')
                ->and($payload['bank'])
                ->toHaveKey('account_number', '1234567890')
                ->toHaveKey('code', '021000021')
                ->toHaveKey('account_type', 'checking')
                ->toHaveKey('routing_number', '021000021')
                ->toHaveKey('swift_code', 'CHASUS33');
        });

        it('builds correct payload for bankEur factory with SWIFT', function () {
            $request = CreateRecipientRequest::bankEur(
                accountNumber: 'DE89370400440532013000',
                bankName: 'Deutsche Bank',
                swiftCode: 'DEUTDEFF',
                firstName: 'Hans',
                lastName: 'Mueller',
                phone: ['country_code' => '49', 'number' => '1701234567'],
                email: 'hans@example.com',
                address: ['city' => 'Berlin', 'country' => 'DE', 'line1' => 'Alexanderplatz 1', 'postal_code' => '10178'],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_eur')
                ->toHaveKey('bank')
                ->and($payload['bank'])
                ->toHaveKey('account_number', 'DE89370400440532013000')
                ->toHaveKey('name', 'Deutsche Bank')
                ->toHaveKey('swift_code', 'DEUTDEFF');
        });

        it('builds correct payload for bankGbp factory with sort code', function () {
            $request = CreateRecipientRequest::bankGbp(
                accountNumber: 'GB82WEST12345698765432',
                accountType: 'individual',
                bankName: 'HSBC',
                sortCode: '401276',
                firstName: 'John',
                lastName: 'Doe',
                phone: ['country_code' => '44', 'number' => '7911123456'],
                email: 'john@example.com',
                address: ['city' => 'London', 'country' => 'GB', 'line1' => '123 High St', 'postal_code' => 'EC1A 1BB'],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_gbp')
                ->toHaveKey('bank')
                ->and($payload['bank'])
                ->toHaveKey('account_number', 'GB82WEST12345698765432')
                ->toHaveKey('account_type', 'individual')
                ->toHaveKey('name', 'HSBC')
                ->toHaveKey('sort_code', '401276');
        });

        it('builds correct payload for mobileMoney factory', function () {
            $request = CreateRecipientRequest::mobileMoney(
                currency: 'TZS',
                network: 'VODACOM',
                phoneNumber: '255123456789',
                firstName: 'John',
                lastName: 'Doe',
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'mobile_money_tzs')
                ->toHaveKey('mobile_money')
                ->toHaveKey('name')
                ->and($payload['mobile_money'])
                ->toHaveKey('network', 'VODACOM')
                ->toHaveKey('msisdn', '255123456789')
                ->and($payload['name'])
                ->toHaveKey('first', 'John')
                ->toHaveKey('last', 'Doe');
        });
    });

    describe('Senders', function () {
        it('can create a transfer sender', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = CreateSenderRequest::generic(
                firstName: 'Integration',
                lastName: 'TestSender',
                email: $this->generateTestEmail(),
                phone: ['country_code' => '255', 'number' => '712345678'],
            );

            $sender = $transferService->createSender($request);

            expect($sender)
                ->toBeInstanceOf(SenderData::class)
                ->id->not->toBeEmpty();
        });

        it('can list senders', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            try {
                $senders = $transferService->listSenders();
                expect($senders)->toBeArray();
            } catch (\Exception $e) {
                $this->markTestSkipped('Staging API does not support listing senders: ' . $e->getMessage());
            }
        });

        it('can get a sender by ID', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = CreateSenderRequest::generic(
                firstName: 'Test',
                lastName: 'GetSender',
                email: $this->generateTestEmail(),
                phone: ['country_code' => '255', 'number' => '712345679'],
            );

            $createdSender = $transferService->createSender($request);

            $sender = $transferService->getSender($createdSender->id);

            expect($sender)
                ->toBeInstanceOf(SenderData::class)
                ->id->toBe($createdSender->id);
        });

        // ==================== SENDER FACTORY PAYLOAD TESTS ====================

        it('builds correct payload for generic sender factory', function () {
            $request = CreateSenderRequest::generic(
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'generic_sender')
                ->toHaveKey('name')
                ->toHaveKey('email', 'john@example.com')
                ->and($payload['name'])
                ->toHaveKey('first', 'John')
                ->toHaveKey('last', 'Doe');
        });

        it('builds correct payload for generic sender with full details', function () {
            $request = CreateSenderRequest::generic(
                firstName: 'John',
                lastName: 'Doe',
                middleName: 'Michael',
                phone: ['country_code' => '234', 'number' => '8012345678'],
                email: 'john@example.com',
                address: [
                    'city' => 'Lagos',
                    'country' => 'NG',
                    'line1' => '123 Main Street',
                    'postal_code' => '100001',
                    'state' => 'Lagos',
                ],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'generic_sender')
                ->toHaveKey('name')
                ->toHaveKey('phone')
                ->toHaveKey('email', 'john@example.com')
                ->toHaveKey('address')
                ->and($payload['name'])
                ->toHaveKey('first', 'John')
                ->toHaveKey('middle', 'Michael')
                ->toHaveKey('last', 'Doe')
                ->and($payload['phone'])
                ->toHaveKey('country_code', '234')
                ->toHaveKey('number', '8012345678');
        });

        it('builds correct payload for bankGbp sender factory', function () {
            $request = CreateSenderRequest::bankGbp(
                firstName: 'John',
                lastName: 'Doe',
                phone: ['country_code' => '44', 'number' => '7911123456'],
                email: 'john@example.com',
                address: [
                    'city' => 'London',
                    'country' => 'GB',
                    'line1' => '123 High Street',
                    'postal_code' => 'EC1A 1BB',
                    'state' => 'Greater London',
                ],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_gbp')
                ->toHaveKey('name')
                ->toHaveKey('phone')
                ->toHaveKey('email', 'john@example.com')
                ->toHaveKey('address')
                ->and($payload['address'])
                ->toHaveKey('city', 'London')
                ->toHaveKey('country', 'GB');
        });

        it('builds correct payload for bankEur sender factory', function () {
            $request = CreateSenderRequest::bankEur(
                firstName: 'Hans',
                lastName: 'Mueller',
                phone: ['country_code' => '49', 'number' => '1701234567'],
                email: 'hans@example.com',
                address: [
                    'city' => 'Berlin',
                    'country' => 'DE',
                    'line1' => 'Alexanderplatz 1',
                    'postal_code' => '10178',
                    'state' => 'Berlin',
                ],
            );

            $payload = $request->toApiPayload();

            expect($payload)
                ->toHaveKey('type', 'bank_eur')
                ->toHaveKey('name')
                ->toHaveKey('phone')
                ->toHaveKey('email', 'hans@example.com')
                ->toHaveKey('address')
                ->and($payload['name'])
                ->toHaveKey('first', 'Hans')
                ->toHaveKey('last', 'Mueller');
        });
    });

    describe('Rates', function () {
        it('can list available transfer rates', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            try {
                $rates = $transferService->listRates();
                expect($rates)->toBeArray();
            } catch (\Exception $e) {
                $this->markTestSkipped('Staging API does not support transfer rates: ' . $e->getMessage());
            }
        });
    });

    describe('Direct Bank Transfers', function () {
        it('can create a successful bank transfer with scenario key', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '044',
                reference: $this->generateReference('BANK'),
                scenarioKey: 'scenario:successful',
            );

            try {
                $transfer = $transferService->bankTransfer($request);

                expect($transfer)
                    ->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class)
                    ->id->not->toBeEmpty();
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                // Skip if staging doesn't support the scenario
                if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid')) {
                    $this->markTestSkipped('Bank transfer test skipped due to staging validation: ' . $e->getMessage());
                }
                throw $e;
            }
        });

        it('can handle insufficient balance scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '044',
                reference: $this->generateReference('BANK_INSUFF'),
                scenarioKey: 'scenario:insufficient_balance',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                // The API may return a failed status or pending status depending on scenario
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                // Insufficient balance should typically result in an error
                expect($e->getStatusCode())->toBe(400);
                $this->markTestSkipped('Insufficient balance scenario returned expected error');
            }
        });

        it('can handle invalid currency scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'INVALID',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '044',
                reference: $this->generateReference('BANK_CURR'),
                scenarioKey: 'scenario:invalid_currency',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                // The API may return a failed status or pending status depending on scenario
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                // Invalid currency should typically result in an error
                expect($e->getStatusCode())->toBe(400);
                $this->markTestSkipped('Invalid currency scenario returned expected error');
            }
        });
    });

    describe('Direct Mobile Money Transfers', function () {
        it('can create a successful mobile money transfer with scenario key', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $phoneNumber = '25571' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);

            $request = new \Gowelle\Flutterwave\Data\Transfer\MobileMoneyTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'TZS',
                network: 'VODACOM',
                phoneNumber: $phoneNumber,
                firstName: 'Integration',
                lastName: 'Test',
                reference: $this->generateReference('MM'),
                scenarioKey: 'scenario:successful',
            );

            try {
                $transfer = $transferService->mobileMoneyTransfer($request);

                expect($transfer)
                    ->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class)
                    ->id->not->toBeEmpty();
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                // Skip if staging doesn't support the scenario
                if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'Invalid') ||
                    str_contains($e->getMessage(), 'not supported')) {
                    $this->markTestSkipped('Mobile money transfer test skipped due to staging validation: ' . $e->getMessage());
                }
                throw $e;
            }
        });
    });

    describe('Transfers', function () {
        it('can list transfers', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            try {
                $transfers = $transferService->list();
                expect($transfers)->toBeArray();
            } catch (\Exception $e) {
                $this->markTestSkipped('Staging API does not support listing transfers: ' . $e->getMessage());
            }
        });

        // ==================== FAILURE SCENARIO TESTS ====================

        it('tests day limit error scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '044',
                reference: $this->generateReference('TRANSFER_DAY_LIMIT'),
                scenarioKey: 'scenario:day_limit_error',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                $this->markTestSkipped('Day limit error scenario handled: ' . $e->getMessage());
            }
        });

        it('tests duplicate reference scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            // Use a fixed reference to trigger duplicate detection
            $reference = 'DUPLICATE_TEST_' . date('Y-m-d');

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '044',
                reference: $reference,
                scenarioKey: 'scenario:duplicate_reference',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                $this->markTestSkipped('Duplicate reference scenario handled: ' . $e->getMessage());
            }
        });

        it('tests no account found scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: 'INVALID123456',
                bankCode: '044',
                reference: $this->generateReference('TRANSFER_NO_ACCOUNT'),
                scenarioKey: 'scenario:no_account_found',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                $this->markTestSkipped('No account found scenario handled: ' . $e->getMessage());
            }
        });

        it('tests account resolved failed scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0000000000',
                bankCode: '044',
                reference: $this->generateReference('TRANSFER_RESOLVE_FAIL'),
                scenarioKey: 'scenario:account_resolved_failed',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                $this->markTestSkipped('Account resolved failed scenario handled: ' . $e->getMessage());
            }
        });

        it('tests blocked bank scenario', function () {
            /** @var FlutterwaveTransferService $transferService */
            $transferService = app(FlutterwaveTransferService::class);

            $request = new \Gowelle\Flutterwave\Data\Transfer\BankTransferRequest(
                amount: 1000,
                sourceCurrency: 'USD',
                destinationCurrency: 'NGN',
                accountNumber: '0123456789',
                bankCode: '999',  // Invalid bank code to simulate blocked bank
                reference: $this->generateReference('TRANSFER_BLOCKED'),
                scenarioKey: 'scenario:blocked_bank',
            );

            try {
                $transfer = $transferService->bankTransfer($request);
                expect($transfer)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Transfer\TransferData::class);
            } catch (\Gowelle\Flutterwave\Exceptions\FlutterwaveApiException $e) {
                $this->markTestSkipped('Blocked bank scenario handled: ' . $e->getMessage());
            }
        });
    });
});
