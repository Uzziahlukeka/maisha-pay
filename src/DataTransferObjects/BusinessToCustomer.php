<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\DataTransferObjects;

use InvalidArgumentException;

final class BusinessToCustomer
{
    public function __construct(
        public float $amount,
        public string $currency,
        public string $provider,
        public string $walletId,
        public ?string $customerFullName = null,
        public ?string $customerEmailAddress = null,
        public ?string $motif = null,
        public ?string $transactionReference = null,
        public ?string $callbackUrl = null
    ) {
        $this->validateProvider($provider);
        $this->validateCurrency($currency);
    }

    public static function create(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'],
            provider: $data['provider'],
            walletId: $data['wallet_id'],
            customerFullName: $data['customer_full_name'] ?? null,
            customerEmailAddress: $data['customer_email_address'] ?? null,
            motif: $data['motif'] ?? null,
            transactionReference: $data['transaction_reference'] ?? null,
            callbackUrl: $data['callback_url'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer_full_name' => $this->customerFullName,
            'customer_email_address' => $this->customerEmailAddress,
            'provider' => $this->provider,
            'wallet_id' => $this->walletId,
            'motif' => $this->motif,
            'transaction_reference' => $this->transactionReference,
            'callback_url' => $this->callbackUrl,
        ];
    }

    private function validateProvider(string $provider): void
    {
        $validProviders = config('maishapay.mobile_money_providers', ['AIRTEL', 'ORANGE', 'MTN', 'VODACOM']);

        if (! in_array($provider, $validProviders)) {
            throw new InvalidArgumentException("Invalid mobile money provider: $provider");
        }
    }

    private function validateCurrency(string $currency): void
    {
        $validCurrencies = config('maishapay.currencies', ['CDF', 'USD', 'EUR', 'XAF', 'XOF']);

        if (! in_array($currency, $validCurrencies)) {
            throw new InvalidArgumentException("Invalid currency: $currency");
        }
    }
}
