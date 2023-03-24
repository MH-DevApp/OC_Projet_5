<?php

/**
 * RouterResponseTest file
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Router Response cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Response::class)]
class RouterResponseTest extends TestCase
{
    /**
     * Test should be get response ok with html/text.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get response ok with html/text")]
    public function itGetResponseOkHtmlText(): void
    {
        $response = new Response("<h1>Test</h1>");

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_end_clean();

        $this->assertStringContainsString("Test", $content);
        $this->assertEquals(200, http_response_code());

    }


    /**
     * Test should be get response not found.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get response not found")]
    public function itGetResponseNotFound(): void
    {
        $response = new Response("Not Found", 404);

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_end_clean();

        $this->assertStringContainsString("Not Found", $content);
        $this->assertEquals(404, http_response_code());

    }


    /**
     * Test should be get response ok with json content.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be get response ok with json content")]
    public function itGetResponseOkWithJson(): void
    {
        $response = new Response(
            json_encode(["name" => "test", "id" => 123]) ?: "",
            200,
            ["Content-Type: application/json"]
        );

        ob_start();
        $response->send();
        $content = ob_get_contents() ?: "";
        ob_end_clean();

        $this->assertStringContainsString("{\"name\":\"test\",\"id\":123}", $content);
        $this->assertEquals(200, http_response_code());

    }

}
