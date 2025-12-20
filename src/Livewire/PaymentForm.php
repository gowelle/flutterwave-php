<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Livewire;

use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Enums\NextActionType;
use Gowelle\Flutterwave\Exceptions\FlutterwaveException;
use Gowelle\Flutterwave\Facades\Flutterwave;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire component for handling Flutterwave card payments.
 *
 * This component manages the complete card payment flow including:
 * - Card data input with real-time validation
 * - Client-side AES-256-GCM encryption
 * - Direct charge creation
 * - Authorization flow handling (PIN, OTP, redirect)
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
class PaymentForm extends Component
{
    // Form fields (received encrypted from JS)
    public string $encryptedCardNumber = '';

    public string $encryptedCvv = '';

    public string $encryptedExpiryMonth = '';

    public string $encryptedExpiryYear = '';

    public string $nonce = '';

    // Customer details
    public string $email = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $phoneNumber = '';

    // Payment details
    public float $amount = 0;

    public string $currency = 'TZS';

    public string $reference = '';

    public string $redirectUrl = '';

    /** @var array<string, mixed> */
    public array $meta = [];

    // State
    public bool $processing = false;

    public string $error = '';

    public ?DirectChargeData $charge = null;

    public ?string $chargeId = null;

    public ?string $currentAction = null;

    public ?string $actionRedirectUrl = null;

    /** @var array<string, mixed> */
    public array $actionData = [];

    /**
     * Mount the component with optional pre-filled data.
     *
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $meta
     */
    public function mount(
        float $amount = 0,
        string $currency = 'TZS',
        string $reference = '',
        string $redirectUrl = '',
        array $customer = [],
        array $meta = [],
    ): void {
        $this->amount = $amount;
        $this->currency = $currency ?: config('flutterwave.default_currency', 'TZS');
        $this->reference = $reference ?: 'FLW-'.uniqid();
        $this->redirectUrl = $redirectUrl;
        $this->meta = $meta;

        // Pre-fill customer details if provided
        if (! empty($customer)) {
            $this->email = $customer['email'] ?? '';
            $this->firstName = $customer['first_name'] ?? $customer['name']['first'] ?? '';
            $this->lastName = $customer['last_name'] ?? $customer['name']['last'] ?? '';
            $this->phoneNumber = $customer['phone_number'] ?? $customer['phone'] ?? '';
        }
    }

