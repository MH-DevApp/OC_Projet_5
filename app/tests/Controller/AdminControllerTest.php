<?php

/**
 * AdminControllerTest file
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

namespace tests\Controller;


use App\Auth\Auth;
use App\Controller\AbstractController;
use App\Controller\AdminController;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Kernel;
use App\Repository\PostRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use Exception;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test AdminController cases
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
#[CoversClass(AdminController::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(PostRepository::class)]
#[CoversClass(Manager::class)]
#[CoversClass(User::class)]
#[CoversClass(Post::class)]
#[CoversClass(Comment::class)]
class AdminControllerTest extends TestCase
{

    private ?User $user = null;
    private Manager $manager;

    /**
     * Init environment
     *
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws Exception
     */
    #[Before]
    public function init(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_SERVER["REQUEST_URI"] = "/admin/dashboard";
        $_ENV["TEST_PATH"] = "_test";

        $_COOKIE = [];
        (new DotEnv())->load();
        Container::loadServices();

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");
        $this->manager = $manager;

        $this->createUser();

    }

    /**
     * End each tests
     *
     * @return void
     */
    #[After]
    public function end(): void
    {
        $this->deleteUser();
    }


    /**
     * Test should be to render not found page of admin controller
     * with not authenticated.
     *
     * @return void
     *
     * @throws RouterException
     */
    #[Test]
    #[TestDox("should be to render not found page of admin controller with not authenticated")]
    public function itNotFoundOfAdminControllerWithNotAuthenticated(): void
    {
        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be to redirect to dashboard users of admin controller.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to redirect to dashboard users of admin controller")]
    public function itRedirectToDashboardUsersOfAdminController(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $_COOKIE = $request->getCookie();
        Container::loadServices();

        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(302, http_response_code());

        $_SERVER["REQUEST_URI"] = "/admin/dashboard/users";
        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "TABLEAU DE BORD - USERS",
            html_entity_decode(htmlspecialchars_decode($content))
        );

    }


    /**
     * Test should be to render not found page of admin
     * controller with bad page.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render not found page of admin controller with bad page")]
    public function itNotFoundOfAdminControllerWithBadPage(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/dashboard/badPage";

        Container::loadServices();

        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be to render not found page of admin
     * controller with bad page.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render not found page of get entities request with bad page")]
    public function itNotFoundOfGetEntitiesRequestWithBadPage(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/dashboard/entities/badpage";

        Container::loadServices();

        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be to render json of get entities request
     * of Admin controller.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of get entities request of Admin controller")]
    public function itGetEntitiesRequestOfAdminController(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/dashboard/entities/users";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(1, count($content["entities"]));

        $_SERVER["REQUEST_URI"] = "/admin/dashboard/entities/posts";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(0, count($content["entities"]));

    }


    /**
     * Test should be to render json of request of
     * toggle status user failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle status user failed")]
    public function itRequestOfToggleStatusUserFailed(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $badId = UuidV4::generate();

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/user/".$badId."/toggle-status";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le status de cet utilisateur, celui-ci n'a pas été trouvé dans la base de données.",
            $content["message"]
        );

        $id = $this->user?->getId() ?: "";
        $_SERVER["REQUEST_URI"] = "/admin/user/".$id."/toggle-status";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le status de votre compte.",
            $content["message"]
        );

    }


    /**
     * Test should be to render json of request of
     * toggle status user success.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle status user success")]
    public function itRequestOfToggleStatusUserSuccess(): void
    {
        $newUser =(new User())
            ->setLastname("Test1")
            ->setFirstname("Test1")
            ->setPseudo("Test1")
            ->setEmail("test1@test.fr")
            ->setRole("ROLE_USER")
            ->setPassword(password_hash("password", PASSWORD_ARGON2ID))
            ->setStatus(User::STATUS_CODE_REGISTERED);

        $this->manager->flush($newUser);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $id = $newUser->getId() ?: "";

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/user/".$id."/toggle-status";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(
            "Le status de l'utilisateur a été désactivé avec succès.",
            $content["message"]
        );

        /**
         * @var User $newUser
         */
        $newUser = (new UserRepository())->findByOne(
            ["id" => $id],
            classObject: User::class
        );

        $this->assertTrue($newUser->getStatus() === User::STATUS_CODE_DEACTIVATED);

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(
            "Le status de l'utilisateur a été activé avec succès.",
            $content["message"]
        );

        /**
         * @var User $newUser
         */
        $newUser = (new UserRepository())->findByOne(
            ["id" => $id],
            classObject: User::class
        );

        $this->assertTrue($newUser->getStatus() === User::STATUS_CODE_REGISTERED);

        $this->manager->delete($newUser);

    }


    /**
     * Test should be to render json of request of
     * toggle role user failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle role user failed")]
    public function itRequestOfToggleRoleUserFailed(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $badId = UuidV4::generate();

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/user/".$badId."/toggle-role";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le role de cet utilisateur, celui-ci n'a pas été trouvé dans la base de données.",
            $content["message"]
        );

        $id = $this->user?->getId() ?: "";
        $_SERVER["REQUEST_URI"] = "/admin/user/".$id."/toggle-role";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le role de votre compte.",
            $content["message"]
        );

    }


    /**
     * Test should be to render json of request of
     * toggle role user success.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle role user success")]
    public function itRequestOfToggleRoleUserSuccess(): void
    {
        $newUser =(new User())
            ->setLastname("Test1")
            ->setFirstname("Test1")
            ->setPseudo("Test1")
            ->setEmail("test1@test.fr")
            ->setRole("ROLE_USER")
            ->setPassword(password_hash("password", PASSWORD_ARGON2ID))
            ->setStatus(User::STATUS_CODE_REGISTERED);

        $this->manager->flush($newUser);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $id = $newUser->getId() ?: "";

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/user/".$id."/toggle-role";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(
            "Le rôle de l'utilisateur a été modifié avec succès.",
            $content["message"]
        );

        /**
         * @var User $newUser
         */
        $newUser = (new UserRepository())->findByOne(
            ["id" => $id],
            classObject: User::class
        );

        $this->assertTrue($newUser->getRole() === "ROLE_ADMIN");

        (new Kernel())->run();

        /**
         * @var User $newUser
         */
        $newUser = (new UserRepository())->findByOne(
            ["id" => $id],
            classObject: User::class
        );

        $this->assertTrue($newUser->getRole() === "ROLE_USER");

        $this->manager->delete($newUser);

    }


    /**
     * Test should be to render json of request of toggle
     * published post failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle published post failed")]
    public function itRequestOfTogglePublishedPostFailed(): void
    {
        $newPost =(new Post())
            ->setTitle("Test")
            ->setChapo("Test")
            ->setContent("Test")
            ->setIsPublished(true)
            ->setIsFeatured(true)
            ->setUserId($this->user?->getId() ?? "")
        ;

        $this->manager->flush($newPost);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $id = UuidV4::generate();

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/post/".$id."/toggle-published";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le status de publication de ce post, celui-ci n'a pas été trouvé dans la base de données.",
            $content["message"]
        );

        $id = $newPost->getId() ?: "";
        $_SERVER["REQUEST_URI"] = "/admin/post/".$id."/toggle-published";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Il n'est pas possible de désactiver la publication d'un post une fois qu'il est mis en avant.",
            $content["message"]
        );

        $this->manager->delete($newPost);

    }


    /**
     * Test should be to render json of request of toggle
     * published post failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle published post success")]
    public function itRequestOfTogglePublishedPostSuccess(): void
    {
        $newPost =(new Post())
            ->setTitle("Test")
            ->setChapo("Test")
            ->setContent("Test")
            ->setUserId($this->user?->getId() ?? "")
        ;

        $this->manager->flush($newPost);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        $id = $newPost->getId() ?: "";

        /**
         * @var Request $request
         */
        $request = Container::getService("request");
        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/post/".$id."/toggle-published";
        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(
            "Le status de publication du post a été modifié avec succès.",
            $content["message"]
        );

        /**
         * @var Post $postUpdated
         */
        $postUpdated = (new PostRepository())->findByOne(
            ["id" => $id],
            classObject: Post::class
        );

        $this->assertTrue($postUpdated->getIsPublished());

        $this->manager->delete($newPost);

    }


    /**
     * Test should be to render json of request of toggle
     * featured post failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle featured post failed")]
    public function itRequestOfToggleFeaturedPostFailed(): void
    {
        /**
         * @var array<int, Post>
         */
        $posts = [];

        for ($i=0; $i<6; $i++) {
            $posts[] =(new Post())
                ->setTitle("Test ".$i)
                ->setChapo("Test ".$i)
                ->setContent("Test ".$i)
                ->setIsPublished($i !== 0)
                ->setIsFeatured($i !== 0)
                ->setUserId($this->user?->getId() ?? "")
            ;
        }

        $this->manager->flush(...$posts);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $badId = UuidV4::generate();

        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/post/".$badId."/toggle-featured";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Impossible de modifier le status de la mise en avant de ce post, celui-ci n'a pas été trouvé dans la base de données.",
            $content["message"]
        );

        $id = $posts[0]->getId() ?: "";
        $_SERVER["REQUEST_URI"] = "/admin/post/".$id."/toggle-featured";

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Le nombre maximum de posts pour la mise en avant a été atteint.",
            $content["message"]
        );

        $posts[1]->setIsFeatured(false);
        $this->manager->flush($posts[1]);

        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertFalse($content["success"]);
        $this->assertEquals(
            "Le post doit être au statut publié pour qu'il soit mis en avant.",
            $content["message"]
        );

        foreach ($posts as $post) {
            $this->manager->delete($post);
        }

    }


    /**
     * Test should be to render json of request of toggle
     * featured post failed.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws RouterException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render json of request of toggle featured post success")]
    public function itRequestOfToggleFeaturedPostSuccess(): void
    {
        $newPost =(new Post())
            ->setTitle("Test")
            ->setChapo("Test")
            ->setContent("Test")
            ->setIsPublished(true)
            ->setUserId($this->user?->getId() ?? "")
        ;

        $this->manager->flush($newPost);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        $id = $newPost->getId() ?: "";

        /**
         * @var Request $request
         */
        $request = Container::getService("request");
        $_COOKIE = $request->getCookie();
        $_SERVER["REQUEST_URI"] = "/admin/post/".$id."/toggle-featured";
        Container::loadServices();

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = json_decode(ob_get_contents() ?: "", true);
        ob_get_clean();

        $this->assertIsArray($content);
        $this->assertTrue($content["success"]);
        $this->assertEquals(
            "Le status de la mise en avant du post a été modifié avec succès.",
            $content["message"]
        );

        /**
         * @var Post $postUpdated
         */
        $postUpdated = (new PostRepository())->findByOne(
            ["id" => $id],
            classObject: Post::class
        );

        $this->assertTrue($postUpdated->getIsFeatured());

        $this->manager->delete($newPost);

    }


    /**
     * Create user test
     *
     * @throws Exception
     */
    private function createUser(): void
    {
        $this->user = (new User())
            ->setLastname("Test")
            ->setFirstname("Test")
            ->setPseudo("Test")
            ->setEmail("test@test.fr")
            ->setRole("ROLE_ADMIN")
            ->setStatus(User::STATUS_CODE_REGISTERED)
            ->setPassword(password_hash(
                "password",
                PASSWORD_ARGON2ID
            ));

        $this->manager->flush($this->user);
    }


    /**
     * delete user test
     *
     * @return void
     */
    private function deleteUser(): void
    {
        if ($this->user) {
            /**
             * @var Session $session
             */
            $session = (new SessionRepository())->findByOne(
                ["userId" => $this->user->getId() ?: ""],
                classObject: Session::class
            );

            if (is_object($session)) {
                $this->manager->delete($session);
            }

            $this->manager->delete($this->user);
        }

    }


}
