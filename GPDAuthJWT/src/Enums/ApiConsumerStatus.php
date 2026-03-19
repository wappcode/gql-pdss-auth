<?php

namespace GPDAuthJWT\Enums;

enum ApiConsumerStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case SUSPENDED = 'suspended';
}
