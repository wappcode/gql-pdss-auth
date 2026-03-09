<?php


namespace GPDAuthJWT\Contracts;


interface ApiConsumerRepositoryInterface
{
    public function isTrustedConsumer(string $consumerId): bool;
    public function getValidPermissionsForConsumer(string $consumerId, array $permissions): array;
    public function getConsumerName(string $consumerId): string;
    public function getConsumerIdFromJwtPayload(array $payload): ?string;
    public function isM2mToken(array $payload): bool;
}
