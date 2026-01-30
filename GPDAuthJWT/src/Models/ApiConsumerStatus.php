<?php

namespace GPDAuthJWT\Models;

enum ApiConsumerStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case SUSPENDED = 'suspended';
}
