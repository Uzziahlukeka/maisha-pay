<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

final class TransactionStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MaishapayTransaction $transaction,
        public array $callbackData = []
    ) {}

}
