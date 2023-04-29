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
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Kernel;
use App\Repository\SessionRepository;
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
            "<span class=\"text-center\">TABLEAU DE BORD - USERS</span>",
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
