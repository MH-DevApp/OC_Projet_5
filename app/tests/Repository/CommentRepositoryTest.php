<?php

/**
 * CommentRepositoryTest file
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
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\AbstractRepository;
use App\Repository\CommentRepository;
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
 * Test Comment Repository cases
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
#[CoversClass(CommentRepository::class)]
#[CoversClass(Comment::class)]
class CommentRepositoryTest extends TestCase
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
     * should be to create a Comment and use repository
     * for fetch one with instance of Comment.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to create a Comment and use repository for fetch one with instance of Comment")]
    public function itCreateCommentAndFetchWithObject(): void
    {
        $idComment = UuidV4::generate();

        /**
         * @var User $user
         */
        $user = (new UserRepository())
            ->findByOne(["lastname" => "User1"],
                classObject: User::class
            );


        /**
         * @var Post $post
         */
        $post = (new PostRepository())
            ->findByOne(["userId" => $user->getId() ?? ""],
                classObject: Post::class
            );

        $statement = $this->pdo->prepare(
            "INSERT INTO `comment` (`id`, `userId`, `blogPostId`, `content`, `createdAt`, `updatedAt`)".
            " VALUES (:id, :userId, :blogPostId, :content, :createdAt, :updatedAt)"
        );
        $statement->bindValue(":id", $idComment);
        $statement->bindValue(":userId", $user->getId());
        $statement->bindValue(":blogPostId", $post->getId());
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

        $comment = (new CommentRepository())->findByOne(["id" => $idComment], classObject: Comment::class);

        $this->assertInstanceOf(Comment::class, $comment);

    }


}
