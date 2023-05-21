<?php

/**
 * DotEnvThrowExceptionWithoutParams file
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
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Exception DotEnv
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
class DotEnvThrowExceptionWithoutParams extends TestCase
{
    /**
     * Init environment of test
     *
     * @return void
     */
    #[Before]
    public function initEnvironment(): void
    {
        $_ENV = [];
        $_ENV["TEST_PATH"] = "_test_exception";
        file_put_contents(__DIR__."/../../../../.env_test_exception", "", LOCK_EX);
    }

    /**
     * Load DotEnv without params in env file
     *
     * @throws DotEnvException
     *
     * @return void
     */
    #[Test]
    #[TestDox("Load DotEnv without params in env file")]
    public function itThrowDotEnvExceptionWithoutParams(): void
    {

        $this->expectException(DotEnvException::class);
        $this->expectExceptionMessage(
            "DotEnvException : APP_ENV is required in .env file."
        );

        (new DotEnv())->load();
    }

    /**
     * Delete file .env_test_exception after running this test
     *
     * @return void
     */
    #[After]
    public function terminateTest(): void
    {
        if (file_exists(__DIR__."/../../../../.env_test_exception")) {
            unlink(__DIR__."/../../../../.env_test_exception");
        }
    }
}
