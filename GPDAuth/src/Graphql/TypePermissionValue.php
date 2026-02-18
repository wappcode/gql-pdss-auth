<?php

declare(strict_types=1);

namespace GPDAuth\Graphql;

use GPDAuth\Entities\PermissionValue;
use GraphQL\Type\Definition\EnumType;

final class TypePermissionValue extends EnumType
{
    public function __construct()
    {
        $config = [
            'name' => 'PermissionValue',
            'values' => [
                PermissionValue::ALL,
                PermissionValue::CREATE,
                PermissionValue::DELETE,
                PermissionValue::UPDATE,
                PermissionValue::VIEW,
                PermissionValue::UPLOAD,
                PermissionValue::DOWNLOAD,
            ],
        ];

        parent::__construct($config);
    }
}
