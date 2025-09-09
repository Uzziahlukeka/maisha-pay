<?php

namespace Uzhlaravel\Maishapay\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $transaction_reference
 * @property string $payment_type
 * @property string $provider
 * @property float $amount
 * @property string $currency
 * @property string $customer_full_name
 * @property string $customer_firstname
 * @property string $customer_lastname
 * @property string $customer_email
 * @property string $customer_phone
 * @property string $customer_address
 * @property string $customer_city
 * @property string $wallet_id
 * @property string $callback_url
 * @property string $status // Add this line
 * @property array $api_response
 * @property array $callback_data
 * @property \Carbon\Carbon $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MaishapayTransaction extends Model
{
    protected $fillable = [
        'transaction_reference',
        'payment_type',
        'provider',
        'amount',
        'currency',
        'customer_full_name',
        'customer_firstname',
        'customer_lastname',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'wallet_id',
        'callback_url',
        'status',
        'api_response',
        'callback_data',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'api_response' => 'array',
        'callback_data' => 'array',
        'processed_at' => 'datetime',
    ];

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'SUCCESS');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'FAILED');
    }

    public function scopeMobileMoney(Builder $query): Builder
    {
        return $query->where('payment_type', 'MOBILEMONEY');
    }

    public function scopeCard(Builder $query): Builder
    {
        return $query->where('payment_type', 'CARD');
    }

    public function scopeByProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    public function markAsSuccessful(array $callbackData = []): void
    {
        $this->update([
            'status' => 'SUCCESS',
            'callback_data' => $callbackData,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(array $callbackData = []): void
    {
        $this->update([
            'status' => 'FAILED',
            'callback_data' => $callbackData,
            'processed_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'CANCELLED',
            'processed_at' => now(),
        ]);
    }
}
