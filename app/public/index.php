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

use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Kernel;

require_once "../vendor/autoload.php";

try {
    (new DotEnv())->load();
    (new Kernel())
        ->run()
        ->send()
    ;

} catch (DotEnvException|RouterException $e) {
    print_r($e->getMessage());

}
