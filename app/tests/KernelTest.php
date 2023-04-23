<?php

/**
 * KernelTest file
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

namespace tests;


use App\Auth\Auth;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Twig\Twig;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Kernel;
use App\Repository\SessionRepository;
use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use Exception;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Kernel cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Kernel::class)]
class KernelTest extends TestCase
{

    /**
     * Initialise environment of test
     *
     * @return void
     */
    #[Before]
    public function init(): void
    {
        $_ENV["TEST_PATH"] = "_test";
    }


    /**
     * Test should be got a response Ok from router.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be got a response Ok from router")]
    public function itGetResponseOkFromRouter(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_SERVER["REMOTE_ADDR"] = "Test";

        (new DotEnv())->load();
        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString("<title>P5 DAPS BLOG - Homepage</title>", $content);

    }


    /**
     * Test should be got a response Not Found from router.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be got a response Not Found from router")]
    public function itGetResponseNotFoundFromRouter(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/bad-route";

        (new DotEnv())->load();
        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be run kernel with authenticated user.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be run kernel with authenticated user")]
    public function itRunKernelWithAuthenticatedUser(): void
    {
        $_COOKIE = [];
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";

        (new DotEnv())->load();
        Container::loadServices();

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");

        $user = (new User())
            ->setFirstname("Test")
            ->setLastname("Test")
            ->setPseudo("Test")
            ->setEmail("test@test.com")
            ->setStatus(true)
            ->setPassword(password_hash("password", PASSWORD_ARGON2ID));

        $manager->flush($user);

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        $auth->authenticate([
            "email" => $user->getEmail() ?: "",
            "password" => "password"
        ]);

        /**
         * @var Request $request
         */
        $request = Container::getService("request");
        $_COOKIE = $request->getCookie();

        (new Kernel())->run();

        $this->assertNotNull(Auth::$currentUser);

        $session = (new SessionRepository())->findByOne(
            ["userId" => $user->getId() ?: ""],
            classObject: Session::class
        );

        if ($session instanceof Session) {
            $manager->delete($session);
        }

        $manager->delete($user);

    }


}
