<?php

/**
 * DotEnvTestScript file : set environment variable test
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

$_ENV["TEST_PATH"] = "_test";

require_once __DIR__."/DatabaseScript.php";
