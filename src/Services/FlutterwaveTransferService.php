<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Api\Transfer\TransferApi;
use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Contracts\TransferServiceInterface;
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
use Gowelle\Flutterwave\Events\FlutterwaveTransferCreated;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

/**
 * Flutterwave Transfer Service
 *
 * Handles all transfer operations including:
 * - Direct transfers (orchestrator: bank, mobile money, wallet)
 * - General flow transfers (using recipient/sender IDs)
 * - Recipients management
 * - Senders management
 * - Transfer rates
 */
final class FlutterwaveTransferService implements TransferServiceInterface
{
    use BuildsWavable;

    public function __construct(
        private readonly FlutterwaveBaseService $flutterwaveBaseService,
    ) {}

    // ==================== ORCHESTRATOR (Direct Transfers) ====================

    /**
     * Create a bank transfer via direct transfer orchestrator
     */
    public function bankTransfer(BankTransferRequest $request): TransferData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::DIRECT_TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::DIRECT_TRANSFER,
            $wavable,
            $request->toApiPayload(),
        );

        $transferData = TransferData::fromApi($response->data);
        event(new FlutterwaveTransferCreated($transferData));

        return $transferData;
    }

    /**
     * Create a mobile money transfer via direct transfer orchestrator
     */
    public function mobileMoneyTransfer(MobileMoneyTransferRequest $request): TransferData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::DIRECT_TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::DIRECT_TRANSFER,
            $wavable,
            $request->toApiPayload(),
        );

        $transferData = TransferData::fromApi($response->data);
        event(new FlutterwaveTransferCreated($transferData));

        return $transferData;
    }

    /**
     * Create a wallet-to-wallet transfer via direct transfer orchestrator
     */
    public function walletTransfer(WalletTransferRequest $request): TransferData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::DIRECT_TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::DIRECT_TRANSFER,
            $wavable,
            $request->toApiPayload(),
        );

        $transferData = TransferData::fromApi($response->data);
        event(new FlutterwaveTransferCreated($transferData));

        return $transferData;
    }

    // ==================== GENERAL FLOW ====================

    /**
     * Create a transfer using the general flow (with recipient/sender IDs)
     */
    public function create(CreateTransferRequest $request): TransferData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::TRANSFER,
            $wavable,
            $request->toApiPayload(),
        );

        $transferData = TransferData::fromApi($response->data);
        event(new FlutterwaveTransferCreated($transferData));

        return $transferData;
    }

    // ==================== COMMON ====================

    /**
     * Get a transfer by ID
     */
    public function get(string $id): TransferData
    {
        $wavable = $this->buildWavable(
            ['id' => $id],
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::TRANSFER, $wavable, $id);

        return TransferData::fromApi($response->data);
    }

    /**
     * List all transfers
     *
     * @return TransferData[]
     */
    public function list(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::TRANSFER, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => TransferData::fromApi($item), $response->data);
    }

    /**
     * Retry a failed transfer
     */
    public function retry(string $id): TransferData
    {
        $headers = $this->flutterwaveBaseService->getHeaderBuilder()->build(
            $this->buildWavable(
                ['id' => $id],
                FlutterwaveApi::TRANSFER,
                $this->flutterwaveBaseService->getConfig()->isProduction(),
            )
        );

        /** @var TransferApi $api */
        $api = app(FlutterwaveApiProvider::class)->useApi(
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getAccessToken(),
            $headers
        );

        $response = $api->retry($id);

        return TransferData::fromApi($response->data);
    }

    // ==================== RECIPIENTS ====================

    /**
     * Create a transfer recipient
     */
    public function createRecipient(CreateRecipientRequest $request): RecipientData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::TRANSFER_RECIPIENTS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::TRANSFER_RECIPIENTS,
            $wavable,
            $request->toApiPayload(),
        );

        return RecipientData::fromApi($response->data);
    }

    /**
     * Get a recipient by ID
     */
    public function getRecipient(string $id): RecipientData
    {
        $wavable = $this->buildWavable(
            ['id' => $id],
            FlutterwaveApi::TRANSFER_RECIPIENTS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::TRANSFER_RECIPIENTS, $wavable, $id);

        return RecipientData::fromApi($response->data);
    }

    /**
     * List all recipients
     *
     * @return RecipientData[]
     */
    public function listRecipients(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::TRANSFER_RECIPIENTS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::TRANSFER_RECIPIENTS, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => RecipientData::fromApi($item), $response->data);
    }

    /**
     * Delete a recipient
     */
    public function deleteRecipient(string $id): bool
    {
        $headers = $this->flutterwaveBaseService->getHeaderBuilder()->build(
            $this->buildWavable(
                ['id' => $id],
                FlutterwaveApi::TRANSFER_RECIPIENTS,
                $this->flutterwaveBaseService->getConfig()->isProduction(),
            )
        );

        /** @var TransferApi $api */
        $api = app(FlutterwaveApiProvider::class)->useApi(
            FlutterwaveApi::TRANSFER_RECIPIENTS,
            $this->flutterwaveBaseService->getAccessToken(),
            $headers
        );

        $response = $api->deleteRecipient($id);

        return $response->status === 'success';
    }

    // ==================== SENDERS ====================

    /**
     * Create a transfer sender
     */
    public function createSender(CreateSenderRequest $request): SenderData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::TRANSFER_SENDERS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::TRANSFER_SENDERS,
            $wavable,
            $request->toApiPayload(),
        );

        return SenderData::fromApi($response->data);
    }

    /**
     * Get a sender by ID
     */
    public function getSender(string $id): SenderData
    {
        $wavable = $this->buildWavable(
            ['id' => $id],
            FlutterwaveApi::TRANSFER_SENDERS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::TRANSFER_SENDERS, $wavable, $id);

        return SenderData::fromApi($response->data);
    }

    /**
     * List all senders
     *
     * @return SenderData[]
     */
    public function listSenders(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::TRANSFER_SENDERS,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::TRANSFER_SENDERS, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => SenderData::fromApi($item), $response->data);
    }

    // ==================== RATES ====================

    /**
     * Get transfer rate for a currency pair
     */
    public function getRate(GetRateRequest $request): RateData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::TRANSFER_RATES,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::TRANSFER_RATES,
            $wavable,
            $request->toApiPayload(),
        );

        return RateData::fromApi($response->data);
    }

    /**
     * List available transfer rates
     *
     * @return RateData[]
     */
    public function listRates(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::TRANSFER_RATES,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::TRANSFER_RATES, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => RateData::fromApi($item), $response->data);
    }
}
