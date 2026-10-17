<?php

namespace GPDAuth\Library;

use Exception;
use Throwable;

class InvalidUserException extends Exception
{



    const CODE = 400;
    public function __construct(string $message = 'No signed', int $errorId = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $previous);
    }
}
