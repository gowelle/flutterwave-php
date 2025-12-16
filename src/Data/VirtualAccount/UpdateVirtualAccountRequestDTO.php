<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\VirtualAccount;

use Gowelle\Flutterwave\Enums\VirtualAccountStatus;
use Gowelle\Flutterwave\Enums\VirtualAccountUpdateAction;

/**
 * Update Virtual Account Request DTO
 *
 * Represents the payload for updating a virtual account.
 */
final class UpdateVirtualAccountRequestDTO
{
    /**
     * @param  VirtualAccountUpdateAction  $actionType  Type of update (update_bvn or update_status)
     * @param  ?VirtualAccountStatus  $status  New account status (for update_status action)
     * @param  ?string  $bvn  New BVN (for update_bvn action)
     * @param  ?array  $meta  Custom metadata
     */
    public function __construct(
        public VirtualAccountUpdateAction $actionType,
        public ?VirtualAccountStatus $status = null,
        public ?string $bvn = null,
        public ?array $meta = null,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            actionType: $data['action_type'] instanceof VirtualAccountUpdateAction
                ? $data['action_type']
                : VirtualAccountUpdateAction::fromApiResponse($data['action_type']),
            status: isset($data['status'])
                ? ($data['status'] instanceof VirtualAccountStatus
                    ? $data['status']
                    : VirtualAccountStatus::fromApiResponse($data['status']))
                : null,
            bvn: $data['bvn'] ?? null,
            meta: $data['meta'] ?? null,
        );
    }

    /**
     * Convert to API request format
     */
    public function toArray(): array
    {
        $data = [
            'action_type' => $this->actionType->value,
        ];

        if ($this->status !== null) {
            $data['status'] = $this->status->value;
        }

        if ($this->bvn !== null) {
            $data['bvn'] = $this->bvn;
        }

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }

    /**
     * Create a BVN update request
     */
    public static function forBvnUpdate(string $bvn, ?array $meta = null): self
    {
        return new self(
            actionType: VirtualAccountUpdateAction::UPDATE_BVN,
            bvn: $bvn,
            meta: $meta,
        );
    }

    /**
     * Create a status update request
     */
    public static function forStatusUpdate(VirtualAccountStatus $status, ?array $meta = null): self
    {
        return new self(
            actionType: VirtualAccountUpdateAction::UPDATE_STATUS,
            status: $status,
            meta: $meta,
        );
    }
}
