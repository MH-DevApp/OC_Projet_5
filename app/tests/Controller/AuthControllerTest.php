<?php

/**
 * AuthControllerTest file
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
use App\Controller\AuthController;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Form\AbstractForm;
use App\Factory\Form\ConnexionForm;
use App\Factory\Form\ForgottenPasswordForm;
use App\Factory\Form\ForgottenPasswordResetForm;
use App\Factory\Form\RegisterForm;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Kernel;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use DateTime;
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
 * Test AuthController cases
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
#[CoversClass(AuthController::class)]
#[CoversClass(Auth::class)]
#[CoversClass(AbstractForm::class)]
#[CoversClass(ConnexionForm::class)]
#[CoversClass(RegisterForm::class)]
#[CoversClass(ForgottenPasswordForm::class)]
#[CoversClass(ForgottenPasswordResetForm::class)]
#[CoversClass(Manager::class)]
class AuthControllerTest extends TestCase
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
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";
        $_COOKIE = [];
        (new DotEnv())->load();
        Container::loadServices();

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");
        $this->manager = $manager;
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
     * Test should be to render connexion page of auth controller.
     *
     * @return void
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to render connexion page of auth controller")]
    public function itRenderConnexionPageOfAuthController(): void
    {
        $this->initPost("GET");
        Container::loadServices();

        $controller = (new AuthController())->login();

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Connexion</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post connexion form invalid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post connexion form invalid")]
    public function itPostConnexionFormNotValid(): void
    {
        $this->initPost(
            "POST",
            [
                "email" => "",
                "password" => "",
                "_csrf" => Csrf::generateTokenCsrf("authenticate") ?: ""
            ]
        );

        $this->createUser();

        Container::loadServices();

        $response = (new AuthController())->login();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $_POST["email"] = "test";
        Container::loadServices();

        $response = (new AuthController())->login();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">L'email n'est pas valide.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $_POST["email"] = "test@test.fr";
        $_POST["password"] = "!password!";
        Container::loadServices();

        $response = (new AuthController())->login();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"col-12 text-center text-danger\">L'Email et/ou le mot de passe sont incorrects.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post connexion form valid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post connexion form valid")]
    public function itPostConnexionFormValid(): void
    {
        $this->initPost(
            "POST",
            [
                "email" => "test@test.fr",
                "password" => "password",
                "_csrf" => Csrf::generateTokenCsrf("authenticate") ?: ""
            ]
        );

        $this->createUser();

        if ($this->user) {
            $this->user->setStatus(User::STATUS_CODE_REGISTERED);
            $this->manager->flush($this->user);
        }

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $response = (new AuthController())->login();
        $response->send();

        $this->assertEquals(302, http_response_code());
        $this->assertTrue($request->hasCookie("session"));
        $this->assertNotNull(Auth::$currentUser);

        if ($this->user) {
            $this->user->setStatus(User::STATUS_CODE_DEACTIVATED);
            $this->manager->flush($this->user);
        }

        $_SERVER["REQUEST_URI"] = "/auth/login";
        $_COOKIE = $request->getCookie();

        $response = (new Kernel())->run();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "Votre compte a été désactivé",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertFalse($request->hasCookie("session"));
        $this->assertNull(Auth::$currentUser);
    }

    /**
     * Test should be to redirect to home page of auth
     * controller with session cookie.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to redirect to home page of auth controller with session cookie")]
    public function itRedirectToHomePageOfAuthControllerWithSessionCookie(): void
    {
        $this->initPost("GET");
        Container::loadServices();

        $this->createUser();

        if ($this->user) {
            $this->user->setStatus(User::STATUS_CODE_REGISTERED);
            $this->manager->flush($this->user);
        }

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        $auth->authenticate([
            "email" => "test@test.fr",
            "password" => "password"
        ]);

        // Login page

        $controller = (new AuthController())->login();
        $controller->send();

        $this->assertEquals(302, http_response_code());

        // Register page

        $controller = (new AuthController())->register();
        $controller->send();

        $this->assertEquals(302, http_response_code());

        // forgotten password page

        $controller = (new AuthController())->forgottenPassword();
        $controller->send();

        $this->assertEquals(302, http_response_code());
    }


    /**
     * Test should be to log out user.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to log out user")]
    public function itLogOutUser(): void
    {
        $this->initPost(
            "POST",
            [
                "email" => "test@test.fr",
                "password" => "password",
                "_csrf" => Csrf::generateTokenCsrf("authenticate") ?: ""
            ]
        );

        $this->createUser();

        if ($this->user) {
            $this->user->setStatus(User::STATUS_CODE_REGISTERED);
            $this->manager->flush($this->user);
        }

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $response = (new AuthController())->login();
        $response->send();

        $this->assertTrue($request->hasCookie("session"));
        $this->assertNotNull(Auth::$currentUser);

        $response = (new AuthController())->logout();
        $response->send();

        $this->assertFalse($request->hasCookie("session"));
        $this->assertNull(Auth::$currentUser);
        $this->assertEquals(302, http_response_code());
    }


    /**
     * Test should be to render register page of auth controller.
     *
     * @return void
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to render register page of auth controller")]
    public function itRenderRegisterPageOfAuthController(): void
    {
        $this->initPost("GET");
        Container::loadServices();

        $controller = (new AuthController())->register();

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString("<title>P5 DAPS BLOG - Inscription</title>", $content);
    }


    /**
     * Test should be to post register form invalid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post register form invalid")]
    public function itPostRegisterFormNotValid(): void
    {
        $this->initPost(
            "POST",
            [
                "lastname" => "",
                "firstname" => "",
                "pseudo" => "",
                "email" => "",
                "password" => "",
                "confirmPassword" => "",
                "_csrf" => Csrf::generateTokenCsrf("register") ?: ""
            ]
        );

        $this->createUser();

        Container::loadServices();

        $response = (new AuthController())->register();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost(
            "POST",
            [
                "lastname" => "t",
                "firstname" => "t",
                "pseudo" => "t",
                "email" => "test",
                "password" => "t",
                "confirmPassword" => "",
                "_csrf" => Csrf::generateTokenCsrf("register") ?: ""
            ]
        );

        Container::loadServices();

        $response = (new AuthController())->register();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 2 et 50 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 2 et 50 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 2 et 50 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">L'email n'est pas valide.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 6 et 20 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost(
            "POST",
            [
                "lastname" => "t",
                "firstname" => "t",
                "pseudo" => "Test",
                "email" => "test@test.fr",
                "password" => "123456",
                "confirmPassword" => "12345678",
                "_csrf" => Csrf::generateTokenCsrf("register") ?: ""
            ]
        );

        Container::loadServices();

        $response = (new AuthController())->register();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Le pseudo existe déjà.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">L'email existe déjà.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">La confirmation du mot de passe n'est pas identique.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post register form valid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post register form valid")]
    public function itPostRegisterFormValid(): void
    {
        $this->initPost(
            "POST",
            [
                "lastname" => "Test",
                "firstname" => "Test",
                "pseudo" => "Test",
                "email" => "test@test.fr",
                "password" => "123456",
                "confirmPassword" => "123456",
                "_csrf" => Csrf::generateTokenCsrf("register") ?: ""
            ]
        );

        Container::loadServices();

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $response = (new AuthController())->register();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Inscription réussi</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        /**
         * @var string $email
         */
        $email = $request->getPost("email");

        /**
         * @var User $user
         */
        $user = (new UserRepository())->findByOne(
            ["email" => $email],
            classObject: User::class
        );

        $this->user = $user;

        $this->assertInstanceOf(User::class, $this->user);
    }


    /**
     * Test should be to valid email successful.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to valid email successful")]
    public function itValidEmailSuccessful(): void
    {
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setEmailValidateToken($hashToken)
                ->setExpiredEmailTokenAt();
            $this->manager->flush($this->user);
        }

        $controller = (new AuthController())->validEmail($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Email confirmé</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to valid email failed with bad token.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to valid email failed with bad token")]
    public function itValidEmailFiledBadToken(): void
    {
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token."badtoken");

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setEmailValidateToken($hashToken)
                ->setExpiredEmailTokenAt();
            $this->manager->flush($this->user);
        }

        (new AuthController())->validEmail($token);

        $this->assertEquals(404, http_response_code());
    }


    /**
     * Test should be to valid email failed with token expired.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to valid email failed with token expired")]
    public function itValidEmailFiledTokenExpired(): void
    {
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setEmailValidateToken($hashToken)
                ->setExpiredEmailTokenAt(new DateTime("-3 minutes"));
            $this->manager->flush($this->user);
        }

        $controller = (new AuthController())->validEmail($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Lien d'activation de votre email a expiré</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost("POST", [
            "email" => "test@test.fr",
            "_csrf" => Csrf::generateTokenCsrf("forgotten-password") ?: ""
        ]);

        Container::loadServices();

        $controller = (new AuthController())->validEmail($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Nouveau lien envoyé</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to render forgotten password page of auth controller.
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render forgotten password page of auth controller")]
    public function itRenderForgottenPasswordPageOfAuthController(): void
    {
        $this->initPost("GET");
        Container::loadServices();

        $controller = (new AuthController())->forgottenPassword();

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Mot de passe perdu</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post forgotten password form invalid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post forgotten password form invalid")]
    public function itPostForgottenPasswordFormNotValid(): void
    {
        $this->initPost(
            "POST",
            [
                "email" => "",
                "_csrf" => Csrf::generateTokenCsrf("forgotten-password") ?: ""
            ]
        );

        $this->createUser();

        Container::loadServices();

        $response = (new AuthController())->forgottenPassword();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost(
            "POST",
            [
                "email" => "test",
                "_csrf" => Csrf::generateTokenCsrf("forgotten-password") ?: ""
            ]
        );

        Container::loadServices();

        $response = (new AuthController())->forgottenPassword();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">L'email n'est pas valide.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post forgotten password form valid.
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post forgotten password form valid")]
    public function itPostForgottenPasswordFormValid(): void
    {
        $this->initPost(
            "POST",
            [
                "email" => "test@test.fr",
                "_csrf" => Csrf::generateTokenCsrf("forgotten-password") ?: ""
            ]
        );

        $this->createUser();

        Container::loadServices();

        $response = (new AuthController())->forgottenPassword();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<h1>Email envoyé !</h1>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to not found page on forgotten password
     * reset bad token.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to not found page on forgotten password reset bad token")]
    public function itNotFoundPageForgottenPasswordResetBadToken(): void
    {
        $this->initPost("GET");
        Container::loadServices();
        $token = UuidV4::generate();

        $controller = (new AuthController())->resetForgottenPassword($token);
        $controller->send();

        $this->assertEquals(404, http_response_code());
    }


    /**
     * Test should be to render forgotten password reset with
     * token expired.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render forgotten password reset with token expired")]
    public function itRenderForgottenPasswordResetTokenExpired(): void
    {
        $this->initPost("GET");
        Container::loadServices();
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setForgottenPasswordToken($hashToken)
                ->setExpiredTokenAt(new DateTime("-3 minutes"));
            $this->manager->flush($this->user);
        }

        $controller = (new AuthController())->resetForgottenPassword($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<h1>Le lien est expiré !</h1>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to render forgotten password reset successfully.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to render forgotten password reset successfully")]
    public function itRenderForgottenPasswordResetSuccessfully(): void
    {
        $this->initPost("GET");
        Container::loadServices();
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setForgottenPasswordToken($hashToken)
                ->setExpiredTokenAt();
            $this->manager->flush($this->user);
        }

        $controller = (new AuthController())->resetForgottenPassword($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<title>P5 DAPS BLOG - Réinitialiser mot de passe</title>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post forgotten password reset form invalid.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post forgotten password reset form invalid")]
    public function itPostForgottenPasswordResetFormInvalid(): void
    {
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setForgottenPasswordToken($hashToken)
                ->setExpiredTokenAt();
            $this->manager->flush($this->user);
        }

        $this->initPost("POST", [
            "newPassword" => "",
            "confirmNewPassword" => ""
        ]);

        Container::loadServices();

        $controller = (new AuthController())->resetForgottenPassword($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost("POST", [
            "newPassword" => "12345",
            "confirmNewPassword" => ""
        ]);

        Container::loadServices();

        $controller = (new AuthController())->resetForgottenPassword($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 6 et 20 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

        $this->initPost("POST", [
            "newPassword" => "123456",
            "confirmNewPassword" => "1234567"
        ]);

        Container::loadServices();

        $controller = (new AuthController())->resetForgottenPassword($token);

        ob_start();
        $controller->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">La confirmation du mot de passe n'est pas identique.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
    }


    /**
     * Test should be to post forgotten password reset form valid.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to post forgotten password reset form valid")]
    public function itPostForgottenPasswordResetFormValid(): void
    {
        $token = UuidV4::generate();

        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);

        $this->createUser();

        if ($this->user) {
            $this->user
                ->setForgottenPasswordToken($hashToken)
                ->setExpiredTokenAt();
            $this->manager->flush($this->user);
        }

        $this->assertTrue(password_verify("password", $this->user?->getPassword() ?: ""));

        $this->initPost("POST", [
            "newPassword" => "123456",
            "confirmNewPassword" => "123456",
            "_csrf" => Csrf::generateTokenCsrf("forgotten-password-reset") ?: ""
        ]);

        Container::loadServices();

        $controller = (new AuthController())->resetForgottenPassword($token);
        $controller->send();

        $this->assertEquals(302, http_response_code());

        /**
         * @var User $user
         */
        $user = (new UserRepository())->findByOne(
            ["id" => $this->user?->getId() ?: ""],
            classObject: User::class
        );

        $this->assertTrue(password_verify("123456", $user->getPassword() ?: ""));
        $this->assertNull($user->getForgottenPasswordToken());
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


    /**
     * Init Post field and method request
     *
     * @param string $method
     * @param array<string, string> $fields
     *
     * @return void
     */
    private function initPost(
        string $method,
        array $fields = []
    ): void {
        $_SERVER["REQUEST_METHOD"] = $method;

        foreach ($fields as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}
