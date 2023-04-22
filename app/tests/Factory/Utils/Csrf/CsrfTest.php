<?php

/**
 * CsrfTest file
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

namespace tests\Factory\Utils\Csrf;


use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Service\Container\Container;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Csrf cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Csrf::class)]
class CsrfTest extends TestCase
{


    /**
     * Test should be to generate a unique token csrf and
     * check is valid.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to generate a unique token csrf and check is valid")]
    public function itGenerateCsrfAndCheckIsValid(): void
    {
        $_SERVER = [];

        (new DotEnv())->load();
        Container::loadServices();
        /**
         * @var string $token
         */
        $token = Csrf::generateTokenCsrf("Test");

        $this->assertFalse($token);
        $this->assertFalse(Csrf::isTokenCsrfValid($token ?: "", "Test"));

        $_SERVER["REMOTE_ADDR"] = "TEST";
        $_SERVER["HTTP_USER_AGENT"] = "TEST";

        (new DotEnv())->load();
        Container::loadServices();

        $token = Csrf::generateTokenCsrf("Test");

        $this->assertIsString($token);
        $this->assertTrue(Csrf::isTokenCsrfValid($token, "Test"));
        $this->assertFalse(Csrf::isTokenCsrfValid($token, "test"));

    }


}
