<?php

/**
 * Container file
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

namespace App\Service\Container;

use App\Factory\Router\Router;
use ReflectionClass;
use ReflectionException;

/**
 * Container class
 * Manage all containers of service for application :
 * - Request,
 * - Router,
 * - Manager
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class Container
{

    /**
     * @var array<string, array<string, ContainerInterface>> $containers
     */
    private static array $containers = [];


    /**
     * Load all services
     *
     * @throws ReflectionException
     */
    public static function loadServices(): void
    {
        $services = yaml_parse_file(__DIR__.'/../../../config/services.yml');

        foreach ($services["services"] as $key => $service) {
            $class = new ReflectionClass($service);
            static::$containers["services"][$key] = $class->newInstance();
        }

        static::$containers["services"]["router"] = new Router();

    }


    /**
     * Return a service with name
     *
     * @param string $name
     *
     * @return ?ContainerInterface
     */
    public static function getService(string $name): ?ContainerInterface
    {
        return static::$containers["services"][$name];

    }


    /**
     * Return container of services
     *
     * @return array<string, ContainerInterface>
     */
    public static function getServices(): array
    {
        return static::$containers["services"];

    }


}
