<?php

/**
 * Csrf file
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

namespace App\Factory\Utils\Csrf;

use App\Factory\Router\Request;
use App\Service\Container\Container;

/**
 * Csrf class
 *
 * Generate a unique string id in format UuidV4 (36 chars)
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class Csrf
{


    /**
     * Generate a unique token and sign it with hash_hmac function
     *
     * @param string $key
     *
     * @return string|false
     */
    public static function generateTokenCsrf(string $key): string|false
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var string $secretKey
         */
        $secretKey = $request->getEnv("SECRET_KEY");

        /**
         * @var string $remoteAddr
         */
        $remoteAddr = $request->getServer("REMOTE_ADDR");

        /**
         * @var string $httpUserAgent
         */
        $httpUserAgent = $request->getServer("HTTP_USER_AGENT");

        if (!$secretKey ||
            !$remoteAddr ||
            !$httpUserAgent
        ) {
            return false;
        }

        return hash_hmac(
            'sha256',
            $key,
            $secretKey.
            $remoteAddr.
            $httpUserAgent
        );
    }


    /**
     * Check if the token in param is valid, return true if valid
     * or false is not valid.
     *
     * @param string $token
     * @param string $key
     *
     * @return bool
     */
    public static function isTokenCsrfValid(
        string $token,
        string $key
    ): bool {
        $tokenKey = self::generateTokenCsrf($key);

        if ($tokenKey) {
            return hash_equals(
                $tokenKey,
                $token
            );
        }

        return false;
    }
}
