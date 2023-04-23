<?php

/**
 * HomeControllerTest file
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
use App\Controller\HomeController;
use App\Factory\Form\ContactForm;
use App\Factory\Router\Route;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Twig\Twig;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Test HomeController cases
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
#[CoversClass(HomeController::class)]
#[CoversClass(ContactForm::class)]
#[CoversClass(Router::class)]
#[CoversClass(Route::class)]
#[CoversClass(Twig::class)]
class HomeControllerTest extends TestCase
{


    /**
     * Test should be to render index of home controller.
     *
     * @return void
     *
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws PHPMailerException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws RouterException
     */
    #[Test]
    #[TestDox("should be to render index of home controller")]
    public function itRenderIndexOfHomeController(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";

        (new DotEnv())->load();
        Container::loadServices();

        $response = (new HomeController())->index();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString("<title>P5 DAPS BLOG - Homepage</title>", $content);

    }


    /**
     * Test should be to post contact form invalid.
     *
     * @return void
     *
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws PHPMailerException
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    #[Test]
    #[TestDox("should be to post contact form invalid")]
    public function itPostContactFormNotValid(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["email"] = "";
        $_POST["subject"] = "";
        $_POST["message"] = "";

        (new DotEnv())->load();
        Container::loadServices();
        $_POST["_csrf"] = Csrf::generateTokenCsrf("contact");
        Container::loadServices();

        $response = (new HomeController())->index();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ est requis.</div>",
            $content
        );

        $_POST["email"] = "NotValidEmail";
        $_POST["subject"] = "lt";
        $_POST["message"] = "lt";
        Container::loadServices();

        $response = (new HomeController())->index();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">L'email n'est pas valide.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir entre 5 et 120 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<div class=\"invalid-feedback\">Ce champ doit contenir au minimum 10 caractères.</div>",
            html_entity_decode(htmlspecialchars_decode($content))
        );

    }


    /**
     * Test should be to post contact form valid.
     *
     * @return void
     *
     * @throws DotEnvException
     * @throws ReflectionException
     * @throws PHPMailerException
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws RouterException
     */
    #[Test]
    #[TestDox("should be to post contact form valid")]
    public function itPostContactFormValid(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["email"] = "test@test.fr";
        $_POST["subject"] = "[TEST] Contact Form";
        $_POST["message"] = "This is a test for contact form of home controller";

        (new DotEnv())->load();
        Container::loadServices();
        $_POST["_csrf"] = Csrf::generateTokenCsrf("contact");
        Container::loadServices();

        $response = (new HomeController())->index();
        $response->send();

        $this->assertEquals(302, http_response_code());

    }


    /**
     * Test should be to use generateUrl, redirectTo,
     * httpNotFound and httpForbidden response in AbstractController.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to use generateUrl, redirectTo, httpNotFound and httpForbidden response in AbstractController")]
    public function itSomeFunctionsAbstractController(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_ENV["TEST_PATH"] = "_test";

        (new DotEnv())->load();
        Container::loadServices();

        $controller = new HomeController();

        // Generate url and absolute url without params
        $url = $controller->generateUrl("app_posts");

        $this->assertEquals("/posts", $url);

        $url = $controller->generateUrl("app_posts", isAbsolute: true);

        $this->assertEquals("http://localhost/posts", $url);

        // Generate url with params
        $url = $controller->generateUrl("app_post_details", ["id" => "da0a369a-6474-4e18-8aab-6f7d32145279"]);

        $this->assertEquals("/post/da0a369a-6474-4e18-8aab-6f7d32145279", $url);

        // Redirect to url

        $controller->redirectTo("app_posts")->send();

        $this->assertEquals(302, http_response_code());

        // Not found response

        $controller->responseHttpNotFound();

        $this->assertEquals(404, http_response_code());

        // Forbidden response

        $controller->responseHttpForbidden();

        $this->assertEquals(403, http_response_code());

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("The route app_bad_route doesn't exists, please check this name.");

        $controller->generateUrl("app_bad_route");

    }


}
