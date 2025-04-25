<?php

namespace App\Exceptions\Custom;

use Exception;

class ValidationException extends Exception
{
    protected $errors;
    protected $code = 422;

    public function __construct($errors, $message = 'Validation failed.')
    {
        $this->errors = $errors;
        parent::__construct($message, $this->code);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}