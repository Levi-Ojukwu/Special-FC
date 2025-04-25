<?php

namespace App\Exceptions\Custom;

use Exception;

class AuthorizationException extends Exception
{
    protected $message = 'Unauthorized.';
    protected $code = 403;
}