<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDCore\Graphql\ResolverFactory;

class ResolversRole
{

    public static function getUserResolve(?callable $proxy): callable
    {
        $resolve = ResolverFactory::forCollection(Role::class, 'users', null, User::class);
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
}
