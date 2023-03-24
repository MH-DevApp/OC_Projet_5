<?php

/**
 * Response file
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

namespace App\Factory\Router;

/**
 * Response class
 * Returns a response text with Header and status-code
 * to the client
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Response
{


    /**
     * Construct
     *
     * @param string             $content [Optional] Text to render
     * @param int                $status  [Optional] Status code to response
     * @param array<int, string> $headers [Optional] headers to response
     */
    public function __construct(
        private readonly string $content="",
        private readonly int $status=200,
        private readonly array $headers=['Content-Type: text/html; charset=utf-8']
    ) {
        http_response_code($this->status);

    }


    /**
     * Function send test to render client
     *
     * @return void
     */
    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }

        echo htmlspecialchars($this->content);

    }


}
