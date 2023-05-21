<?php

/**
 * Utils DotEnv, load .env file and set the environment variables.*
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */

declare(strict_types=1);

namespace App\Factory\Utils\DotEnv;

/**
 * DotEnv class
 *
 * __construct sets required environment variables for the application,
 * throws an exception if they are not all defined.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class DotEnv
{
    /**
     * Array of environment variables required
     *
     * @var array<int, string> $_required
     */
    private array $_required;

    /**
     * Init the environment variables required
     */
    public function __construct()
    {
        $this->_required = [
            "APP_ENV",
            "DB_DNS",
            "DB_USER",
            "DB_PWD",
            "SECRET_KEY"
        ];
    }

    /**
     * Get contents in .env file and set global environment variable $_ENV.
     * Check if the environment variables are defined, else throw an
     * DotEnv exception.
     *
     * @throws DotEnvException
     *
     * @return void
     */
    public function load(): void
    {
        // for environment of test
        $appEnv = $_ENV["TEST_PATH"] ?? "";

        // check if file exists else throw an exception
        if (!file_exists(
            __DIR__."/../../../../.env"
            .$appEnv
        )
        ) {
            throw new DotEnvException(
                "DotEnvException : .env"
                .$appEnv
                ." file is required"
            );
        }

        // get all rows of params in .env file and explode it in array
        $envParams = explode(
            PHP_EOL,
            file_get_contents(
                __DIR__."/../../../../.env"
                .$appEnv
            ) ?: ""
        );

        /* get key and value on row of params and set global environment
        variable $_ENV to key with value */
        foreach ($envParams as $envParam) {
            $params = explode("=", $envParam, 2);
            [$key, $value] = [trim($params[0] ?? ""), trim($params[1] ?? "")];
            $_ENV[$key] = $value;
        }

        // check if all required params has defined
        $this->checkRequired();
    }

    /**
     * Check if the environment variables are defined, else throw an
     * DotEnv Exception.
     *
     * @throws DotEnvException
     *
     * @return void
     */
    public function checkRequired(): void
    {
        foreach ($this->_required as $paramRequired) {
            if (!$_ENV[$paramRequired]) {
                throw new DotEnvException(
                    "DotEnvException : "
                    .$paramRequired
                    ." is required in .env file."
                );
            }
        }
    }
}
