<?php

/**
 * RouterTest file
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

namespace tests\Factory\Router;

use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Factory\Router\Router;
use App\Factory\Router\RouterException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Router cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Router::class)]
#[CoversClass(Route::class)]
class RouterTest extends TestCase
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
     * Test should be get Response success value with the dispatch
     * of the router.
     *
     * @return void
     *
     * @throws RouterException
     * @throws ReflectionException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be get Response SUCCESS value with the dispatch of the router")]
    public function itGetResponseSuccessWithDispatchRouter(): void
    {
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        $_SERVER["REMOTE_ADDR"] = "Test";
        (new DotEnv())->load();

        Container::loadServices();
        $router = new Router();
        /**
         * @var array<int, callable> $dispatch
         */
        $dispatch = $router->dispatch();

        $this->assertIsArray($dispatch);

        ob_start();

        /**
         * @var Response $response
         */
        $response = call_user_func_array(...$dispatch);
        $response->send();
        $obContent = ob_get_contents() ?: "";
        ob_end_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString("<title>P5 DAPS BLOG - Homepage</title>", $obContent);
    }


    /**
     * Test should be get Response failed with the dispatch
     * of the router.
     *
     * @return void
     * @throws RouterException
     * @throws ReflectionException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be get Response FAILED with the dispatch of the router")]
    public function itGetResponseFailedWithDispatchRouter(): void
    {
        $_SERVER["REQUEST_URI"] = "/bad-route";
        $_SERVER["REQUEST_METHOD"] = "GET";
        (new DotEnv())->load();

        Container::loadServices();
        $router = new Router();
        $dispatch = $router->dispatch();

        if (!$dispatch) {
            header("HTTP/1.0 404 Not Found");
        }

        $this->assertFalse($dispatch);
        $this->assertEquals(404, http_response_code());
    }


    /**
     * Test should be get router exception because set
     * request method not exist.
     *
     * @return void
     * @throws RouterException
     * @throws ReflectionException
     * @throws DotEnvException
     */
    #[Test]
    #[TestDox("should be get router exception because set request method not exist")]
    public function itGetRouterExceptionRequestNotExist(): void
    {
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["REQUEST_METHOD"] = "PUT";
        (new DotEnv())->load();

        Container::loadServices();
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("REQUEST_METHOD does not exist.");

        $router = new Router();
        $dispatch = $router->dispatch();

        $this->assertFalse($dispatch);
        $this->assertEquals(500, http_response_code());
    }
}
