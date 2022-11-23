<?php

namespace GPDAuth\Library;

use GPDCore\Library\GQLException;

class NoSignedException extends GQLException
{


    const ERROR_ID = 'AUTH_NO_SIGNED';
    const CODE = '401';
    
    public function __construct($message = 'No signed', $errorId = NoSignedException::ERROR_ID, $httpcode = NoSignedException::CODE, $category = 'businessLogic', $previous = null)
    {
        parent::__construct($message, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
}
