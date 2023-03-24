<?php

/**
 * RouterRouteTest file
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


use App\Factory\Router\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Router Route cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Route::class)]
class RouterRouteTest extends TestCase
{


    /**
     * Test should be get match simple route.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get match simple route")]
    public function itMatchSimpleRoute(): void
    {
        $route = new Route(
            "/test",
            "app_test"
        );

        $this->assertTrue($route->match("/test"));
        $this->assertEquals("test", $route->getPath());
        $this->assertEquals(["GET"], $route->getMethods());
        $this->assertEquals("app_test", $route->getName());
        $this->assertEquals([], $route->getParams());

    }


    /**
     * Test should be get match complex route with default
     * value regex.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get match complex route with default value regex")]
    public function itMatchComplexRouteWithDefaultRegex(): void
    {
        $route = new Route(
            "/test/:id/:slug",
            "app_test_id_and_slug"
        );

        $this->assertTrue($route->match("/test/1/hello"));
        $this->assertFalse($route->match("/test/1"));
        $this->assertEquals("test/:id/:slug", $route->getPath());
        $this->assertEquals(["GET"], $route->getMethods());
        $this->assertEquals("app_test_id_and_slug", $route->getName());
        $this->assertEquals(["1", "hello"], $route->getParams());

    }


    /**
     * Test should be get match complex route with custom
     * value regex.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get match complex route with custom value regex")]
    public function itMatchComplexRouteWithCustomRegex(): void
    {
        $route = new Route(
            "/test/:id/:slug",
            "app_test_id_and_slug",
            ["id" => "[0-9]+", "slug" => "([a-z\-0-9])+"],
            ["GET", "POST"]

        );

        $this->assertTrue($route->match("/test/1/hello"));
        $this->assertFalse($route->match("/test/hello/1"));
        $this->assertEquals("test/:id/:slug", $route->getPath());
        $this->assertEquals(["GET", "POST"], $route->getMethods());
        $this->assertEquals("app_test_id_and_slug", $route->getName());
        $this->assertEquals(["1", "hello"], $route->getParams());

    }

}
