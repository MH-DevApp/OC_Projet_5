<?php

/**
 * DotEnvDockerScript file : set environment variable docker
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

namespace scripts;

$_ENV["ENV_MODE"] = "docker";

require_once __DIR__."/DotEnvScript.php";
