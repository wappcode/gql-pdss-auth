<?php

declare(strict_types=1);

namespace GPDAuth\Enums;

enum PermissionAccess: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
}