    /**
     * Submit the payment form.
     * Receives encrypted card data from JavaScript.
     *
     * @param  array<string, mixed>  $encryptedData
     */
    public function submitPayment(array $encryptedData): void
    {
        $this->processing = true;
        $this->error = '';

        try {
            // Validate required fields
            $this->validate([
                'email' => 'required|email',
                'firstName' => 'required|string|min:2',
                'lastName' => 'required|string|min:2',
                'phoneNumber' => 'required|string|min:10',
                'amount' => 'required|numeric|min:1',
            ]);

            // Validate encrypted data received from JS
            if (empty($encryptedData['nonce']) || empty($encryptedData['encrypted_card_number'])) {
                throw new \InvalidArgumentException(__('flutterwave::messages.card_encryption_missing'));
            }

            // Create the direct charge
            $this->charge = Flutterwave::directCharge()->create([
                'amount' => $this->amount,
                'currency' => $this->currency,
                'reference' => $this->reference,
                'redirect_url' => $this->redirectUrl,
                'meta' => $this->meta,
                'customer' => [
                    'email' => $this->email,
                    'name' => [
                        'first' => $this->firstName,
                        'last' => $this->lastName,
                    ],
                    'phone_number' => $this->phoneNumber,
                ],
                'payment_method' => [
                    'type' => 'card',
                    'card' => [
                        'nonce' => $encryptedData['nonce'],
                        'encrypted_card_number' => $encryptedData['encrypted_card_number'],
                        'encrypted_cvv' => $encryptedData['encrypted_cvv'],
                        'encrypted_expiry_month' => $encryptedData['encrypted_expiry_month'],
                        'encrypted_expiry_year' => $encryptedData['encrypted_expiry_year'],
                    ],
                ],
            ]);

            $this->chargeId = $this->charge->id;

            // Handle the charge result
            $this->handleChargeResult($this->charge);

        } catch (FlutterwaveException $e) {
            $this->error = $e->getUserFriendlyMessage();
            $this->dispatch('payment-error', error: $this->error);
        } catch (\Exception $e) {
            $this->error = __('flutterwave::messages.unexpected_error');
            $this->dispatch('payment-error', error: $this->error);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Handle the charge result and determine next steps.
     */
    protected function handleChargeResult(DirectChargeData $charge): void
    {
        if ($charge->status->isSuccessful()) {
            $this->dispatch('payment-success', charge: $charge->toArray());

            return;
        }

        if ($charge->status->requiresAction()) {
            $this->currentAction = $charge->nextAction->type->value;
            $this->actionData = $charge->nextAction->data ?? [];

            match ($charge->nextAction->type) {
                NextActionType::REQUIRES_PIN => $this->dispatch('requires-pin', chargeId: $charge->id),
                NextActionType::REQUIRES_OTP => $this->dispatch('requires-otp', chargeId: $charge->id),
                NextActionType::REDIRECT_URL => $this->handleRedirect($charge),
                NextActionType::REQUIRES_ADDITIONAL_FIELDS => $this->dispatch('requires-avs', chargeId: $charge->id, fields: $this->actionData),
                NextActionType::PAYMENT_INSTRUCTION => $this->dispatch('payment-instruction', instruction: $this->actionData),
                default => null,
            };

            return;
        }

        if ($charge->status->isTerminal()) {
            $this->error = $charge->getIssuerResponseMessage() ?? __('flutterwave::messages.payment_failed');
            $this->dispatch('payment-failed', charge: $charge->toArray());
        }
    }

    /**
     * Handle redirect authorization (3DS, mobile money, etc.)
     */
    protected function handleRedirect(DirectChargeData $charge): void
    {
        $redirectUrl = $charge->getRedirectUrl();

        if ($redirectUrl) {
            $this->actionRedirectUrl = $redirectUrl;
            $this->dispatch('requires-redirect', url: $redirectUrl, chargeId: $charge->id);
        }
    }

    /**
     * Submit PIN authorization.
     */
    #[On('submit-pin')]
    public function submitPin(string $encryptedPin, string $nonce): void
    {
        if (! $this->chargeId) {
            return;
        }

        $this->processing = true;
        $this->error = '';

        try {
            $this->charge = Flutterwave::directCharge()->updateChargeAuthorization(
                chargeId: $this->chargeId,
                authorizationData: \Gowelle\Flutterwave\Data\AuthorizationData::createPin($nonce, $encryptedPin)
            );

            $this->handleChargeResult($this->charge);
        } catch (FlutterwaveException $e) {
            $this->error = $e->getUserFriendlyMessage();
            $this->dispatch('payment-error', error: $this->error);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Submit OTP authorization.
     */
    #[On('submit-otp')]
    public function submitOtp(string $otp): void
    {
        if (! $this->chargeId) {
            return;
        }

        $this->processing = true;
        $this->error = '';

        try {
            $this->charge = Flutterwave::directCharge()->updateChargeAuthorization(
                chargeId: $this->chargeId,
                authorizationData: \Gowelle\Flutterwave\Data\AuthorizationData::createOtp($otp)
            );

            $this->handleChargeResult($this->charge);
        } catch (FlutterwaveException $e) {
            $this->error = $e->getUserFriendlyMessage();
            $this->dispatch('payment-error', error: $this->error);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Get the encryption key for client-side encryption.
     */
    public function getEncryptionKey(): string
    {
        return config('flutterwave.encryption_key', '');
    }

    public function render(): View
    {
        return view('flutterwave::livewire.payment-form');
    }
}
