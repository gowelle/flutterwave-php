<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\NextActionType;

/**
 * Next action data for direct charge authorization flow.
 *
 * Represents the action a customer must take to complete a charge,
 * including any data needed to display or process the action.
 */
final readonly class NextActionData
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public NextActionType $type,
        public ?array $data = null,
    ) {}

    /**
     * Create from API response
     *
     * @param  array<string, mixed>|null  $nextAction
     */
    public static function fromApi(?array $nextAction): self
    {
        if ($nextAction === null || empty($nextAction)) {
            return new self(
                type: NextActionType::NONE,
                data: null,
            );
        }

        $type = NextActionType::fromApiResponse($nextAction);

        // Extract type-specific data
        $data = match ($type) {
            NextActionType::REDIRECT_URL => [
                'url' => $nextAction['redirect_url']['url'] ?? null,
            ],
            NextActionType::PAYMENT_INSTRUCTION => [
                'note' => $nextAction['payment_instruction']['note'] ?? null,
                'instructions' => $nextAction['payment_instruction']['instructions'] ?? null,
            ],
            NextActionType::REQUIRES_PIN => [
                'message' => $nextAction['requires_pin']['message'] ?? 'Please enter your card PIN',
            ],
            NextActionType::REQUIRES_OTP => [
                'message' => $nextAction['requires_otp']['message'] ?? 'Please enter the OTP sent to you',
                'reference' => $nextAction['requires_otp']['reference'] ?? null,
            ],
            NextActionType::REQUIRES_ADDITIONAL_FIELDS => [
                'fields' => $nextAction['requires_additional_fields']['fields'] ?? [],
                'message' => $nextAction['requires_additional_fields']['message'] ?? 'Please provide additional information',
            ],
            NextActionType::NONE => null,
        };

        return new self(
            type: $type,
            data: $data,
        );
    }

    /**
     * Get redirect URL if action type is redirect
     */
    public function getRedirectUrl(): ?string
    {
        if ($this->type !== NextActionType::REDIRECT_URL) {
            return null;
        }

        return $this->data['url'] ?? null;
    }

    /**
     * Get payment instruction note if action type is payment instruction
     */
    public function getPaymentInstructionNote(): ?string
    {
        if ($this->type !== NextActionType::PAYMENT_INSTRUCTION) {
            return null;
        }

        return $this->data['note'] ?? null;
    }

    /**
     * Get message for customer-input actions
     */
    public function getMessage(): ?string
    {
        if (! $this->type->requiresCustomerInput()) {
            return null;
        }

        return $this->data['message'] ?? null;
    }

    /**
     * Check if action requires customer input
     */
    public function requiresCustomerInput(): bool
    {
        return $this->type->requiresCustomerInput();
    }

    /**
     * Check if action requires redirect
     */
    public function requiresRedirect(): bool
    {
        return $this->type->requiresRedirect();
    }

    /**
     * Check if action is asynchronous
     */
    public function isAsynchronous(): bool
    {
        return $this->type->isAsynchronous();
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
        ];
    }
}
