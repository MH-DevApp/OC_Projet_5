<?php

/**
 * UserRepositoryTest file
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
use App\Entity\User;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\AbstractRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use DateTime;
use Exception;
use PDO;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test User Repository cases
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
#[CoversClass(UserRepository::class)]
#[CoversClass(User::class)]
class UserRepositoryTest extends TestCase
{
    static int $nbUser = 0;
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
     * Test should be to create a User and use repository for
     * find one in the database.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a User and use repository for find one in the database")]
    public function itCreateUserAndFindOneItWithDB(): void
    {
        $id = UuidV4::generate();
        $this->createUser($id);

        $userRepo = new UserRepository();

        /**
         * @var array<string, string> $user
         */
        $user = $userRepo->findByOne(["id" => $id, "lastname" => "User1"]);

        $this->assertEquals(10, count($user));
        $this->assertTrue(is_array($user));

        $user = $userRepo->findByOne(["id" => $id], classObject: User::class);
        $this->assertInstanceOf(User::class, $user);

    }


    /**
     * Test should be to create a some Users and use repository for
     * find some in the database.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a some Users and use repository for find some in the database")]
    public function itCreateUserAndFindByItWithDB(): void
    {
        for ($i=0; $i<5; $i++) {
            $id = UuidV4::generate();
            $this->createUser($id);
        }

        $userRepo = new UserRepository();

        $users = $userRepo->findBy(["role" => "ROLE_USER"]);

        $this->assertEquals(static::$nbUser, count($users));
        $this->assertIsArray($users);

        $users = $userRepo->findBy(["role" => "ROLE_USER"], [PDO::FETCH_CLASS, User::class]);

        foreach ($users as $user)
        {
            $this->assertInstanceOf(User::class, $user);
        }

    }


    /**
     * Test should be to create a some Users and use repository for
     * find all in the database.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a some Users and use repository for find all in the database")]
    public function itCreateUserAndFindAllItWithDB(): void
    {
        for ($i=0; $i<5; $i++) {
            $id = UuidV4::generate();
            $this->createUser($id);
        }

        $userRepo = new UserRepository();
        $users = $userRepo->findAll([PDO::FETCH_CLASS, User::class]);

        $this->assertEquals(static::$nbUser, count($users));
        foreach ($users as $user)
        {
            $this->assertInstanceOf(User::class, $user);
        }

    }


    /**
     * Create User in the database
     *
     * @param string $id The ID to user
     *
     * @return void
     * @throws Exception
     */
    private function createUser(string $id): void
    {
        static::$nbUser++;
        $statement = $this->pdo->prepare(
            "INSERT INTO ".User::TABLE_NAME.
            " (`id`, `lastname`, `firstname`, `pseudo`, `password`, `email`, `createdAt`, `role`, `expiredTokenAt`) ".
            "VALUES".
            " (:id, :lastname, :firstname, :pseudo, :password, :email, :createdAt, :role, :expiredTokenAt)"
        );

        $statement->bindValue(":id", $id);
        $statement->bindValue(":lastname", "User".static::$nbUser);
        $statement->bindValue(":firstname", "User".static::$nbUser);
        $statement->bindValue(":pseudo", "user".static::$nbUser);
        $statement->bindValue(":password", password_hash("test", PASSWORD_ARGON2ID));
        $statement->bindValue(":email", "test".static::$nbUser."@test.com");
        $statement->bindValue(
            ":createdAt",
            (new DateTime(
                "now",
                new \DateTimeZone("Europe/Paris")
            ))->format(DATE_ATOM)
        );
        $statement->bindValue(
            ":expiredTokenAt",
            (new DateTime(
                "now",
                new \DateTimeZone("Europe/Paris")
            ))->format(DATE_ATOM)
        );
        $statement->bindValue(":role", "ROLE_USER");
        $statement->execute();
    }


}
