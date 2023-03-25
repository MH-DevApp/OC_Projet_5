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

use App\Factory\Router\Response;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;

require_once "../vendor/autoload.php";

try {
    (new DotEnv())->load();
    $dispatchRouter = (new Router())->dispatch();
    if ($dispatchRouter instanceof Response) {
        $dispatchRouter->send();
    } else {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
} catch (DotEnvException|RouterException $e) {
    print_r($e->getMessage());
}
