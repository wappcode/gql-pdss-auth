<?php

namespace GPDAuthJWT;

use GPDAuthJWT\Authentication\JWTAuthenticator;
use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuthJWT\Middleware\JwtAuthMiddleware;
use GPDAuthJWT\Services\ApiConsumerRepository;
use GPDAuthJWT\Services\JWTTrustIssuerRepository;
use GPDAuthJWT\Services\JWTUserRepository;
use GPDCore\Core\AbstractModule;

class GPDAuthJWTModule extends AbstractModule
{


    public function __construct(private bool $exitUnAuthorized = false, private int $maxTokenLifetime = 3600) {}
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
        $apiConsumerRepository = new ApiConsumerRepository($entityManager);
        $userRepository = new JWTUserRepository($apiConsumerRepository);
        $jwtAuthenticator = new JWTAuthenticator(
            new JWTTrustIssuerRepository($entityManager),
            $apiConsumerRepository,
            $userRepository,
            $this->maxTokenLifetime
        );
        return [
            new JwtAuthMiddleware(
                $jwtAuthenticator,
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
