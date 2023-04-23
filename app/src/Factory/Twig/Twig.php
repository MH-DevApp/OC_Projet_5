<?php

/**
 * Twig file
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

namespace App\Factory\Twig;


use App\Entity\User;
use App\Factory\Router\Router;
use App\Factory\Utils\Csrf\Csrf;
use App\Service\Container\Container;
use Exception;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Twig class
 *
 * Instance Environment Twig.
 * Add some functions and globals properties to template twig.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Twig
{
    private Environment $twig;
    /**
     * @var array<string, User|null> $app
     */
    private static array $app = [
        "user" => null
    ];


    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__."/../../../templates");
        $this->twig = new Environment(
            $loader, [
                "cache" => false,
                "debug" => true,
                "strict_variables" => true
            ]
        );
        $this->twig->addExtension(new DebugExtension());
        $this->twig->addFunction($this->generateUrl());
        $this->twig->addFunction($this->generateCSRF());
        $this->twig->addFunction($this->getAssetsFolder());
        $this->twig->addGlobal("app", self::$app);

    }


    /**
     * Get environment twig of the application to
     * render templates html.
     *
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;

    }


    /**
     * Set current user if is authenticated
     *
     * @param User $user
     *
     * @return void
     */
    public static function setCurrentUser(User $user): void
    {
        self::$app["user"] = $user;

    }


    /**
     * Return Csrf twig function to generate token CSRF in
     * template twig.
     *
     * @return TwigFunction
     *
     * @throws Exception
     */
    private function generateCSRF(): TwigFunction
    {
        return new TwigFunction(
            "csrf",
            function(string $key) {
                $csrfToken = Csrf::generateTokenCsrf($key);

                if (!$csrfToken) {
                    throw new Exception("Error for generate a token CSRF.");
                }

                return $csrfToken;
            }
        );

    }


    /**
     * Return Path twig function to generate url in
     * template twig.
     *
     * @return TwigFunction
     *
     */
    private function generateUrl(): TwigFunction
    {
        return new TwigFunction(
            "path",
            function (string $name, array $params = []): string {
                /**
                 * @var Router $router
                 */
                $router = Container::getService("router");

                return $router->generateUrl($name, $params);

            }
        );

    }


    /**
     * Get assets folder path.
     *
     * @return TwigFunction
     */
    private function getAssetsFolder(): TwigFunction
    {
        return new TwigFunction(
            "asset",
            function (string $path): string {
                $path = trim($path, "/");

                return "/assets/".$path;

            }
        );

    }


}
