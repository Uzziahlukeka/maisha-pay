<?php

namespace Uzhlaravel\Maishapay\Services;

use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Exceptions\MaishapayException;
use Uzhlaravel\Maishapay\Maishapay;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

class EnhancedMaishapayService extends Maishapay
{
    /**
     * Process mobile money payment with transaction logging
     */
    public function processMobileMoneyPaymentWithLogging(MobileMoney $mobileMoney): array
    {
        $transactionReference = $mobileMoney->transactionReference ?: $this->generateTransactionReference();

        // Create transaction record
        $transaction = MaishapayTransaction::create([
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
     * Get transaction by reference
     */
    public function getTransaction(string $transactionReference): ?MaishapayTransaction
    {
        return MaishapayTransaction::where('transaction_reference', $transactionReference)->first();
    }

    /**
     * Get transactions by status
     */
    public function getTransactionsByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return MaishapayTransaction::where('status', strtoupper($status))->get();
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return MaishapayTransaction::latest()->limit($limit)->get();
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats(): array
    {
        return [
            'total' => MaishapayTransaction::count(),
            'pending' => MaishapayTransaction::pending()->count(),
            'successful' => MaishapayTransaction::successful()->count(),
            'failed' => MaishapayTransaction::failed()->count(),
            'mobile_money' => MaishapayTransaction::mobileMoney()->count(),
            'card' => MaishapayTransaction::card()->count(),
        ];
    }
}
