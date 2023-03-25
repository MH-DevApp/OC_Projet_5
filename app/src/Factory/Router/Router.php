<?php

/**
 * Router file
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

namespace App\Factory\Router;


use App\Service\Container\ContainerInterface;

/**
 * Router class
 * Manage the routing application and contains
 * all routes
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Router implements ContainerInterface
{

    /**
     * @var array<string, array<int, Route>> $routes
     */
    private array $routes = [];


    /**
     * Construct
     */
    public function __construct()
    {

        $routes = [new Route("/", "app_home")];

        foreach ($routes as $route) {
            $this->add($route);
        }

    }


    /**
     * Add route in the routes list
     *
     * @param Route $route Route instance
     * @return void
     */
    private function add(Route $route): void
    {
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }

    }


    /**
     * Search match route with URI, return a Response if true, else
     * return false.
     *
     * @return Response|false
     *
     * @throws RouterException
     */
    public function dispatch(): Response|false
    {
        $request = new Request();

        /*
         * First time, check if the request method exist in the routes list.
         * If it doesn't exist, then throw a router exception, else
         * continue execution.
         */

        if (isset($this->routes[$request->getMethod()]) === false) {
            throw new RouterException("REQUEST_METHOD does not exist.");
        }

        foreach ($this->routes[$request->getMethod()] as $route) {
            if ($route->match($request->getURI()) === true) {
                return new Response("SUCCESS");
            }
        }

        return false;

    }


}
