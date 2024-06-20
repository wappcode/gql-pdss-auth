<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDCore\Library\ResolverFactory;

class ResolversRole
{

    public static function getUserResolve(?callable $proxy): callable
    {
        $resolve = ResolverFactory::createCollectionResolver(Role::class, 'users', null, User::class);
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
}
