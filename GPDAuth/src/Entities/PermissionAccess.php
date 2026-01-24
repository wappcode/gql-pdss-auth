<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

enum PermissionAccess: string
{
    case ALLOW = 'ALLOW';
    case DENY = 'DENY';
}
