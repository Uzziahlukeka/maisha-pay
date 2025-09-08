<?php

namespace Uzhlaravel\Maishapay\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Uzhlaravel\Maishapay\Events\TransactionStatusUpdated;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

class MaishapayCallbackController extends Controller
{
    /**
     * Handle MaishaPay callback notification
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            Log::info('MaishaPay callback received', $data);

            // Validate required fields
            if (! isset($data['transactionReference'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction reference is required',
                ], 400);
            }

            $transactionReference = $data['transactionReference'];

            // Find the transaction
            $transaction = MaishapayTransaction::query()->where('transaction_reference', $transactionReference)->first();

            if (! $transaction) {
                Log::warning('Transaction not found', ['reference' => $transactionReference]);

                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            // Update transaction based on status
            $status = $data['status'] ?? $data['paymentStatus'] ?? 'UNKNOWN';

            switch (strtoupper($status)) {
                case 'SUCCESS':
                case 'SUCCESSFUL':
                case 'COMPLETED':
                    $transaction->markAsSuccessful($data);
                    Log::info('Transaction marked as successful', ['reference' => $transactionReference]);
                    break;

                case 'FAILED':
                case 'FAILURE':
                case 'ERROR':
                    $transaction->markAsFailed($data);
                    Log::info('Transaction marked as failed', ['reference' => $transactionReference]);
                    break;

                case 'CANCELLED':
                case 'CANCELED':
                    $transaction->markAsCancelled();
                    Log::info('Transaction marked as cancelled', ['reference' => $transactionReference]);
                    break;

                default:
                    Log::warning('Unknown transaction status', [
                        'reference' => $transactionReference,
                        'status' => $status,
                    ]);
                    break;
            }

            // Fire event for further processing
            event(new TransactionStatusUpdated($transaction, $data));

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
}
