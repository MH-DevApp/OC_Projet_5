<?php

/**
 * Application startup file
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

use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;

require_once "../vendor/autoload.php";

try {
    (new DotEnv())->load();
    $container = new Container();

    /**
     * @var Router $router
     */
    $router = Container::$containers["services"]["router"];
    $router->dispatch();

} catch (DotEnvException|RouterException $e) {
    print_r($e->getMessage());

}
