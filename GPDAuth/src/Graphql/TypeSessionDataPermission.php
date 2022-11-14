<?php

namespace GPDAuth\Graphql;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TypeSessionDataPermission extends ObjectType
{

    const NAME = 'SessionDataPermission';
    public function __construct()
    {
        $config = [
            'name' => static::NAME,
            'description' => '',
            'fields' => [
                'resource' => Type::nonNull(Type::string()),
                'access' => Type::nonNull(Type::string()),
                'value' => Type::nonNull(Type::string()),
                'scope' => Type::nonNull(Type::string())
            ]
        ];
        parent::__construct($config);
    }
}
