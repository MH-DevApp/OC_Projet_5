<?php

/**
 * SessionRepositoryTest file
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */

declare(strict_types=1);

namespace tests\Repository;


use App\Database\Database;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\AbstractRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use Exception;
use PDO;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Session Repository cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(AbstractRepository::class)]
#[CoversClass(SessionRepository::class)]
#[CoversClass(Session::class)]
class SessionRepositoryTest extends TestCase
{
    private PDO $pdo;


    /**
     * Initialise environment for test
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Before]
    public function init(): void
    {
        $_ENV["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        Container::loadServices();
        $this->pdo = (new Database())->connect();
    }


    /**
     * Test should be to create a Session and use repository
     * for fetch with instance of Session.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a Session and use repository for fetch with instance of Session")]
    public function itCreateSessionAndFetchWithObject(): void
    {
        $idSession = UuidV4::generate();

        /**
         * @var User $user
         */
        $user = (new UserRepository())->findByOne(["lastname" => "User1"], classObject: User::class);

        $statement = $this->pdo->prepare(
            "INSERT INTO `session` (`id`, `userId`)".
            " VALUES (:id, :userId)"
        );

        $statement->bindValue(":id", $idSession);
        $statement->bindValue(":userId", $user->getId());
        $statement->execute();

        $session = (new SessionRepository())->findByOne(["id" => $idSession], classObject: Session::class);

        $this->assertInstanceOf(Session::class, $session);

    }


}
