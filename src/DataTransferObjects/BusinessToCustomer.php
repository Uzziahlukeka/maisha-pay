<?php

declare(strict_types=1);

namespace Uzhlaravel\Maishapay\DataTransferObjects;

use InvalidArgumentException;

final class BusinessToCustomer
{
    public function __construct(
        public string $amount,
        public string $currency,
        public string $customerFullName,
        public string $customerEmailAddress,
        public string $provider,
        public string $walletId,
        public string $motif,
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
            customerFullName: $data['customer_full_name'],
            customerEmailAddress: $data['customer_email_address'],
            provider: $data['provider'],
            walletId: $data['wallet_id'],
            motif: $data['motif'],
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
