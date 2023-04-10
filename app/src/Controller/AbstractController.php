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
use App\Factory\Twig\Twig;
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
     * @throws SyntaxError|RuntimeError|LoaderError
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


}
