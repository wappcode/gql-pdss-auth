<?php
// src/Library/SessionAuthMiddleware.php

namespace GPDAuth\Library;


use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuthJWT\Contracts\SessionAuthenticatorInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class SessionAuthMiddleware implements MiddlewareInterface
{
    /**
     * Valida la autenticación por session en la solicitud
     * Agrega el usuario autenticado a los atributos de la solicitud con el atributo identity
     * Cuando exitUnAuthorized es true, responde con 401 si la autenticación falla (Aplica para rutas protegidas)
     * Cuando exitUnAuthorized es false, continúa la cadena de middleware si la autenticación falla (Aplica para rutas públicas o para GraphQL para validar cada query, la validación se hace en los resolvers o middleware de los resolvers, con los datos del atributo identity de request)
     * @param boolean $exitUnAuthorized
     */
    public function __construct(
        private SessionAuthenticatorInterface $sessionAuthenticator,
        private string $sessionKey = 'gpdauth_session_id',
        private string $identityKey = AuthenticatedUserInterface::class,
        private bool $exitUnAuthorized = true,

    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {


        try {
            $authenticatedUser = $this->sessionAuthenticator->authenticate($this->sessionKey);
            return $handler->handle(
                $request->withAttribute($this->identityKey, $authenticatedUser)
            );
        } catch (\Throwable $e) {
            if ($this->exitUnAuthorized) {
                return $this->unauthorized($e->getMessage());
            } else {
                return $handler->handle($request);
            }
        }
    }
    private function unauthorized(string $message): ResponseInterface
    {
        return new JsonResponse([
            'error' => 'unauthorized',
            'message' => $message,
        ], 401);
    }
}
