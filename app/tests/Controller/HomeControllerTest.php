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


use App\Controller\AbstractController;
use App\Controller\HomeController;
use App\Factory\Form\ContactForm;
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
#[CoversClass(AbstractController::class)]
#[CoversClass(HomeController::class)]
#[CoversClass(ContactForm::class)]
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
     */
    #[Test]
    #[TestDox("should be to render index of home controller")]
    public function itRenderIndexOfHomeController(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";

        (new DotEnv())->load();
        Container::loadServices();

        $response = (new HomeController())->index();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertStringContainsString("SUCCESS", $content);

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
            "<small id=\"email\">Ce champ est requis.</small>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<small id=\"subject\">Ce champ est requis.</small>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<small id=\"message\">Ce champ est requis.</small>",
            html_entity_decode(htmlspecialchars_decode($content))
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
            "<small id=\"email\">L'email n'est pas valide.</small>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<small id=\"subject\">Ce champ doit contenir entre 5 et 120 caractères.</small>",
            html_entity_decode(htmlspecialchars_decode($content))
        );
        $this->assertStringContainsString(
            "<small id=\"message\">Ce champ doit contenir au minimum 10 caractères.</small>",
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
     */
    #[Test]
    #[TestDox("should be to post contact form valid")]
    public function itPostContactFormValid(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_POST["email"] = "test@test.fr";
        $_POST["subject"] = "[TEST] Contact Form";
        $_POST["message"] = "This is a test for contact form of home controller";

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
            "SUCCESS",
            html_entity_decode(htmlspecialchars_decode($content))
        );

    }


}
