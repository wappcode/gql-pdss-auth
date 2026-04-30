<?php
// src/Middleware/JwtAuthMiddleware.php

namespace GPDAuthJWT\Middleware;


use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuthJWT\Contracts\JWTAuthenticatorInterface;
use GPDAuthJWT\Library\JwtUtilities;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * Valida la autenticación JWT en la solicitud
     * Agrega el usuario autenticado a los atributos de la solicitud con el atributo identity
     * Cuando exitUnAuthorized es true, responde con 401 si la autenticación falla (Aplica para rutas protegidas)
     * Cuando exitUnAuthorized es false, continúa la cadena de middleware si la autenticación falla (Aplica para rutas públicas o para GraphQL para validar cada query, la validación se hace en los resolvers o middleware de los resolvers, con los datos del atributo identity de request)
     *
     * @param JWTAuthenticatorInterface $jwtAuthenticator
     * @param boolean $exitUnAuthorized
     */
    public function __construct(
        private JWTAuthenticatorInterface $jwtAuthenticator,
        private string $identityKey = AuthenticatedUserInterface::class,
        private bool $exitUnAuthorized = true
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $jwt = JwtUtilities::extractJWTFromRequest($request);
        if ($jwt === null) {
            if ($this->exitUnAuthorized) {
                return $this->unauthorized('Missing token');
            } else {
                return $handler->handle($request);
            }
        }
        try {
            $authenticationResult = $this->jwtAuthenticator->authenticate($jwt);

            $request = $request->withAttribute($this->identityKey, $authenticationResult->getAuthenticatedUser());
            $request = $request->withAttribute('jwt_payload', $authenticationResult->getPayload());
            return $handler->handle(
                $request
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
