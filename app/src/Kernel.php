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

use App\Factory\Router\Response;
use App\Factory\Router\Router;
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

    /**
     * Construct instance
     * @throws ReflectionException
     */
    public function __construct()
    {
        Container::loadServices();

        /** @var Router router */
        $router = Container::getService("router");

        if ($router) {
            $this->router = $router;
        }

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
        $dispatch = $this->router->dispatch();

        if ($dispatch) {
            return $dispatch;
        }

        header("HTTP/1.0 404 Not Found");
        return new Response("", 404);

    }


}
