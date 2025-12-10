<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\NextActionData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\NextActionType;
use Gowelle\Flutterwave\Models\ChargeSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ChargeSession Model', function () {
    beforeEach(function () {
        // Run migrations for charge sessions table
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    });

    it('can be created with required attributes', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        expect($session)
            ->toBeInstanceOf(ChargeSession::class)
            ->user_id->toBe('user_123')
            ->payment_id->toBe('payment_456')
            ->remote_charge_id->toBe('dc_789')
            ->status->toBe('pending');
    });

    it('generates ULID for id', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        expect($session->id)
            ->toBeString()
            ->toHaveLength(26); // ULID length
    });

    it('casts next_action_data to array', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::REQUIRES_ACTION->value,
            'next_action_type' => NextActionType::REQUIRES_PIN->value,
            'next_action_data' => ['message' => 'Enter PIN'],
        ]);

        expect($session->next_action_data)
            ->toBeArray()
            ->toHaveKey('message', 'Enter PIN');
    });

    it('casts payment_method_details to array', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
            'payment_method_details' => ['type' => 'card', 'last4' => '4242'],
        ]);

        expect($session->payment_method_details)
            ->toBeArray()
            ->toHaveKey('type', 'card')
            ->toHaveKey('last4', '4242');
    });

    it('casts meta to array', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
            'meta' => ['order_id' => '12345', 'custom_field' => 'value'],
        ]);

        expect($session->meta)
            ->toBeArray()
            ->toHaveKey('order_id', '12345');
    });

    it('returns charge status as enum', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::SUCCEEDED->value,
        ]);

        expect($session->getChargeStatus())
            ->toBeInstanceOf(DirectChargeStatus::class)
            ->toBe(DirectChargeStatus::SUCCEEDED);
    });

    it('returns next action type as enum', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::REQUIRES_ACTION->value,
            'next_action_type' => NextActionType::REQUIRES_OTP->value,
        ]);

        expect($session->getNextActionType())
            ->toBeInstanceOf(NextActionType::class)
            ->toBe(NextActionType::REQUIRES_OTP);
    });

    it('returns null for next action type when not set', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        expect($session->getNextActionType())->toBeNull();
    });

    it('returns next action as DTO', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::REQUIRES_ACTION->value,
            'next_action_type' => NextActionType::REDIRECT_URL->value,
            'next_action_data' => ['redirect_url' => ['url' => 'https://bank.com/3ds']],
        ]);

        $nextAction = $session->getNextAction();

        expect($nextAction)
            ->toBeInstanceOf(NextActionData::class)
            ->type->toBe(NextActionType::REDIRECT_URL);
    });

    it('updates next action from DTO', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        $nextAction = NextActionData::fromApi([
            'type' => 'requires_pin',
            'requires_pin' => ['message' => 'Enter your PIN'],
        ]);

        $session->updateNextAction($nextAction);
        $session->refresh();

        expect($session)
            ->next_action_type->toBe('requires_pin')
            ->next_action_data->toHaveKey('message', 'Enter your PIN');
    });

    it('updates charge status', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        $session->updateStatus(DirectChargeStatus::SUCCEEDED);
        $session->refresh();

        expect($session->status)->toBe('succeeded');
        expect($session->getChargeStatus())->toBe(DirectChargeStatus::SUCCEEDED);
    });

    it('checks if session is in terminal state', function () {
        $pendingSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_1',
            'remote_charge_id' => 'dc_1',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        $succeededSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_2',
            'remote_charge_id' => 'dc_2',
            'status' => DirectChargeStatus::SUCCEEDED->value,
        ]);

        expect($pendingSession->isTerminal())->toBeFalse();
        expect($succeededSession->isTerminal())->toBeTrue();
    });

    it('checks if session requires action', function () {
        $requiresActionSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_1',
            'remote_charge_id' => 'dc_1',
            'status' => DirectChargeStatus::REQUIRES_ACTION->value,
        ]);

        $pendingSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_2',
            'remote_charge_id' => 'dc_2',
            'status' => DirectChargeStatus::PENDING->value,
        ]);

        expect($requiresActionSession->requiresAction())->toBeTrue();
        expect($pendingSession->requiresAction())->toBeFalse();
    });

    it('checks if session is successful', function () {
        $succeededSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_1',
            'remote_charge_id' => 'dc_1',
            'status' => DirectChargeStatus::SUCCEEDED->value,
        ]);

        $failedSession = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_2',
            'remote_charge_id' => 'dc_2',
            'status' => DirectChargeStatus::FAILED->value,
        ]);

        expect($succeededSession->isSuccessful())->toBeTrue();
        expect($failedSession->isSuccessful())->toBeFalse();
    });

    it('gets and sets metadata', function () {
        $session = ChargeSession::create([
            'user_id' => 'user_123',
            'payment_id' => 'payment_456',
            'remote_charge_id' => 'dc_789',
            'status' => DirectChargeStatus::PENDING->value,
            'meta' => ['initial_key' => 'initial_value'],
        ]);

        expect($session->getMeta('initial_key'))->toBe('initial_value');
        expect($session->getMeta('missing_key'))->toBeNull();
        expect($session->getMeta('missing_key', 'default'))->toBe('default');

        $session->setMeta('new_key', 'new_value');

        expect($session->meta)->toHaveKey('new_key', 'new_value');
        expect($session->meta)->toHaveKey('initial_key', 'initial_value');
    });

    // Query Scope Tests

    it('scope: filters pending sessions', function () {
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p1', 'remote_charge_id' => 'dc1', 'status' => 'pending']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p2', 'remote_charge_id' => 'dc2', 'status' => 'requires_action']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p3', 'remote_charge_id' => 'dc3', 'status' => 'succeeded']);

        $pendingSessions = ChargeSession::pending()->get();

        expect($pendingSessions)
            ->toHaveCount(2)
            ->each(fn ($session) => $session->status->toBeIn(['pending', 'requires_action']));
    });

    it('scope: filters completed sessions', function () {
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p1', 'remote_charge_id' => 'dc1', 'status' => 'pending']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p2', 'remote_charge_id' => 'dc2', 'status' => 'succeeded']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p3', 'remote_charge_id' => 'dc3', 'status' => 'failed']);

        $completedSessions = ChargeSession::completed()->get();

        expect($completedSessions)
            ->toHaveCount(2)
            ->each(fn ($session) => $session->status->toBeIn(['succeeded', 'failed', 'cancelled', 'timeout']));
    });

    it('scope: filters by specific status', function () {
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p1', 'remote_charge_id' => 'dc1', 'status' => 'succeeded']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p2', 'remote_charge_id' => 'dc2', 'status' => 'failed']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p3', 'remote_charge_id' => 'dc3', 'status' => 'pending']);

        $succeededSessions = ChargeSession::withStatus(DirectChargeStatus::SUCCEEDED)->get();
        $failedSessions = ChargeSession::withStatus('failed')->get();

        expect($succeededSessions)->toHaveCount(1);
        expect($failedSessions)->toHaveCount(1);
    });

    it('scope: finds by remote charge id', function () {
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p1', 'remote_charge_id' => 'dc_target', 'status' => 'succeeded']);
        ChargeSession::create(['user_id' => 'u1', 'payment_id' => 'p2', 'remote_charge_id' => 'dc_other', 'status' => 'pending']);

        $session = ChargeSession::byRemoteChargeId('dc_target')->first();

        expect($session)
            ->not->toBeNull()
            ->remote_charge_id->toBe('dc_target')
            ->status->toBe('succeeded');
    });
});
