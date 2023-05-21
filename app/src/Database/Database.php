<?php

/**
 * Database file
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

namespace App\Database;

use App\Factory\Router\Request;
use App\Service\Container\Container;
use PDO;

/**
 * Database class
 * Make connexion with DB environment app and return
 * a PDO
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Database
{

    private string $dns;
    private string $user;
    private string $pwd;


    /**
     * Construct instance Database
     *
     * @throws DatabaseException
     */
    public function __construct()
    {
        $this->init();
    }


    /**
     * Initialise all data for connect to the
     * database.
     *
     * @return void
     *
     * @throws DatabaseException
     */
    private function init(): void
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /** @var string $dns */
        $dns = $request->getEnv("DB_DNS") ?? "";

        /** @var string $user */
        $user = $request->getEnv("DB_USER") ?? "";

        /** @var string $pwd */
        $pwd = $request->getEnv("DB_PWD") ?? "";

        /** @var string $env */
        $env = $request->getEnv("APP_ENV") ?? "";

        $this->dns = ($dns."_". strtolower($env));

        $dbname = explode("dbname=", $this->dns)[1];

        if (!$dbname) {
            throw new DatabaseException(
                "The database name doesn't define, check the ".
                ".env file"
            );
        }

        $this->user = $user;
        $this->pwd = $pwd;
    }


    /**
     * Connect to Database
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        return new PDO($this->dns, $this->user, $this->pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
