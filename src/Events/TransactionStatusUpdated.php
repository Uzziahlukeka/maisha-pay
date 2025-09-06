<?php

namespace Uzhlaravel\Maishapay\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

class TransactionStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MaishapayTransaction $transaction,
        public array $callbackData = []
    ) {}

}
