<?php

namespace GPDAuth\Library;

use GPDCore\Exceptions\GQLException;
use Throwable;

class InvalidUserException extends GQLException
{


    const ERROR_ID = 'AUTH_INVALID_USER';
    const CODE = 400;
    public function __construct(string $message = 'No signed', string $errorId = InvalidUserException::ERROR_ID, int $httpcode = InvalidUserException::CODE, $category = 'businessLogic', ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, $category, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
