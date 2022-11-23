<?php

namespace GPDAuth\Library;

use GPDCore\Library\GQLException;

class InvalidUserException extends GQLException
{


    const ERROR_ID = 'AUTH_INVALID_USER';
    const CODE = '400';
    public function __construct($message = 'No signed', $errorId = InvalidUserException::ERROR_ID, $httpcode = InvalidUserException::CODE, $category = 'businessLogic', $previous = null)
    {
        parent::__construct($message, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
