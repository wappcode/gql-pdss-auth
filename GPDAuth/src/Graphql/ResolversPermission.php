<?php

namespace GPDAuth\Graphql;

use GPDAuth\Entities\Resource;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use GPDCore\Library\EntityBuffer;
use GPDCore\Library\ResolverFactory;

class ResolversPermission
{

    public static function getUserResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityBuffer(User::class);
        $resolve = ResolverFactory::createEntityResolver($entityBuffer, 'user');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
    public static function getRoleResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityBuffer(Role::class);
        $resolve = ResolverFactory::createEntityResolver($entityBuffer, 'role');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
    public static function getResourceResolve(?callable $proxy): callable
    {
        $entityBuffer = new EntityBuffer(Resource::class);
        $resolve = ResolverFactory::createEntityResolver($entityBuffer, 'resource');
        return is_callable($proxy) ? $proxy($resolve) : $resolve;
    }
}
