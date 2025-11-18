<?php
declare(strict_types=1);

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct("Validation failed");
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
