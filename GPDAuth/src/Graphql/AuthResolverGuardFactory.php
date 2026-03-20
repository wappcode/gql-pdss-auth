<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\NoAuthorizedException;
use GPDAuth\Library\NoSignedException;
use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ResolverMiddlewareInterface;
use GPDCore\Graphql\ResolverWrapperMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class AuthResolverGuardFactory
{
    public static function requireAuthenticated(?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }
    public static function requireRole(string $role, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $role, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasRole($role)) {
                throw new NoAuthorizedException("User does not have the required role: " . $role, "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }

    public static function requireAnyRole(array $roles, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $roles, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasAnyRole($roles)) {
                throw new NoAuthorizedException("User does not have any of the required roles: " . implode(", ", $roles), "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }

    public static function requireAllRoles(array $roles, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $roles, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasAllRoles($roles)) {
                throw new NoAuthorizedException("User does not have all the required roles: " . implode(", ", $roles), "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }

    public static function requirePermission(string $resource, string $permission, ?string $scope = null, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $resource, $permission, $scope, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasPermission($resource, $permission, $scope)) {
                throw new NoAuthorizedException("User does not have the required permission: " . $resource . ":" . $permission . ($scope ? ":" . $scope : ""), "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }
    public static function requireAnyPermission(array $resources, array $permissions, ?array $scopes = null, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $resources, $permissions, $scopes, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasAnyPermission($resources, $permissions, $scopes)) {
                throw new NoAuthorizedException("User does not have any of the required permissions: " . implode(", ", array_map(fn($r) => $r . ":" . implode("|", $permissions) . ($scopes ? ":" . implode("|", $scopes) : ""), $resources)), "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }

    public static function requireAllPermissions(array $resources, array $permissions, ?array $scopes = null, ?string $identifier = null): ResolverMiddlewareInterface
    {
        $proxy = fn($resolver) => function ($root, $args, $context, $info) use ($resolver, $resources, $permissions, $scopes, $identifier) {
            $user = static::getAuthenticatedUser($context, $identifier);
            if (!$user) {
                throw new NoSignedException();
            }
            if (!$user->hasAllPermissions($resources, $permissions, $scopes)) {
                throw new NoAuthorizedException("User does not have all the required permissions: " . implode(", ", array_map(fn($r) => $r . ":" . implode("|", $permissions) . ($scopes ? ":" . implode("|", $scopes) : ""), $resources)), "FORBIDDEN", 403);
            }
            return $resolver($root, $args, $context, $info);
        };
        return new ResolverWrapperMiddleware($proxy);
    }

    private static function getAuthenticatedUser(AppContextInterface $context, ?string $identifier): ?AuthenticatedUserInterface
    {

        if (!$identifier) {
            $identifier = AuthenticatedUserInterface::class;
        }
        $request = $context->getContextAttribute(ServerRequestInterface::class);
        /** @var AuthenticatedUserInterface|null */
        $user = null;
        $user = $request?->getAttribute($identifier);
        if ($user instanceof AuthenticatedUserInterface) {
            return $user;
        }
        /** @var AuthServiceInterface|null */
        $authService = $context->getServiceManager()?->get(AuthServiceInterface::class);
        $user = $authService?->getAuthenticatedUser();
        return $user;
    }
}
