<?php

namespace App\Exception;

class InvalidCalculatorDataException extends \DomainException
{
    private(set) array $context;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
}
