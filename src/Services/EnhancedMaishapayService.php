<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\Services;

use Illuminate\Database\Eloquent\Collection;
use Uzhlaravel\Maishapay\DataTransferObjects\BusinessToCustomer;
use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Events\TransactionStatusUpdated;
use Uzhlaravel\Maishapay\Exceptions\MaishapayException;
use Uzhlaravel\Maishapay\Maishapay;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

final class EnhancedMaishapayService extends Maishapay
{
    /**
     * Process mobile money payment with transaction logging
     */
    public function processMobileMoneyPaymentWithLogging(MobileMoney $mobileMoney): array
    {
        $transactionReference = $mobileMoney->transactionReference ?: $this->generateTransactionReference();

        // Create transaction record
        $transaction = MaishapayTransaction::query()->create([
            'transaction_reference' => $transactionReference,
            'payment_type' => 'MOBILEMONEY',
            'provider' => $mobileMoney->provider,
            'amount' => $mobileMoney->amount,
            'currency' => $mobileMoney->currency,
            'customer_full_name' => $mobileMoney->customerFullName,
            'customer_email' => $mobileMoney->customerEmailAddress,
            'wallet_id' => $mobileMoney->walletId,
            'callback_url' => $mobileMoney->callbackUrl ?: config('maishapay.callback_url'),
            'status' => 'PENDING',
        ]);

        try {
            $mobileMoney->transactionReference = $transactionReference;
            $response = $this->processMobileMoneyPayment($mobileMoney);

            // Update transaction with API response
            $transaction->update([
                'api_response' => $response->json(),
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'response' => $response->json(),
            ];

        } catch (MaishapayException $e) {
            $transaction->markAsFailed(['error' => $e->getMessage()]);

            return [
                'success' => false,
                'transaction' => $transaction,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process card payment with transaction logging
     */
    public function processCardPaymentWithLogging(CardPayment $cardPayment, bool $useV3 = false): array
    {
        $transactionReference = $cardPayment->transactionReference ?: $this->generateTransactionReference();

        // Create transaction record
        $transaction = MaishapayTransaction::create([
            'transaction_reference' => $transactionReference,
            'payment_type' => 'CARD',
            'provider' => $cardPayment->provider,
            'amount' => $cardPayment->amount,
            'currency' => $cardPayment->currency,
            'customer_full_name' => $cardPayment->customerFullName,
            'customer_firstname' => $cardPayment->customerFirstname,
            'customer_lastname' => $cardPayment->customerLastname,
            'customer_email' => $cardPayment->customerEmailAddress,
            'customer_phone' => $cardPayment->customerPhoneNumber,
            'customer_address' => $cardPayment->customerAddress,
            'customer_city' => $cardPayment->customerCity,
            'callback_url' => $cardPayment->callbackUrl ?: config('maishapay.callback_url'),
            'status' => 'PENDING',
        ]);

        try {
            $cardPayment->transactionReference = $transactionReference;

            $response = $useV3
                ? $this->processEnhancedCardPayment($cardPayment)
                : $this->processCardPayment($cardPayment);

            // Update transaction with API response
            $transaction->update([
                'api_response' => $response->json(),
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'response' => $response->json(),
            ];

        } catch (MaishapayException $e) {
            $transaction->markAsFailed(['error' => $e->getMessage()]);

            return [
                'success' => false,
                'transaction' => $transaction,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process B2C payment with transaction logging
     */
    public function processB2CPaymentWithLogging(BusinessToCustomer $b2c): array
    {
        $transactionReference = $b2c->transactionReference ?: $this->generateTransactionReference();

        $transaction = MaishapayTransaction::query()->create([
            'transaction_reference' => $transactionReference,
            'payment_type' => 'B2C',
            'provider' => $b2c->provider,
            'amount' => $b2c->amount,
            'currency' => $b2c->currency,
            'customer_full_name' => $b2c->customerFullName,
            'customer_email' => $b2c->customerEmailAddress,
            'wallet_id' => $b2c->walletId,
            'motif' => $b2c->motif,
            'callback_url' => $b2c->callbackUrl ?: config('maishapay.callback_url'),
            'status' => 'PENDING',
        ]);

        try {
            $b2c->transactionReference = $transactionReference;
            $response = $this->processB2CPayment($b2c);
            $payload = $response->json() ?? [];

            // Unlike collection, a B2C transfer returns its final status
            // (SUCCESS or FAILED) synchronously, so sync the record now instead
            // of waiting for a callback that may never arrive.
            $status = self::extractStatus($payload);

            $transaction->update([
                'api_response' => $payload,
            ]);

            match ($status) {
                'SUCCESS' => $transaction->markAsSuccessful($payload),
                'FAILED' => $transaction->markAsFailed($payload),
                'CANCELLED' => $transaction->markAsCancelled(),
                default => null,
            };

            if ($status !== 'PENDING') {
                event(new TransactionStatusUpdated($transaction->refresh(), $payload));
            }

            return [
                'success' => $status === 'SUCCESS',
                'status' => $status,
                'transaction' => $transaction->refresh(),
                'response' => $payload,
            ];

        } catch (MaishapayException $e) {
            $transaction->markAsFailed(['error' => $e->getMessage()]);

            return [
                'success' => false,
                'transaction' => $transaction,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction by reference (from the local database).
     */
    public function getTransaction(string $transactionReference): ?MaishapayTransaction
    {
        return MaishapayTransaction::where('transaction_reference', $transactionReference)->first();
    }

    /**
     * Fetch the live status of a transaction directly from MaishaPay's servers.
     *
     * Unlike getTransaction(), this does not read the status from the database;
     * it queries MaishaPay's status endpoint and returns the canonical status
     * together with the raw API response.
     *
     * @return array{status: string, response: array}
     */
    public function fetchTransactionStatus(string $transactionReference): array
    {
        $response = $this->checkTransactionStatus($transactionReference);
        $payload = $response->json() ?? [];

        return [
            'status' => self::extractStatus($payload),
            'response' => $payload,
        ];
    }

    /**
     * Get the canonical status of a transaction from MaishaPay's servers.
     *
     * This is the endpoint-backed replacement for reading the status off the
     * local database record.
     */
    public function getTransactionStatus(string $transactionReference): string
    {
        return $this->fetchTransactionStatus($transactionReference)['status'];
    }

    /**
     * Refresh a local transaction record from MaishaPay's servers.
     *
     * Queries the live status from the endpoint, syncs the local database
     * record to match (used as a cache) and fires TransactionStatusUpdated
     * when the status changes. Returns the updated model, or null when no
     * matching local record exists.
     */
    public function refreshTransactionStatus(string $transactionReference): ?MaishapayTransaction
    {
        $transaction = $this->getTransaction($transactionReference);

        if (! $transaction) {
            return null;
        }

        ['status' => $status, 'response' => $payload] = $this->fetchTransactionStatus($transactionReference);

        $previousStatus = $transaction->status;

        match ($status) {
            'SUCCESS' => $transaction->markAsSuccessful($payload),
            'FAILED' => $transaction->markAsFailed($payload),
            'CANCELLED' => $transaction->markAsCancelled(),
            default => null,
        };

        if ($status !== $previousStatus && $status !== 'PENDING') {
            event(new TransactionStatusUpdated($transaction->refresh(), $payload));
        }

        return $transaction->refresh();
    }

    /**
     * Get transactions by status
     */
    public function getTransactionsByStatus(string $status): Collection
    {
        return MaishapayTransaction::query()->where('status', mb_strtoupper($status))->get();
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions(int $limit = 10): Collection
    {
        return MaishapayTransaction::query()->latest()->limit($limit)->get();
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats(): array
    {
        return [
            'total' => MaishapayTransaction::query()->count(),
            'pending' => MaishapayTransaction::pending()->count(),
            'successful' => MaishapayTransaction::successful()->count(),
            'failed' => MaishapayTransaction::failed()->count(),
            'mobile_money' => MaishapayTransaction::mobileMoney()->count(),
            'card' => MaishapayTransaction::card()->count(),
            'b2c' => MaishapayTransaction::b2c()->count(),
        ];
    }
}
