<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

enum PermissionAccess: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
}
