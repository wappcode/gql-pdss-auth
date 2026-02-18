<?php
// src/Middleware/JwtAuthMiddleware.php

namespace App\Middleware;


use GPDAuth\Models\AuthenticatedUserInterface;
use GPDAuth\Models\AuthServiceInterface;
use GPDAuthJWT\Models\ApiConsumerRepositoryInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Valida la autenticación por session en la solicitud
     * Agrega el usuario autenticado a los atributos de la solicitud con el atributo identity
     * Cuando exitUnauthenticated es true, responde con 401 si la autenticación falla (Aplica para rutas protegidas)
     * Cuando exitUnauthenticated es false, continúa la cadena de middleware si la autenticación falla (Aplica para rutas públicas o para GraphQL para validar cada query, la validación se hace en los resolvers o middleware de los resolvers, con los datos del atributo identity de request)
     *
     * @param AuthServiceInterface $authService
     * @param ApiConsumerRepositoryInterface $apiConsumerRepository
     * @param boolean $exitUnauthenticated
     */
    public function __construct(
        private AuthServiceInterface $authService,
        private string $identityKey = AuthenticatedUserInterface::class,
        private bool $exitUnauthenticated = true,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $authenticatedUser = $this->authService->getAuthenticatedUser();
        if (!($authenticatedUser instanceof AuthenticatedUserInterface)) {
            if ($this->exitUnauthenticated) {
                return $this->unauthorized('Unauthenticated');
            } else {
                return $handler->handle($request);
            }
        }
        $request = $request->withAttribute($this->identityKey, $authenticatedUser);
        return $handler->handle(
            $request
        );
    }
    private function unauthorized(string $message): ResponseInterface
    {
        return new JsonResponse([
            'error' => 'unauthorized',
            'message' => $message,
        ], 401);
    }
}
