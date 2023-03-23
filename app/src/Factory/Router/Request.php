<?php

/**
 * Request file
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
 * Request class
 * Manage all globals variables :
 * GET, POST, SERVER, FILES, ENV, COOKIE and has a getter
 * method to request method.
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Request
{
    private string $_method = "";
    /** @var array<string, string> $_get */
    private array $_get;
    /** @var array<string, string> $_env */
    private array $_env;
    /** @var array<string, string> $_post */
    private array $_post;
    /** @var array<string, array<string, string>> $_files */
    private array $_files;
    /** @var array<string, string> $_cookie */
    private array $_cookie;
    /** @var array<string, string> $_server */
    private array $_server;

    /**
     * Constructor :
     * Initialise all private variables with globals
     * variables
     */
    public function __construct()
    {
        $this->_method = $_SERVER['REQUEST_METHOD'] ?? "";
        $this->_post = $_POST;
        $this->_get = $_GET;
        $this->_env = $_ENV;
        $this->_files = $_FILES;
        $this->_cookie = $_COOKIE;
        $this->_server = $_SERVER;
    }

    /**
     * Get $_POST global variable
     *
     * @param string|null $key [Optional] name of key
     *                         to get value, return null
     *                         if it doesn't define
     *
     * @return array<string, string>|string|null
     */
    public function getPost(?string $key = null):array|string|null
    {
        if ($key) {
            return $this->_post[$key] ?? null;
        }
        return $this->_post;
    }

    /**
     * Get $_GET global variable
     *
     * @param string|null $key [Optional] name of key
     *                         to get value, return null
     *                         if it doesn't define
     *
     * @return array<string, string>|string|null
     */
    public function getGet(?string $key = null): array|string|null
    {
        if ($key) {
            return $this->_get[$key] ?? null;
        }
        return $this->_get;
    }

    /**
     * Get $_ENV global variable
     *
     * @param string|null $key [Optional] name of key
     *                         to get value, return null
     *                         if it doesn't define
     *
     * @return array<string, string>|string|null
     */
    public function getEnv(?string $key = null): array|string|null
    {
        if ($key) {
            return $this->_env[$key] ?? null;
        }
        return $this->_env;
    }

    /**
     * Get $_FILES global variable
     *
     * @return array<string, array<string, string>>|null
     */
    public function getFiles(): ?array
    {
        return $this->_files;
    }

    /**
     * Get $_SERVER global variable
     *
     * @param string|null $key [Optional] name of key
     *                         to get value, return null
     *                         if it doesn't define
     *
     * @return array<string, string>|string|null
     */
    public function getServer(?string $key = null): array|string|null
    {
        if ($key) {
            return $this->_server[$key] ?? null;
        }
        return $this->_server;
    }

    /**
     * Get request method ($_POST, $_GET, $_PUT,
     * $_DELETE, $_PATCH)
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->_method;
    }

    /**
     * Get $_COOKIE global variable
     *
     * @param string|null $key [Optional] name of key
     *                         to get value, return null
     *                         if it doesn't define
     *
     * @return array<string, string>|string|null
     */
    public function getCookie(?string $key = null): array|string|null
    {
        if ($key) {
            return $this->_cookie[$key] ?? null;
        }
        return $this->_cookie;
    }

    /**
     * Set a value to the key name in the cookie global variable
     *
     * @param string $key      Name of key cookie to wants to be set
     * @param string $value    Value linked to key
     * @param int    $expires  [Optional] timestamp expiration
     *                         default value = 0
     * @param bool   $secure   [Optional] setting ssl,
     *                         default value = false
     * @param bool   $httpOnly [Optional] setting http only,
     *                         default value = true

     * @return void
     */
    public function setCookie(
        string $key,
        string $value,
        int $expires = 0,
        bool $secure = false,
        bool $httpOnly = true
    ): void {
        setcookie($key, $value, $expires, "/", "", $secure, $httpOnly);
        if ($expires > time()) {
            $this->_cookie[$key] = $value;
        } else {
            $this->_cookie = array_filter(
                $this->_cookie, fn ($k) => $key !== $k,
                ARRAY_FILTER_USE_KEY
            );
        }
    }

    /**
     * Check if name of key is define in the cookie
     *
     * @param string $key name of key to check if define
     *
     * @return bool
     */
    public function hasCookie(string $key): bool
    {
        return array_key_exists($key, $this->_cookie);
    }

    /**
     * Get the request uri :
     * if REQUEST_URI key is defined and the ends uri is not 'cgi' then return
     * the value else if REQUEST_URI key is defined then return the value else
     * by default root path.
     *
     * @return string
     */
    public function getURI(): string
    {
        if (isset($this->_server['REQUEST_URI']) 
            && !str_ends_with($this->_server['REQUEST_URI'], 'cgi')
        ) {
            $result = $this->_server['REQUEST_URI'];
        } else {
            $result = $this->_server['REQUEST_URI'] ?? '/';
        }

        return $result;
    }
}
