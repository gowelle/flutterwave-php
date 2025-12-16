<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Contracts;

use Gowelle\Flutterwave\Data\Transfer\BankTransferRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateRecipientRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateSenderRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateTransferRequest;
use Gowelle\Flutterwave\Data\Transfer\GetRateRequest;
use Gowelle\Flutterwave\Data\Transfer\MobileMoneyTransferRequest;
use Gowelle\Flutterwave\Data\Transfer\RateData;
use Gowelle\Flutterwave\Data\Transfer\RecipientData;
use Gowelle\Flutterwave\Data\Transfer\SenderData;
use Gowelle\Flutterwave\Data\Transfer\TransferData;
use Gowelle\Flutterwave\Data\Transfer\WalletTransferRequest;

/**
 * Interface for Flutterwave Transfer Service.
 */
interface TransferServiceInterface
{
    // Orchestrator (Direct Transfers)

    /**
     * Create a bank transfer via orchestrator
     */
    public function bankTransfer(BankTransferRequest $request): TransferData;

    /**
     * Create a mobile money transfer via orchestrator
     */
    public function mobileMoneyTransfer(MobileMoneyTransferRequest $request): TransferData;

    /**
     * Create a wallet transfer via orchestrator
     */
    public function walletTransfer(WalletTransferRequest $request): TransferData;

    // General Flow

    /**
     * Create a transfer using recipient/sender IDs
     */
    public function create(CreateTransferRequest $request): TransferData;

    // Common

    /**
     * Get a transfer by ID
     */
    public function get(string $id): TransferData;

    /**
     * List transfers
     *
     * @return TransferData[]
     */
    public function list(): array;

    /**
     * Retry a failed transfer
     */
    public function retry(string $id): TransferData;

    // Recipients

    /**
     * Create a recipient
     */
    public function createRecipient(CreateRecipientRequest $request): RecipientData;

    /**
     * Get a recipient by ID
     */
    public function getRecipient(string $id): RecipientData;

    /**
     * List recipients
     *
     * @return RecipientData[]
     */
    public function listRecipients(): array;

    /**
     * Delete a recipient
     */
    public function deleteRecipient(string $id): bool;

    // Senders

    /**
     * Create a sender
     */
    public function createSender(CreateSenderRequest $request): SenderData;

    /**
     * Get a sender by ID
     */
    public function getSender(string $id): SenderData;

    /**
     * List senders
     *
     * @return SenderData[]
     */
    public function listSenders(): array;

    // Rates

    /**
     * Get transfer rate
     */
    public function getRate(GetRateRequest $request): RateData;

    /**
     * List available rates
     *
     * @return RateData[]
     */
    public function listRates(): array;
}
