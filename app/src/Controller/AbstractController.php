<?php

/**
 * AbstractController file
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

namespace App\Controller;


use App\Auth\Auth;
use App\Entity\User;
use App\Factory\Router\Response;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Twig\Twig;
use App\Service\Container\Container;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * AbstractController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class AbstractController
{
    private Router $router;


    public function __construct()
    {
        /**
         * @var Router $router
         */
        $router = Container::getService("router");
        $this->router = $router;

    }


    /**
     * Get Current User
     *
     * @return User|null
     */
    protected function user(): ?User
    {
        return Auth::$currentUser;

    }


    /**
     * Render template twig in the response
     *
     * @param string $path
     * @param array<string, mixed> $params
     * @param int $statusCode
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render(
        string $path,
        array $params = [],
        int $statusCode = 200
    ): Response
    {
        $twig = new Twig();
        return new Response(
            $twig->getTwig()->render($path, $params),
            $statusCode
        );

    }


    /**
     * Generate url
     *
     * @param string $name
     * @param array $params
     * @param bool $isAbsolute
     *
     * @return string
     *
     * @throws RouterException
     */
    public function generateUrl(
        string $name,
        array $params = [],
        bool $isAbsolute = false
    ): string
    {
        return $this->router->generateUrl(
            $name,
            $params,
            $isAbsolute
        );

    }


    /**
     * Redirect to URL
     *
     * @param string $name
     * @param array $params
     *
     * @return Response
     *
     * @throws RouterException
     */
    public function redirectTo(
        string $name,
        array $params = []
    ): Response
    {
        return $this->router->redirectTo(
            $name,
            $params
        );

    }


    /**
     * Return not found response
     *
     * @return Response
     */
    public function responseHttpNotFound(): Response
    {
        return $this->router->httpNotFound();

    }


    /**
     * Return forbidden response.
     *
     * @return Response
     */
    public function responseHttpForbidden(): Response
    {
        return $this->router->httpForbidden();

    }


}
