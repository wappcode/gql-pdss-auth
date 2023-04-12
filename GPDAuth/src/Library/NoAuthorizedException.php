<?php

namespace GPDAuth\Library;

use Throwable;
use GPDCore\Library\GQLException;

class NoAuthorizedException extends GQLException
{


    const ERROR_ID = 'AUTH_NO_AUTHORIZED';
    const CODE = 403;

    public function __construct(string $message = 'No authorized', string $errorId = NoAuthorizedException::ERROR_ID, int $httpcode = NoAuthorizedException::CODE, string $category = 'businessLogic', ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
