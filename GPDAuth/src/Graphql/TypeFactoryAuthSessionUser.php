<?php

namespace GPDAuth\Graphql;

use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TypeFactoryAuthSessionUser
{

    const NAME = 'AuthSessionUser';

    public static function create(IContextService $contxt, string $name = TypeFactoryAuthSessionUser::NAME, $description = '')
    {
        $types = $contxt->getTypes();
        $serviceManager = $contxt->getServiceManager();
        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'fullName' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'firstName' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'lastName' => [
                    'type' => Type::string(),
                ],
                'username' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'email' => [
                    'type' => Type::string(),
                ],
                'picture' => [
                    'type' => Type::string(),
                ],
            ]

        ]);
    }
}
