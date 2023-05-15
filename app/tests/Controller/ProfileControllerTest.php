<?php

/**
 * ProfileControllerTest file
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
use App\Controller\ProfileController;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Form\ProfileResetPasswordForm;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\RouterException;
use App\Factory\Twig\Twig;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Mapper\Mapper;
use App\Kernel;
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
 * Test ProfileController cases
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
#[CoversClass(ProfileController::class)]
#[CoversClass(ProfileResetPasswordForm::class)]
#[CoversClass(Twig::class)]
#[CoversClass(Mapper::class)]
class ProfileControllerTest extends TestCase
{

    private Manager $manager;
    private User $user;

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
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_SERVER["REQUEST_URI"] = "/profile";
        $_COOKIE = [];
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
            ->setStatus(User::STATUS_CODE_REGISTERED)
            ->setPassword(password_hash("password", PASSWORD_ARGON2ID))
            ->setEmail("test@test.fr");
        $this->manager->flush($this->user);

    }


    /**
     * End test
     *
     * @return void
     */
    #[After]
    public function end(): void
    {
        /**
         * @var Session|false $session
         */
        $session = (new SessionRepository())->findByOne([
            "userId" => $this->user->getId() ?? ""
        ], classObject: Session::class);

        if ($session) {
            $this->manager->delete($session);
        }

        $this->manager->delete($this->user);

    }


    /**
     * Test should be to show not found page with not authenticate user.
     *
     * @return void
     *
     * @throws DotEnvException
     * @throws RouterException
     */
    #[Test]
    #[TestDox("should be to show not found page with not authenticate user")]
    public function itNotFoundPageWithNotAuthenticateUser(): void
    {

        (new DotEnv())->load();
        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be to render profile page with authenticate user.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render profile page with authenticate user")]
    public function itRenderProfilePageWithAuthenticateUser(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        $_COOKIE = $request->getCookie();

        (new DotEnv())->load();
        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Mon profil</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

    }


    /**
     * Test should be to form reset password in profile failed.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to form reset password in profile failed")]
    public function itFormResetPasswordInProfileFailed(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        $_SERVER["REQUEST_METHOD"] = "POST";

        $_COOKIE = $request->getCookie();

        $_POST = [
            "actualPassword" => "",
            "newResetPassword" => "",
            "confirmNewResetPassword" => ""
        ];

        (new DotEnv())->load();
        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $_POST = [
            "actualPassword" => "t",
            "newResetPassword" => "12345",
            "confirmNewResetPassword" => "t"
        ];

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Le mot de passe actuel est incorrect.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 6 et 20 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $_POST = [
            "actualPassword" => "password",
            "newResetPassword" => "123456",
            "confirmNewResetPassword" => "1234567"
        ];

        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">La confirmation du mot de passe n'est pas identique.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

    }


    /**
     * Test should be to form reset password in profile successfully.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to form reset password in profile successfully")]
    public function itFormResetPasswordInProfileSuccessfully(): void
    {
        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        $_SERVER["REQUEST_METHOD"] = "POST";

        $_COOKIE = $request->getCookie();

        $_POST = [
            "actualPassword" => "password",
            "newResetPassword" => "123456",
            "confirmNewResetPassword" => "123456",
            "_csrf" => Csrf::generateTokenCsrf("profile-password-reset")
        ];

        (new DotEnv())->load();
        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Mot de passe modifié</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        /**
         * @var User $user
         */
        $user = (new UserRepository())->findByOne(
            ["id" => $this->user->getId() ?? ""],
            classObject: User::class
        );

        $this->assertTrue(password_verify("123456", $user->getPassword() ?? ""));

    }


}
