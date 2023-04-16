<?php

/**
 * KernelTest file
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

namespace tests;


use App\Factory\Router\Request;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Kernel;
use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Kernel cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Kernel::class)]
class KernelTest extends TestCase
{

    /**
     * Initialise environment of test
     *
     * @return void
     */
    #[Before]
    public function init(): void
    {
        $_ENV["TEST_PATH"] = "_test";
    }


    /**
     * Test should be got a response Ok from router.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be got a response Ok from router")]
    public function itGetResponseOkFromRouter(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        (new DotEnv())->load();
        $response = (new Kernel())->run();

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString("SUCCESS", $content);

    }


    /**
     * Test should be got a response Not Found from router.
     *
     * @return void
     *
     * @throws RouterException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be got a response Not Found from router")]
    public function itGetResponseNotFoundFromRouter(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/bad-route";

        (new DotEnv())->load();
        $response = (new Kernel())->run();
        $response->send();

        $this->assertEquals(404, http_response_code());

    }


}
