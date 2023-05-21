<?php

/**
 * DatabaseTest file
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

namespace tests\Database;

use App\Database\Database;
use App\Database\DatabaseException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Database cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Database::class)]
class DatabaseTest extends TestCase
{

    private PDO $pdo;


    /**
     * Test should be to connect to database test successful.
     *
     * @return void
     *
     * @throws ReflectionException|DotEnvException
     */
    #[Test]
    #[TestDox("should be to connect to database test successful")]
    public function itConnectDatabaseSuccessful(): void
    {
        $_ENV["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        Container::loadServices();

        $this->pdo = (new Database())->connect();

        $this->assertNotNull($this->pdo);
    }


    /**
     * Test should be to connect to database test DBName not
     * define, catch exception.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to connect to database test failed")]
    public function itConnectDatabaseFailed(): void
    {
        $this->expectException(PDOException::class);

        $_ENV["DB_DNS"] = "mysql:host=127.0.0.1:3001;dbname=test";
        Container::loadServices();
        $this->pdo = (new Database())->connect();
    }


    /**
     * Test should be to connect to database test DBName not
     * define, catch exception.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to connect to database test DBName not define, catch exception")]
    public function itConnectDatabaseFailedDBNameNotDefine(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(
            "The database name doesn't define, check the ".
            ".env file"
        );

        $_ENV["DB_DNS"] = "mysql:host=127.0.0.1:3001;";
        Container::loadServices();
        new Database();
    }
}
