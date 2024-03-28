<?php

namespace GPDAuth\Graphql;

use GraphQL\Type\Definition\Type;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\ObjectType;
use GPDAuth\Graphql\TypeFactoryAuthSessionUser;

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
                    'type' => Type::nonNull($serviceManager->get(TypeFactoryAuthSessionUser::NAME))
                ],
                'roles' => [
                    'type' => Type::listOf(Type::string())
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
