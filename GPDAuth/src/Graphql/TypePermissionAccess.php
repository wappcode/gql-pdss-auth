<?php

declare(strict_types=1);

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Permission;
use GraphQL\Type\Definition\EnumType;

final class TypePermissionAccess extends EnumType
{
    public function __construct()
    {
        $config = [
            'name' => 'PermissionAccess',
            'values' => [
                Permission::ALLOW,
                Permission::DENY,

            ],
        ];

        parent::__construct($config);
    }
}
