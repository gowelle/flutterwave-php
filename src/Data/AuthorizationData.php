<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\NextActionType;

/**
 * Authorization data for submitting customer authorization to Flutterwave.
 *
 * Represents the authorization payload (PIN, OTP, AVS) that needs to be
 * sent to complete a direct charge transaction.
 */
final readonly class AuthorizationData
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public NextActionType $type,
        public array $data,
        public ?string $scenarioKey = null,
    ) {}

    /**
     * Create PIN authorization
     */
    public static function createPin(string $nonce, string $encryptedPin, ?string $scenarioKey = null): self
    {
        return new self(
            type: NextActionType::REQUIRES_PIN,
            data: [
                'type' => 'pin',
                'pin' => [
                    'nonce' => $nonce,
                    'encrypted_pin' => $encryptedPin,
                ],
            ],
            scenarioKey: $scenarioKey,
        );
    }

    /**
     * Create OTP authorization
     */
    public static function createOtp(string $code, ?string $scenarioKey = null): self
    {
        return new self(
            type: NextActionType::REQUIRES_OTP,
            data: [
                'type' => 'otp',
                'otp' => [
                    'code' => $code,
                ],
            ],
            scenarioKey: $scenarioKey,
        );
    }

    /**
     * Create AVS (Address Verification System) authorization
     *
     * @param  array<string, mixed>  $address
     */
    public static function createAvs(array $address, ?string $scenarioKey = null): self
    {
        return new self(
            type: NextActionType::REQUIRES_ADDITIONAL_FIELDS,
            data: [
                'type' => 'avs',
                'avs' => [
                    'address' => $address,
                ],
            ],
            scenarioKey: $scenarioKey,
        );
    }

    /**
     * Create from request data
     *
     * @param  array<string, mixed>  $requestData
     */
    public static function fromRequest(array $requestData): self
    {
        $type = $requestData['type'] ?? 'unknown';

        return match ($type) {
            'pin' => self::createPin(
                $requestData['pin']['nonce'] ?? '',
                $requestData['pin']['encrypted_pin'] ?? '',
            ),
            'otp' => self::createOtp(
                $requestData['otp']['code'] ?? '',
            ),
            'avs' => self::createAvs(
                $requestData['avs']['address'] ?? [],
            ),
            default => new self(
                type: NextActionType::NONE,
                data: $requestData,
            ),
        };
    }

    /**
     * Get authorization payload for API request
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return [
            'authorization' => $this->data,
        ];
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'data' => $this->data,
            'scenario_key' => $this->scenarioKey,
        ];
    }

    public function getScenarioKey(): ?string
    {
        return $this->scenarioKey;
    }
}
