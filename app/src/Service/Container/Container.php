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
class Container
{

    /**
     * @var array<string, ContainerInterface> $containers
     */
    public static array $containers = [];


    /**
     * Construct
     *
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->loadServices();
    }


    /**
     * Load all services
     *
     * @throws ReflectionException
     */
    private function loadServices(): void
    {
        $services = yaml_parse_file(__DIR__.'/../../../config/services.yml');

        foreach ($services["services"] as $key => $service) {
            $class = new ReflectionClass($service);
            self::$containers["services"][$key] = $class->newInstance();
        }

        self::$containers["services"]["router"] = new Router();
    }


}
