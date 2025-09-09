<?php

namespace Uzhlaravel\Maishapay;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Uzhlaravel\Maishapay\DataTransferObjects\CardPayment;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Exceptions\MaishapayException;

class Maishapay
{
    protected string $publicKey;

    protected string $secretKey;

    protected int $gatewayMode;

    protected string $baseUrl;

    public function __construct(
        string $publicKey,
        string $secretKey,
        int $gatewayMode = 0,
        string $baseUrl = 'https://marchand.maishapay.online/api/collect'
    ) {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
        $this->gatewayMode = $gatewayMode;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Process mobile money payment
     */
    public function processMobileMoneyPayment(MobileMoney $mobileMoney): Response
    {
        $payload = [
            'transactionReference' => $mobileMoney->transactionReference ?: $this->generateTransactionReference(),
            'gatewayMode' => $this->gatewayMode,
            'publicApiKey' => $this->publicKey,
            'secretApiKey' => $this->secretKey,
            'order' => [
                'amount' => $mobileMoney->amount,
                'currency' => $mobileMoney->currency,
                'customerFullName' => $mobileMoney->customerFullName,
                'customerEmailAdress' => $mobileMoney->customerEmailAddress,
            ],
            'paymentChannel' => [
                'channel' => 'MOBILEMONEY',
                'provider' => $mobileMoney->provider,
                'walletID' => $mobileMoney->walletId,
                'callbackUrl' => $mobileMoney->callbackUrl ?: config('maishapay.callback_url'),
            ],
        ];

        return $this->makeRequest('/v2/store/mobileMoney', $payload);
    }

    /**
     * Process card payment (v2)
     */
    public function processCardPayment(CardPayment $cardPayment): Response
    {
        $payload = [
            'transactionReference' => $cardPayment->transactionReference ?: $this->generateTransactionReference(),
            'gatewayMode' => $this->gatewayMode,
            'publicApiKey' => $this->publicKey,
            'secretApiKey' => $this->secretKey,
            'order' => [
                'amount' => $cardPayment->amount,
                'currency' => $cardPayment->currency,
                'customerFullName' => $cardPayment->customerFullName,
                'customerPhoneNumber' => $cardPayment->customerPhoneNumber,
                'customerEmailAdress' => $cardPayment->customerEmailAddress,
            ],
            'paymentChannel' => [
                'channel' => 'CARD',
                'provider' => $cardPayment->provider,
                'callbackUrl' => $cardPayment->callbackUrl ?: config('maishapay.callback_url'),
            ],
        ];

        return $this->makeRequest('/v2/store/card', $payload);
    }

    /**
     * Process enhanced card payment (v3)
     */
    public function processEnhancedCardPayment(CardPayment $cardPayment): Response
    {
        $payload = [
            'transactionReference' => $cardPayment->transactionReference ?: $this->generateTransactionReference(),
            'gatewayMode' => $this->gatewayMode,
            'publicApiKey' => $this->publicKey,
            'secretApiKey' => $this->secretKey,
            'order' => [
                'amount' => $cardPayment->amount,
                'currency' => $cardPayment->currency,
                'customerFirstname' => $cardPayment->customerFirstname,
                'customerLastname' => $cardPayment->customerLastname,
                'customerAddress' => $cardPayment->customerAddress,
                'customerCity' => $cardPayment->customerCity,
                'customerPhoneNumber' => $cardPayment->customerPhoneNumber,
                'customerEmailAdress' => $cardPayment->customerEmailAddress,
            ],
            'paymentChannel' => [
                'channel' => 'CARD',
                'provider' => $cardPayment->provider,
                'callbackUrl' => $cardPayment->callbackUrl ?: config('maishapay.callback_url'),
            ],
        ];

        return $this->makeRequest('/v3/store/card', $payload, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Make HTTP request to MaishaPay API
     */
    protected function makeRequest(string $endpoint, array $payload, array $headers = []): Response
    {
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->baseUrl.$endpoint, $payload);

        if ($response->failed()) {
            throw new MaishapayException(
                'MaishaPay API request failed: '.$response->body(),
                $response->status()
            );
        }

        return $response;
    }

    /**
     * Generate unique transaction reference
     */
    public function generateTransactionReference(): string
    {
        return 'MP_'.strtoupper(Str::random(10)).'_'.time();
    }

    /**
     * Verify transaction status
     */
    public function verifyTransaction(string $transactionReference): Response
    {
        // Note: You might need to implement this based on MaishaPay's verification endpoint
        return $this->makeRequest('/verify', [
            'transactionReference' => $transactionReference,
            'publicApiKey' => $this->publicKey,
            'secretApiKey' => $this->secretKey,
        ]);
    }
}
