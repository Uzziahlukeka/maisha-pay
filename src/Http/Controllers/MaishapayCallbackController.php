<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Uzhlaravel\Maishapay\Events\TransactionStatusUpdated;
use Uzhlaravel\Maishapay\Maishapay;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

final class MaishapayCallbackController extends Controller
{
    /**
     * Handle MaishaPay callback notification
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            Log::info('MaishaPay callback received', $data);

            $transactionReference = $data['originatingTransactionId']
                ?? $data['transactionReference']
                ?? null;

            if (! $transactionReference) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction reference is required',
                ], 400);
            }

            $transaction = MaishapayTransaction::query()->where('transaction_reference', $transactionReference)->first();

            if (! $transaction) {
                Log::warning('Transaction not found', ['reference' => $transactionReference]);

                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            $status = Maishapay::extractStatus($data);

            match ($status) {
                'SUCCESS' => $this->transition(fn () => $transaction->markAsSuccessful($data), 'successful', $transactionReference),
                'FAILED' => $this->transition(fn () => $transaction->markAsFailed($data), 'failed', $transactionReference),
                'CANCELLED' => $this->transition(fn () => $transaction->markAsCancelled(), 'cancelled', $transactionReference),
                default => Log::warning('Unhandled transaction status', [
                    'reference' => $transactionReference,
                    'status' => $data['transactionStatus'] ?? $data['status'] ?? null,
                ]),
            };

            event(new TransactionStatusUpdated($transaction->refresh(), $data));

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully',
            ]);

        } catch (Exception $e) {
            Log::error('MaishaPay callback processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
            ], 500);
        }
    }

    private function transition(callable $apply, string $label, string $reference): void
    {
        $apply();

        Log::info("Transaction marked as {$label}", ['reference' => $reference]);
    }
}
