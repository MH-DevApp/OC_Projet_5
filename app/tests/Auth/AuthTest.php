<?php

/**
 * AuthTest file
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


use App\Auth\Auth;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
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
 * Test Auth cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Auth::class)]
class AuthTest extends TestCase
{

    private Manager $manager;
    private User $user;


    /**
     * Start tests
     * Initialise params.
     *
     * @throws ReflectionException
     * @throws DotEnvException
     * @throws Exception
     */
    #[Before]
    public function start(): void
    {
        $_ENV["TEST_PATH"] = "_test";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_COOKIE = [];
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

    }


    /**
     * End Tests
     *
     * @return void
     */
    #[After]
    public function end(): void
    {
        /**
         * @var Session $session
         */
        $session = (new SessionRepository())
            ->findByOne(
                ["userId" => $this->user->getId() ?? ""],
                classObject: Session::class
            );

        if (is_object($session)) {
            $this->manager->delete($session);
        }

        $this->manager->delete($this->user);

    }


    /**
     * Test should be to log in successful.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to log in successful")]
    public function itLogInSuccessful(): void
    {
        $_POST["email"] = "test@test.com";
        $_POST["password"] = "password";

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        /**
         * @var array<string, string> $posts
         */
        $posts = $request->getPost();

        $this->assertTrue(
            $auth->authenticate($posts)
        );

        $currentUser = Auth::$currentUser;
        $this->assertNotNull($currentUser);
        $this->assertInstanceOf(User::class, $currentUser);
        $this->assertEquals($this->user->getId(), $currentUser->getId());

    }


    /**
     * Test should be to log in fail.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to log in fail")]
    public function itLogInFail(): void
    {
        $_POST["email"] = "test1@test.com";
        $_POST["password"] = "password1";

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        /**
         * @var array<string, string> $posts
         */
        $posts = $request->getPost();

        $this->assertFalse(
            $auth->authenticate($posts)
        );

        $currentUser = Auth::$currentUser;
        $this->assertNull($currentUser);

    }


    /**
     * Test should be to cookie authenticate available.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to cookie authenticate available")]
    public function itCookieAuthenticateAvailable(): void
    {
        $_POST["email"] = "test@test.com";
        $_POST["password"] = "password";

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        /**
         * @var array<string, string> $posts
         */
        $posts = $request->getPost();

        $this->assertTrue(
            $auth->authenticate($posts)
        );

        $currentUser = Auth::$currentUser;

        $this->assertNotNull($currentUser);

        /**
         * @var Session $session
         */
        $session = (new SessionRepository())
            ->findByOne(
                ["userId" => $currentUser->getId() ?? ""],
                classObject: Session::class
            );

        $this->assertInstanceOf(Session::class, $session);
        $this->assertNotNull($request->getCookie("session"));

        /**
         * @var string $sessionCookieJson
         */
        $sessionCookieJson = $request->getCookie("session");

        /**
         * @var array<string, string> $sessionCookie
         */
        $sessionCookie = json_decode($sessionCookieJson, true);

        $this->assertNotNull($sessionCookie["id"]);
        $this->assertNotNull($sessionCookie["sign"]);
        $this->assertEquals($session->getId(), $sessionCookie["id"]);
        $this->assertEquals($currentUser->getId(), $session->getUserId());
        $this->assertTrue(Csrf::isTokenCsrfValid($sessionCookie["sign"], $session->getId() ?? ""));

        // Delete old session to create a new session
        $auth->authenticate($posts);

        $session = (new SessionRepository())
            ->findByOne(
                ["id" => $sessionCookie["id"]],
                classObject: Session::class
            );

        $this->assertFalse($session);

    }


    /**
     * Test should be to check user is authenticated.
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to check user is authenticated")]
    public function itIsAuthenticated(): void
    {
        $_POST["email"] = "test@test.com";
        $_POST["password"] = "password";

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        $this->assertFalse($auth->isAuthenticated());

        /**
         * @var array<string, string> $posts
         */
        $posts = $request->getPost();

        $this->assertTrue(
            $auth->authenticate($posts)
        );

        $this->assertTrue($auth->isAuthenticated());
        $this->assertEquals($this->user->getId(), Auth::$currentUser?->getId());

        // Test the cookie invalid
        /**
         * @var string $fakeSession
         */
        $fakeSession = json_encode([
            "id" => UuidV4::generate(),
            "sign" => Csrf::generateTokenCsrf(UuidV4::generate())
        ]);

        $request->setCookie(
            "session",
            $fakeSession,
            time() + 60 * 60 * 24 * 14
        );

        $this->assertFalse($auth->isAuthenticated());
        $this->assertEmpty($request->getCookie("session"));



    }


}
