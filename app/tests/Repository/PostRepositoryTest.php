<?php

/**
 * PostRepositoryTest file
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
use App\Entity\Post;
use App\Entity\User;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\AbstractRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Post Repository cases
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
#[CoversClass(PostRepository::class)]
#[CoversClass(Post::class)]
class PostRepositoryTest extends TestCase
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
     * should be to create a Post and use repository
     * for fetch one with instance of Post.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a Post and use repository for fetch one with instance of Post")]
    public function itCreatePostAndFetchWithObject(): void
    {
        $idPost = UuidV4::generate();

        /**
         * @var User $user
         */
        $user = (new UserRepository())
            ->findByOne(["lastname" => "User1"],
                classObject: User::class
            );

        $statement = $this->pdo->prepare(
            "INSERT INTO `post` (`id`, `userId`, `title`, `chapo`, `content`, `createdAt`, `updatedAt`)".
            " VALUES (:id, :userId, :title, :chapo, :content, :createdAt, :updatedAt)"
        );
        $statement->bindValue(":id", $idPost);
        $statement->bindValue(":userId", $user->getId());
        $statement->bindValue(":title", "Test");
        $statement->bindValue(":chapo", "Test");
        $statement->bindValue(":content", "Test");
        $statement->bindValue(
            ":createdAt",
            (new DateTime(
                "now",
                new DateTimeZone("Europe/Paris")
            ))->format(DATE_ATOM)
        );
        $statement->bindValue(
            ":updatedAt",
            (new DateTime(
                "now",
                new DateTimeZone("Europe/Paris")
            ))->format(DATE_ATOM)
        );
        $statement->execute();

        $post = (new PostRepository())->findByOne(["id" => $idPost], classObject: Post::class);

        $this->assertInstanceOf(Post::class, $post);

    }


}
