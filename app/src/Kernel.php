<?php

/**
 * Kernel file
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

namespace App;

use App\Auth\Auth;
use App\Factory\Router\Response;
use App\Factory\Router\Router;
use App\Factory\Twig\Twig;
use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use ReflectionException;

/**
 * Kernel class
 * Core of application, his role is
 * load container of services and return
 * a response.
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Kernel
{
    private Router $router;
    private Auth $auth;

    /**
     * Construct instance
     * @throws ReflectionException
     */
    public function __construct()
    {
        Container::loadServices();

        /**
         * @var Router $router
         */
        $router = Container::getService("router");
        $this->router = $router;

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");
        $this->auth = $auth;

    }

    /**
     * Return response about route call, if
     * the route doesn't exist then return 404 not found.
     *
     * @return Response
     *
     * @throws Factory\Router\RouterException
     */
    public function run(): Response
    {
        if ($this->auth->isAuthenticated() && $this->auth::$currentUser) {
            Twig::setCurrentUser($this->auth::$currentUser);
        }

        /**
         * @var array<int, callable> $dispatch
         */
        $dispatch = $this->router->dispatch();

        if ($dispatch) {
            /** @var Response $response */
            $response = call_user_func_array(...$dispatch);

            return $response;

        }

        return $this->router->httpNotFound();

    }


}
