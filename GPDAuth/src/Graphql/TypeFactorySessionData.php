<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\User;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TypeFactorySessionData
{

    const NAME = 'SessionData';

    public static function create(IContextService $contxt, string $name = TypeFactorySessionData::NAME, $description = '')
    {
        $types = $contxt->getTypes();
        $serviceManager = $contxt->getServiceManager();
        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'user' => [
                    'type' => Type::nonNull($types->getOutput(User::class))
                ],
                'permissions' => [
                    'type' => Type::nonNull(Type::listOf($serviceManager->get(TypeSessionDataPermission::class))),
                ],
                'jwt' => [
                    'type' => Type::string()
                ]
            ]

        ]);
    }
}
