<?php
// src/Middleware/JwtAuthMiddleware.php

namespace App\Middleware;


use GPDAuth\Library\NoSignedException;
use GPDAuth\Contracts\UserRepositoryInterface;
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
        private UserRepositoryInterface $userRepository,
        private string $sessionKey = 'gpdauth_session_id',
        private string $identityKey = 'identity',
        private bool $exitUnAuthorized = true,

    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {


        try {
            $userId = $_SESSION[$this->sessionKey]["identifier"] ?? null;
            if ($userId === null) {
                throw new NoSignedException();
            }
            $authenticatedUser = $this->userRepository->findById($userId);
            if ($authenticatedUser === null) {
                throw new NoSignedException();
            }
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
