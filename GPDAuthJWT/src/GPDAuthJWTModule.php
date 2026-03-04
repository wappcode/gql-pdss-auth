<?php

namespace GPDAuthJWT;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuthJWT\Middleware\JwtAuthMiddleware;
use GPDAuthJWT\Services\ApiConsumerRepository;
use GPDAuthJWT\Services\JWTTrustIssuerRepository;
use GPDCore\Core\AbstractModule;

class GPDAuthJWTModule extends AbstractModule
{


    public function __construct(private bool $exitUnAuthorized = false) {}
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getSchema(): string
    {
        return '';
    }
    public function getServices(): array
    {
        return [];
    }
    public function getResolvers(): array
    {
        return [];
    }
    public function getMiddlewares(): array
    {
        $context = $this->application->getContext();
        $entityManager = $context->getEntityManager();
        return [
            new JwtAuthMiddleware(
                new JWTTrustIssuerRepository($context),
                new ApiConsumerRepository($entityManager),
                identityKey: AuthenticatedUserInterface::class,
                exitUnAuthorized: $this->exitUnAuthorized
            )
        ];
    }
    public function getRoutes(): array
    {
        return [];
    }
    public function getTypes(): array
    {
        return [];
    }
}
