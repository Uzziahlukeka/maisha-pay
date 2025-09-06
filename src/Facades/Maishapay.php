<?php

namespace Uzhlaravel\Maishapay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response processMobileMoneyPayment(\Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney $mobileMoney)
 * @method static \Illuminate\Http\Client\Response processCardPayment(\Uzhlaravel\Maishapay\DataTransferObjects\CardPayment $cardPayment)
 * @method static \Illuminate\Http\Client\Response processEnhancedCardPayment(\Uzhlaravel\Maishapay\DataTransferObjects\CardPayment $cardPayment)
 * @method static \Illuminate\Http\Client\Response verifyTransaction(string $transactionReference)
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
