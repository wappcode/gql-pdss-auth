<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Resource;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDCore\DataLoaders\EntityDataLoader;
use GPDCore\Graphql\ResolverFactory;

class ResolversPermission
{

    public static function getUserResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityDataLoader(User::class);
        $resolve = ResolverFactory::forEntity($entityBuffer, 'user');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
    public static function getRoleResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityDataLoader(Role::class);
        $resolve = ResolverFactory::forEntity($entityBuffer, 'role');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
    public static function getResourceResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityDataLoader(Resource::class);
        $resolve = ResolverFactory::forEntity($entityBuffer, 'resource');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
}
