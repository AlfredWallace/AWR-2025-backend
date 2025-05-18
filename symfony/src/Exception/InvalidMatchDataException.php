<?php

namespace App\Exception;

class InvalidMatchDataException extends AppDomainException
{
    public function __construct(string $message = "Invalid match data", int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}