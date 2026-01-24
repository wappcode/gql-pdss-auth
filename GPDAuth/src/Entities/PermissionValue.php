<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

enum PermissionValue: string
{
    case ALL = 'ALL';
    case VIEW = 'VIEW';
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case UPLOAD = 'UPLOAD';
    case DOWNLOAD = 'DOWNLOAD';
}
