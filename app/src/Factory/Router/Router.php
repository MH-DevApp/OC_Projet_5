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


use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use ReflectionClass;

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
     * @var array<string, Route> $namedRoutes
     */
    private array $namedRoutes = [];


    /**
     * Construct
     * @throws \ReflectionException
     */
    public function __construct()
    {
        /**
         * @var array<string, array<int, string>> $controllers
         */
        $controllers = yaml_parse_file(__DIR__."/../../../config/routes.yml");

        /**
         * @var class-string $controller
         */
        foreach ($controllers["controllers"] as $controller) {
            $reflectionController = new ReflectionClass($controller);

            foreach ($reflectionController->getMethods() as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    /**
                     * @var Route $route
                     */
                    $route = $attribute->newInstance();

                    $route->setControllerName($controller);
                    $route->setAction($method->getName());
                    $this->add($route);
                }
            }
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
            $this->namedRoutes[$route->getName()] = $route;
        }

    }


    /**
     * Search match route with URI, return a Response if true, else
     * return false.
     *
     * @return array<int, mixed>|false
     *
     * @throws RouterException
     */
    public function dispatch(): array|false
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

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
                return [
                    [
                        new ($route->getControllerName())(),
                        $route->getAction()
                    ],
                    $route->getParams()
                ];
            }
        }

        return false;

    }


}
