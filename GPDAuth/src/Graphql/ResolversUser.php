<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDCore\Library\ResolverFactory;

class ResolversUser
{


    public static function getFullNameResolve(): callable
    {

        return function ($root, $args, $context, $info) {
            $firstName = $root["firstName"] ?? '';
            $lastName = $root["lastName"] ?? '';
            $fullName = $firstName . " " . $lastName;
            return trim($fullName);
        };
    }

    public static function getRolesResolve(?callable $proxy): callable
    {

        $resolve = ResolverFactory::createCollectionResolver(User::class, 'roles', null, Role::class);
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
}
