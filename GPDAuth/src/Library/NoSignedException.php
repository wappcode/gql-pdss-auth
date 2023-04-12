<?php

namespace GPDAuth\Library;

use Throwable;
use GPDCore\Library\GQLException;

class NoSignedException extends GQLException
{


    const ERROR_ID = 'AUTH_NO_SIGNED';
    const CODE = 401;

    public function __construct(string $message = 'No signed', string $errorId = NoSignedException::ERROR_ID, int $httpcode = NoSignedException::CODE, string $category = 'businessLogic', ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
