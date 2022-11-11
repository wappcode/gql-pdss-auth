<?php

namespace GPDAuth\Library;

use GPDCore\Library\GQLException;

class NoAuthorizedException extends GQLException
{


    const ERROR_ID = 'AUTH_NO_AUTHORIZED';
    const CODE = '401';
    public function __construct($message = 'No authenticated', $errorId = static::ERROR_ID, $httpcode = static::CODE, $category = 'businessLogic', $previous = null)
    {
        parent::__construct($message, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
