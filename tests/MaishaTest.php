<?php

use Illuminate\Support\Facades\Http;
use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;

beforeEach(function () {
    $this->publicKey = 'MP-SBPK-test123';
    $this->secretKey = 'MP-SBSK-test456';
});

it('can create mobile money DTO', function () {
    $mobileMoney = MobileMoney::create([
        'amount' => '1000',
        'currency' => 'CDF',
        'customer_full_name' => 'John Doe',
        'customer_email_address' => 'john@example.com',
        'provider' => 'AIRTEL',
        'wallet_id' => '+243991234567',
    ]);

    expect($mobileMoney->amount)->toBe('1000')
        ->and($mobileMoney->currency)->toBe('CDF')
        ->and($mobileMoney->provider)->toBe('AIRTEL');
});

it('validates mobile money provider', function () {
    expect(fn () => MobileMoney::create([
        'amount' => '1000',
        'currency' => 'CDF',
        'customer_full_name' => 'John Doe',
        'customer_email_address' => 'john@example.com',
        'provider' => 'INVALID_PROVIDER',
        'wallet_id' => '+243991234567',
    ]))->toThrow(InvalidArgumentException::class);
});

it('can create card payment DTO for v2', function () {
    $cardPayment = CardPayment::createForV2([
        'amount' => '100',
        'currency' => 'USD',
        'customer_full_name' => 'John Doe',
        'customer_email_address' => 'john@example.com',
        'customer_phone_number' => '+1234567890',
        'provider' => 'VISA',
    ]);

    expect($cardPayment->amount)->toBe('100')
        ->and($cardPayment->currency)->toBe('USD')
        ->and($cardPayment->provider)->toBe('VISA');
});

it('can create card payment DTO for v3', function () {
    $cardPayment = CardPayment::createForV3([
        'amount' => '100',
        'currency' => 'USD',
        'customer_firstname' => 'John',
        'customer_lastname' => 'Doe',
        'customer_email_address' => 'john@example.com',
        'customer_phone_number' => '+1234567890',
        'provider' => 'MASTERCARD',
    ]);

    expect($cardPayment->customerFirstname)->toBe('John')
        ->and($cardPayment->customerLastname)->toBe('Doe')
        ->and($cardPayment->provider)->toBe('MASTERCARD');
});

it('can process mobile money payment', function () {
    Http::fake([
        'marchand.maishapay.online/*' => Http::response([
            'success' => true,
            'transactionReference' => 'MP_TEST123',
            'status' => 'PENDING',
        ]),
    ]);

    $service = new \Uzhlaravel\Maishapay\Maishapay($this->publicKey, $this->secretKey, 0);

    $mobileMoney = MobileMoney::create([
        'amount' => '1000',
        'currency' => 'CDF',
        'customer_full_name' => 'John Doe',
        'customer_email_address' => 'john@example.com',
        'provider' => 'AIRTEL',
        'wallet_id' => '+243991234567',
    ]);

    $response = $service->processMobileMoneyPayment($mobileMoney);

    expect($response->successful())->toBe(true)
        ->and($response->json('success'))->toBe(true);
});

it('can create transaction record', function () {
    $transaction = MaishapayTransaction::create([
        'transaction_reference' => 'TEST_REF_123',
        'payment_type' => 'MOBILEMONEY',
        'provider' => 'AIRTEL',
        'amount' => 1000,
        'currency' => 'CDF',
        'customer_full_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'PENDING',
    ]);

    expect($transaction->transaction_reference)->toBe('TEST_REF_123')
        ->and($transaction->isPending())->toBe(true)
        ->and($transaction->isSuccessful())->toBe(false);
});

it('can mark transaction as successful', function () {
    $transaction = MaishapayTransaction::create([
        'transaction_reference' => 'TEST_REF_456',
        'payment_type' => 'CARD',
        'provider' => 'VISA',
        'amount' => 100,
        'currency' => 'USD',
        'customer_email' => 'john@example.com',
        'status' => 'PENDING',
    ]);

    $transaction->markAsSuccessful(['payment_id' => 'PAY_123']);

    expect($transaction->fresh()->isSuccessful())->toBe(true)
        ->and($transaction->fresh()->callback_data)->toHaveKey('payment_id');
});

it('can use facade', function () {
    Http::fake([
        'marchand.maishapay.online/*' => Http::response([
            'success' => true,
            'transactionReference' => 'MP_FACADE_TEST',
            'status' => 'PENDING',
        ]),
    ]);

    $mobileMoney = MobileMoney::create([
        'amount' => '500',
        'currency' => 'USD',
        'customer_full_name' => 'Jane Doe',
        'customer_email_address' => 'jane@example.com',
        'provider' => 'MTN',
        'wallet_id' => '+243991234567',
    ]);

    $maisha = new Uzhlaravel\Maishapay\Maishapay($this->publicKey, $this->secretKey);
    $response = $maisha->processMobileMoneyPayment($mobileMoney);

    expect($response->successful())->toBe(true);
});

it('generates unique transaction reference', function () {
    $service = new \Uzhlaravel\Maishapay\Maishapay($this->publicKey, $this->secretKey);

    $ref1 = $service->generateTransactionReference();
    $ref2 = $service->generateTransactionReference();

    expect($ref1)->toStartWith('MP_')
        ->and($ref2)->toStartWith('MP_')
        ->and($ref1)->not->toBe($ref2);
});

it('throws exception', function () {
    throw new Exception('Something happened.');
})->throws(Exception::class);
