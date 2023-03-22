<?php

/**
 * DotEnvThrowExceptionWithoutEnvFileTest file
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
class DotEnvThrowExceptionWithoutEnvFileTest extends TestCase
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
    }

    /**
     * Test not file found exception
     *
     * @throws DotEnvException
     *
     * @return void
     */
    #[Test]
    #[TestDox(
        "Load DotEnv with filename not exist and 
        throw an exception file is required"
    )]
    public function itThrowDotEnvExceptionFileEnvNotExist(): void
    {
        $this->expectException(DotEnvException::class);
        $this->expectExceptionMessage(
            "DotEnvException : .env_test_exception file is required"
        );

        (new DotEnv())->load();

    }
}
