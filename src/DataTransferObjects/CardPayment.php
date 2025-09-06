<?php

namespace Uzhlaravel\Maishapay\DataTransferObjects;

class CardPayment
{
    public function __construct(
        public string $amount,
        public string $currency,
        public string $customerEmailAddress,
        public string $customerPhoneNumber,
        public string $provider,
        public ?string $customerFullName = null,
        public ?string $customerFirstname = null,
        public ?string $customerLastname = null,
        public ?string $customerAddress = null,
        public ?string $customerCity = null,
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
            customerEmailAddress: $data['customer_email_address'],
            customerPhoneNumber: $data['customer_phone_number'],
            provider: $data['provider'],
            customerFullName: $data['customer_full_name'] ?? null,
            customerFirstname: $data['customer_firstname'] ?? null,
            customerLastname: $data['customer_lastname'] ?? null,
            customerAddress: $data['customer_address'] ?? null,
            customerCity: $data['customer_city'] ?? null,
            transactionReference: $data['transaction_reference'] ?? null,
            callbackUrl: $data['callback_url'] ?? null
        );
    }

    public static function createForV2(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'],
            customerEmailAddress: $data['customer_email_address'],
            customerPhoneNumber: $data['customer_phone_number'],
            provider: $data['provider'],
            customerFullName: $data['customer_full_name'],
            transactionReference: $data['transaction_reference'] ?? null,
            callbackUrl: $data['callback_url'] ?? null
        );
    }

    public static function createForV3(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'],
            customerEmailAddress: $data['customer_email_address'],
            customerPhoneNumber: $data['customer_phone_number'],
            provider: $data['provider'],
            customerFirstname: $data['customer_firstname'],
            customerLastname: $data['customer_lastname'],
            customerAddress: $data['customer_address'] ?? null,
            customerCity: $data['customer_city'] ?? null,
            transactionReference: $data['transaction_reference'] ?? null,
            callbackUrl: $data['callback_url'] ?? null
        );
    }

    private function validateProvider(string $provider): void
    {
        $validProviders = config('maishapay.card_providers', ['VISA', 'MASTERCARD', 'AMERICAN EXPRESS']);

        if (! in_array($provider, $validProviders)) {
            throw new \InvalidArgumentException("Invalid card provider: {$provider}");
        }
    }

    private function validateCurrency(string $currency): void
    {
        $validCurrencies = config('maishapay.currencies', ['CDF', 'USD', 'EUR', 'XAF', 'XOF']);

        if (! in_array($currency, $validCurrencies)) {
            throw new \InvalidArgumentException("Invalid currency: {$currency}");
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer_email_address' => $this->customerEmailAddress,
            'customer_phone_number' => $this->customerPhoneNumber,
            'provider' => $this->provider,
            'customer_full_name' => $this->customerFullName,
            'customer_firstname' => $this->customerFirstname,
            'customer_lastname' => $this->customerLastname,
            'customer_address' => $this->customerAddress,
            'customer_city' => $this->customerCity,
            'transaction_reference' => $this->transactionReference,
            'callback_url' => $this->callbackUrl,
        ], fn ($value) => $value !== null);
    }
}
