<?php

declare(strict_types=1);

namespace GPDAuth\Entities;

enum PermissionValue: string
{
    case ALL = 'all';
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case UPLOAD = 'upload';
    case DOWNLOAD = 'download';
}
