<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Models;

use Gowelle\Flutterwave\Data\NextActionData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\NextActionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Flutterwave Charge Session Model
 *
 * Tracks the state of a direct charge transaction that may require
 * multiple authorization steps (PIN, OTP, AVS, etc.).
 *
 * @property string $id
 * @property string $user_id
 * @property string $payment_id
 * @property string $remote_charge_id
 * @property string $status
 * @property string|null $next_action_type
 * @property array|null $next_action_data
 * @property string|null $payment_method_type
 * @property array|null $payment_method_details
 * @property string|null $remote_customer_id
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model $user
 * @property-read \Illuminate\Database\Eloquent\Model $payment
 *
 * @method static Builder<static>|ChargeSession newModelQuery()
 * @method static Builder<static>|ChargeSession newQuery()
 * @method static Builder<static>|ChargeSession query()
 * @method static Builder<static>|ChargeSession whereId($value)
 * @method static Builder<static>|ChargeSession whereUserId($value)
 * @method static Builder<static>|ChargeSession wherePaymentId($value)
 * @method static Builder<static>|ChargeSession whereRemoteChargeId($value)
 * @method static Builder<static>|ChargeSession whereStatus($value)
 * @method static Builder<static>|ChargeSession byRemoteChargeId(string $remoteChargeId)
 * @method static Builder<static>|ChargeSession completed()
 * @method static Builder<static>|ChargeSession pending()
 * @method static Builder<static>|ChargeSession whereCreatedAt($value)
 * @method static Builder<static>|ChargeSession whereMeta($value)
 * @method static Builder<static>|ChargeSession whereNextActionData($value)
 * @method static Builder<static>|ChargeSession whereNextActionType($value)
 * @method static Builder<static>|ChargeSession wherePaymentMethodDetails($value)
 * @method static Builder<static>|ChargeSession wherePaymentMethodType($value)
 * @method static Builder<static>|ChargeSession whereRemoteCustomerId($value)
 * @method static Builder<static>|ChargeSession whereUpdatedAt($value)
 * @method static Builder<static>|ChargeSession withStatus(\App\Domain\Flutterwave\Enums\DirectChargeStatus|string $status)
 *
 * @mixin \Eloquent
 */
final class ChargeSession extends Model
{
    use HasFactory;
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'flutterwave_charge_sessions';

    protected $guarded = ['id'];

    /**
     * Get the user that owns the charge session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('flutterwave.models.user'));
    }

    /**
     * Get the payment associated with this charge session.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(config('flutterwave.models.payment'));
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, DirectChargeStatus|string $status): void
    {
        $statusValue = $status instanceof DirectChargeStatus ? $status->value : $status;
        $query->where('status', $statusValue);
    }

    /**
     * Scope to filter pending sessions.
     */
    public function scopePending(Builder $query): void
    {
        $query->whereIn('status', [
            DirectChargeStatus::PENDING->value,
            DirectChargeStatus::REQUIRES_ACTION->value,
        ]);
    }

    /**
     * Scope to filter completed (terminal) sessions.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->whereIn('status', [
            DirectChargeStatus::SUCCEEDED->value,
            DirectChargeStatus::FAILED->value,
            DirectChargeStatus::CANCELLED->value,
            DirectChargeStatus::TIMEOUT->value,
        ]);
    }

    /**
     * Scope to find by remote charge ID.
     */
    public function scopeByRemoteChargeId(Builder $query, string $remoteChargeId): void
    {
        $query->where('remote_charge_id', $remoteChargeId);
    }

    /**
     * Get the charge status as enum.
     */
    public function getChargeStatus(): DirectChargeStatus
    {
        return DirectChargeStatus::from($this->status);
    }

    /**
     * Get the next action type as enum.
     */
    public function getNextActionType(): ?NextActionType
    {
        if ($this->next_action_type === null) {
            return null;
        }

        return NextActionType::tryFrom($this->next_action_type);
    }

    /**
     * Get next action data as DTO.
     */
    public function getNextAction(): NextActionData
    {
        return NextActionData::fromApi([
            'type' => $this->next_action_type,
            ...$this->next_action_data ?? [],
        ]);
    }

    /**
     * Update next action from DTO.
     */
    public function updateNextAction(NextActionData $nextAction): bool
    {
        return $this->update([
            'next_action_type' => $nextAction->type->value,
            'next_action_data' => $nextAction->data,
        ]);
    }

    /**
     * Update charge status.
     */
    public function updateStatus(DirectChargeStatus $status): bool
    {
        return $this->update([
            'status' => $status->value,
        ]);
    }

    /**
     * Check if charge session is in terminal state.
     */
    public function isTerminal(): bool
    {
        return $this->getChargeStatus()->isTerminal();
    }

    /**
     * Check if charge session requires action.
     */
    public function requiresAction(): bool
    {
        return $this->getChargeStatus()->requiresAction();
    }

    /**
     * Check if charge session is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->getChargeStatus()->isSuccessful();
    }

    /**
     * Get metadata value by key.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }

    /**
     * Set metadata value.
     */
    public function setMeta(string $key, mixed $value): void
    {
        $meta = $this->meta ?? [];
        $meta[$key] = $value;
        $this->meta = $meta;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_action_data' => 'array',
            'payment_method_details' => 'array',
            'meta' => 'array',
        ];
    }
}
