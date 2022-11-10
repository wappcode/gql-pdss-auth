<?php

declare(strict_types=1);

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Permission;
use GraphQL\Type\Definition\EnumType;

final class TypePermissionValue extends EnumType
{
    public function __construct()
    {
        $config = [
            'name' => 'PermissionValue',
            'values' => [
                Permission::ALL,
                Permission::CREATE,
                Permission::DELETE,
                Permission::UPDATE,
                Permission::VIEW,
                Permission::UPLOAD,
                Permission::DOWNLOAD,
            ],
        ];

        parent::__construct($config);
    }
}
