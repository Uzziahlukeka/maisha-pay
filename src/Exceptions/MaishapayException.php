<?php

namespace Uzhlaravel\Maishapay\Exceptions;

use Exception;

class MaishapayException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid MaishaPay API credentials provided.');
    }

    public static function invalidPaymentData(string $field): self
    {
        return new self("Invalid payment data: {$field}");
    }

    public static function transactionFailed(string $reason): self
    {
        return new self("Transaction failed: {$reason}");
    }

    public static function networkError(): self
    {
        return new self('Network error occurred while processing payment.');
    }
}
