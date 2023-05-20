<?php

/**
 * RouterRequestTest file
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

use App\Factory\Router\Request;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Router Request cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Request::class)]
class RouterRequestTest extends TestCase
{
    /**
     * Function load before tests
     *
     * @return void
     */
    #[Before]
    public function initEnvironmentVariables(): void
    {
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_ENV = [];
        $_FILES = [];
    }

    /**
     * Test $_POST global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test \$_POST global variable with Request class.")]
    public function itPostGlobalVariableTest(): void
    {
        $_POST["name"] = "Test";
        $request = new Request();

        $this->assertEquals("Test", $request->getPost("name"));
        $this->assertIsArray($request->getPost());
        $this->assertNull($request->getPost("NotParamExist"));
        $this->assertEquals(["name" => "Test"], $request->getPost());
    }

    /**
     * Test $_GET global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test \$_GET global variable with Request class.")]
    public function itGetGlobalVariableTest(): void
    {
        $_GET["name"] = "Test";
        $request = new Request();

        $this->assertEquals("Test", $request->getGet("name"));
        $this->assertIsArray($request->getGet());
        $this->assertNull($request->getGet("NotParamExist"));
        $this->assertEquals(["name" => "Test"], $request->getGet());
    }

    /**
     * Test all cases $_COOKIE global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test all cases \$_COOKIE global variable with Request class.")]
    public function itCookieGlobalVariableTest(): void
    {
        $request = new Request();

        // Create cookie and check if exist
        $request->setCookie("name", "Test", time() + 3600);
        $this->assertTrue($request->hasCookie("name"));

        // Get cookie and check if value equals
        $this->assertEquals("Test", $request->getCookie("name"));
        $this->assertIsArray($request->getCookie());
        $this->assertEquals(["name" => "Test"], $request->getCookie());

        // Get null value
        $this->assertNull($request->getCookie("NotParamExist"));

        // Delete cookie
        $request->setCookie("name", "", time() - 1);
        $this->assertFalse($request->hasCookie("name"));
    }

    /**
     * Test $_ENV global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test \$_ENV global variable with Request class.")]
    public function itEnvGlobalVariableTest(): void
    {
        $_ENV = ["APP_ENV" => "TEST"];
        $request = new Request();

        $this->assertEquals("TEST", $request->getEnv("APP_ENV"));
        $this->assertIsArray($request->getEnv());
        $this->assertNull($request->getEnv("NotParamExist"));
        $this->assertEquals(["APP_ENV" => "TEST"], $request->getEnv());
    }

    /**
     * Test $_FILES global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test \$_FILES global variable with Request class.")]
    public function itFilesGlobalVariableTest(): void
    {
        // Init simulate file
        $files["file"] = [
            "name"     => "test.txt",
            "tmp_name" => "tmp_test",
            "size"     => "10000",
            "type"     => "test",
            "error"    => "0"
        ];

        $_FILES = $files;

        $request = new Request();

        $this->assertEquals($files, $request->getFiles());
        $this->assertIsArray($request->getFiles());
        $this->assertIsArray($request->getFiles()["file"]);
    }

    /**
     * Test $_SERVER global variable with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test \$_SERVER global variable with Request class.")]
    public function itServerGlobalVariableTest(): void
    {
        $_SERVER = [];
        $_SERVER["REQUEST_METHOD"] = "POST";
        $request = new Request();

        $this->assertEquals(["REQUEST_METHOD" => "POST"], $request->getServer());
        $this->assertIsArray($request->getServer());
        $this->assertEquals("POST", $request->getServer("REQUEST_METHOD"));
        $this->assertNull($request->getServer("NotParamExist"));
    }

    /**
     * Test getMethod() request with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test getMethod() request with Request class.")]
    public function itGetMethodTest(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $request = new Request();

        $this->assertEquals("POST", $request->getMethod());
    }

    /**
     * Test getURI() method with Request class.
     *
     * @return void
     */
    #[Test]
    #[TestDox("test getURI() method with Request class.")]
    public function itGetURITest(): void
    {
        $_SERVER["REQUEST_URI"] = "/test";
        $request = new Request();

        $this->assertEquals("/test", $request->getURI());

        $_SERVER = [];
        $request = new Request();

        $this->assertEquals("/", $request->getURI());
    }

    /**
     * Function load after tests
     *
     * @return void
     */
    #[After]
    public function endTest(): void
    {
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_ENV = [];
        $_FILES = [];
    }
}
