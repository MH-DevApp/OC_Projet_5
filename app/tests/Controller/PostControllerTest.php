<?php

/**
 * PostControllerTest file
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

namespace tests\Database;


use App\Controller\AbstractController;
use App\Controller\PostController;
use App\Entity\Post;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\PostRepository;
use App\Service\Container\Container;
use Exception;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Test PostController cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(AbstractController::class)]
#[CoversClass(PostController::class)]
#[CoversClass(PostRepository::class)]
class PostControllerTest extends TestCase
{

    private PostController $controller;
    private Manager $manager;
    private User $user;

    /**
     * @var array<int, Post>
     */
    private array $posts = [];

    /**
     * Init of environment test
     *
     * @return void
     *
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws Exception
     */
    #[Before]
    public function init(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_ENV["TEST_PATH"] = "_test";

        (new DotEnv())->load();
        Container::loadServices();

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");
        $this->manager = $manager;

        $this->user = (new User())
            ->setLastname("Test")
            ->setFirstname("Test")
            ->setPseudo("Test")
            ->setPassword(password_hash("password", PASSWORD_ARGON2ID))
            ->setEmail("test@test.com");
        $this->manager->flush($this->user);

        $this->controller = new PostController();
    }


    /**
     * End test
     *
     * @return void
     */
    #[After]
    public function end(): void
    {
        if (count($this->posts) > 0) {
            for ($i=0; $i<count($this->posts); $i++) {
                $this->manager->delete($this->posts[$i]);
            }
        }
        $this->manager->delete($this->user);
    }


    /**
     * Test should be to render posts list empty of post controller.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Test]
    #[TestDox("should be to render posts list empty of post controller")]
    public function itRenderPostsListEmptyOfPostController(): void
    {
        $response = $this->controller->showPosts();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div>Aucun post n'a été publié</div>",
            $content
        );

    }


    /**
     * Test should be to render posts list of post controller.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render posts list of post controller")]
    public function itRenderPostsListOfPostController(): void
    {
        $this->createPosts();

        $response = $this->controller->showPosts();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        preg_match_all("#(<div>){1}([\w\d\s]+)(<\/div>){1}#", $content, $matches);

        $this->assertCount(5, $matches[0]);
        $this->assertEquals([
            "<div>Titre 5</div>",
            "<div>Titre 2</div>",
            "<div>Titre 3</div>",
            "<div>Titre 1</div>",
            "<div>Titre 4</div>",
        ], $matches[0]);

    }


    /**
     * Test should be to render post details by Id of post controller.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render post details by Id of post controller")]
    public function itRenderPostDetailsByIdOfPostController(): void
    {
        $this->createPosts();

        $response = $this->controller->showPost($this->posts[0]->getId() ?: "");

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertMatchesRegularExpression("#(<div>){1}([\w\d\s]+)(<\/div>){1}#", $content);
        $this->assertStringContainsString("<div>Titre 4</div>", $content);

        $response = $this->controller->showPost("NOK");
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Create posts
     *
     * @return void
     * @throws Exception
     */
    private function createPosts(): void
    {
        $data = [
            [
                "date" => new \DateTime("2023-01-05"),
                "title" => "Titre 4"
            ],
            [
                "date" => new \DateTime("2023-01-06"),
                "title" => "Titre 1"
            ],
            [
                "date" => new \DateTime("2023-01-07"),
                "title" => "Titre 3"
            ],
            [
                "date" => new \DateTime("2023-01-08"),
                "title" => "Titre 2"
            ],
            [
                "date" => new \DateTime("2023-01-09"),
                "title" => "Titre 5"
            ],
        ];

        /**
         * @var string $userId
         */
        $userId = $this->user->getId();

        foreach ($data as $item) {
            $post = (new Post())
                ->setId(UuidV4::generate());

            /**
             * @var Post $post
             */
            $post
                ->setCreatedAt($item["date"])
                ->setUserId($userId)
                ->setTitle($item["title"])
                ->setContent("Test")
                ->setChapo("Test");

            /**
             * @var string $createdAt
             */
            $createdAt = $post->getCreatedAt() instanceof \DateTime ?
                $post->getCreatedAt()->format(DATE_ATOM) :
                $post->getCreatedAt();

            $pdo = $this->manager->getPDO();
            $statement = $pdo->prepare("
            INSERT INTO post (id, userId, title, chapo, content, createdAt) 
            VALUES (:id, :userId, :title, :chapo, :content, :createdAt)
        ");
            $statement->bindValue(":id", $post->getId());
            $statement->bindValue(":userId", $post->getUserId());
            $statement->bindValue(":title", $post->getTitle());
            $statement->bindValue(":chapo", $post->getChapo());
            $statement->bindValue(":content", $post->getContent());
            $statement->bindValue(":createdAt", $createdAt);

            $statement->execute();

            $this->posts[] = $post;
        }
    }


}
