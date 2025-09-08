<?php

namespace Uzhlaravel\Maishapay\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;

/**
 * @method static Response processMobileMoneyPayment(MobileMoney $mobileMoney)
 * @method static Response processCardPayment(CardPayment $cardPayment)
 * @method static Response processEnhancedCardPayment(CardPayment $cardPayment)
 * @method static Response verifyTransaction(string $transactionReference)
 * @method static string generateTransactionReference()
 *
 * @see \Uzhlaravel\Maishapay\MaishapayService
 */
class Maishapay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Uzhlaravel\Maishapay\Maishapay::class;
    }
}
