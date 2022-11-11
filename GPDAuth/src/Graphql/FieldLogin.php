<?php

namespace GPDAuth\Graphql;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;
use GPDAuth\Entities\User;
use GPDAuth\Services\AuthService;
use GPDCore\Library\GeneralDoctrineUtilities;
use GPDCore\Library\GQLException;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\Type;

class FieldLogin
{

    /**
     * @var IContextService
     */
    protected $context;

    public static function get(IContextService $context, ?callable $proxy)
    {
        $resolve = FieldLogin::createResolve();
        $proxyResolve = is_callable($proxy) ? $proxy($resolve) : $resolve;
        $types = $context->getTypes();
        return [
            "type" => $types->getOutput(User::class),
            "args" => [
                [
                    "name" => "username",
                    "type" => Type::nonNull(Type::string())
                ],
                [
                    "name" => "password",
                    "type" => Type::nonNull(Type::string())
                ],
            ],
            "resolve" => $proxyResolve
        ];
    }
    private static function createResolve(): callable
    {
        return function ($root, array $args, IContextService $context, $info) {
            $entityManager = $context->getEntityManager();
            $username = $args["username"] ?? '';
            $password = $args["password"] ?? '';
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            try {
                $auth->login($username, $password);
                $user = static::getUser($username, $entityManager);
                return $user;
            } catch (Exception $e) {
                throw new GQLException($e->getMessage(), "AUTH_LOGIN_400", 400);
            }
        };
    }

    private static function getUser(string $username, EntityManager $entityManager): array
    {
        $qb = $entityManager->createQueryBuilder()->from(User::class, 'user')->select('user');
        $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb, User::RELATIONS_MANY_TO_ONE);
        $qb->andWhere('user.username = :username')
            ->setParameter(':username', $username);
        $user = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        return $user;
    }

    private function __construct(IContextService $context)
    {
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }
}
