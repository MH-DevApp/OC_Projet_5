<?php

/**
 * DotEnvScript : create default .env and .env_test files
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

use App\Factory\Utils\Uuid\UuidV4;
use Exception;

try {
    echo "# Creation of the '.env' file in the root of the app folder."
        .PHP_EOL.PHP_EOL;
    $default_data =  "APP_ENV=DEV".PHP_EOL;
    $default_data .= "DB_DNS=mysql:host=localhost:3306;dbname=db_name".PHP_EOL;
    $default_data .= "DB_USER=root".PHP_EOL;
    $default_data .= "DB_PWD=YourPassword";
    $default_data .= "SECRET_KEY=" . UuidV4::generate();
    file_put_contents(__DIR__."/../.env", $default_data, LOCK_EX);
    echo "# File .env created : it is necessary to modify the default values 
    with your data.".PHP_EOL.PHP_EOL;

    echo "# Creation of the '.env_test' file in the root of the app folder."
        .PHP_EOL.PHP_EOL;
    $default_data =  "APP_ENV=TEST".PHP_EOL;
    $default_data .= "DB_DNS=mysql:host=localhost:3306;dbname=db_name".PHP_EOL;
    $default_data .= "DB_USER=root".PHP_EOL;
    $default_data .= "DB_PWD=YourPassword";
    $default_data .= "SECRET_KEY=" . UuidV4::generate();
    file_put_contents(__DIR__."/../.env_test", $default_data, LOCK_EX);
    echo "# File .env_test created : it is necessary to modify the default values 
    with your data.".PHP_EOL.PHP_EOL;
} catch (Exception $e) {
    print_r($e);
}
