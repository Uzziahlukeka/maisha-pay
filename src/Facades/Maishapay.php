<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\MaishapayService;

/**
 * @method static Response processMobileMoneyPayment(MobileMoney $mobileMoney)
 * @method static Response processCardPayment(CardPayment $cardPayment)
 * @method static Response processEnhancedCardPayment(CardPayment $cardPayment)
 * @method static Response checkTransactionStatus(string $transactionReference)
 * @method static Response checkTransactionById(string|int $transactionId)
 * @method static Response verifyTransaction(string $transactionReference)
 * @method static string extractStatus(array $payload)
 * @method static string normalizeStatus(string $status)
 * @method static string generateTransactionReference()
 *
 * @see MaishapayService
 */
final class Maishapay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Uzhlaravel\Maishapay\Maishapay::class;
    }
}
