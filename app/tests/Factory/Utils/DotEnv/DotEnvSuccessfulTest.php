<?php

/**
 * DotEnvSuccessfulTest file
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

namespace tests\Factory\Utils\DotEnv;

use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test DotEnv successful cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(DotEnv::class)]
class DotEnvSuccessfulTest extends TestCase
{
    /**
     * Array of environment variables
     *
     * @var array<string, string> $_envParams
     */
    private array $_envParams = [];

    /**
     * Set TEST_PATH in environment variable $_ENV
     *
     * @return void
     */
    #[Before]
    public function initEnvironmentVariables(): void
    {
        $_ENV["TEST_PATH"] = "_test";

        $envParams = explode(
            PHP_EOL,
            file_get_contents(
                __DIR__."/../../../../.env_test"
            ) ?: ""
        );

        foreach ($envParams as $envParam) {
            $params = explode("=", $envParam, 2);
            [$key, $value] = [trim($params[0]), trim($params[1])];
            $this->_envParams[$key] = $value;
        }
    }

    /**
     * Tests all successful cases.
     *
     * @throws DotEnvException
     *
     * @return void
     */
    #[Test]
    #[TestDox(
        "Load DotEnv with successful Data and load all 
        environment variables required"
    )]
    public function itLoadDotenvWithSuccessfulData(): void
    {
        (new DotEnv())->load();

        foreach ($this->_envParams as $key => $param) {
            $this->assertEquals($param, $_ENV[$key]);
        }
    }
}
