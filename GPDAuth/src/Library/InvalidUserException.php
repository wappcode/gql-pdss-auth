<?php

namespace GPDAuth\Library;

use Exception;
use Throwable;

class InvalidUserException extends Exception
{


    const ERROR_ID = 'AUTH_INVALID_USER';
    const CODE = 400;
    public function __construct(string $message = 'No signed', string $errorId = InvalidUserException::ERROR_ID, int $httpcode = InvalidUserException::CODE, $category = 'businessLogic', ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $previous);
    }
}
