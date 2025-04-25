<?php

namespace App\Exceptions\Custom;

use Exception;

class AuthenticationException extends Exception
{
    protected $message = 'Unauthenticated.';
    protected $code = 401;
}