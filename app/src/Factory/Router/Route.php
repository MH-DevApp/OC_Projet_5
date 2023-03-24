<?php

/**
 * Route file
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
class Route
{

    /**
     * @var array<int, string|int> $params
     */
    private array $params = [];


    /**
     * Construct
     *
     * @param string                $path    Path route (for example /posts or
                                    /posts/:id with params)
     * @param string                $name    Name of route (for example app_posts)
     * @param array<string, string> $regexs  [Optional] array of regexs for params
     * @param array<int, string>    $methods [Optional] array of methods, by default GET
     */
    public function __construct(
        private string $path,
        private readonly string $name,
        private array $regexs=[],
        private readonly array $methods=["GET"]
    ) {
        $this->path = trim($this->path, "/");

        // Replace to remove bracket open
        foreach ($this->regexs as $key => $value) {
            $this->regexs[$key] = str_replace('(', '(?:', $value);
        }

    }


    /**
     * Math all params between URL and path.
     * If match then return true else false.
     *
     * @param string $url
     *
     * @return bool
     */
    public function match(string $url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback(
            '#:(\w+)#',
            [
                $this,
                'paramMatch'
            ],
            $this->path
        );
        $regex = "#^$path$#i";

        /*
         * Return false if no match with regex path else
         * get all params of the url matches with regex path
         */
        if (preg_match($regex, $url, $matches) === false) {
            return false;
        }

        // Remove first item and keep matches word
        array_shift($matches);
        $this->params = $matches;

        return true;

    }


    /**
     * Get regex if defined else return default
     * regex value
     *
     * @param array<int, string> $match Param in path
     *
     * @return string
     */
    private function paramMatch(array $match): string
    {
        if (isset($this->regexs[$match[1]]) === true) {
            return '('.$this->regexs[$match[1]].')';

        }

        // Return default regex value
        return '([^/]+)';

    }


    /**
     * Get path of the route
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;

    }


    /**
     * Get name of the route
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;

    }


    /**
     * Get methods of the route
     * GET, POST
     *
     * @return array<int, string>
     */
    public function getMethods(): array
    {
        return $this->methods;

    }


    /**
     * Get params of the route
     *
     * @return array<int, string|int>
     */
    public function getParams(): array
    {
        return $this->params;

    }


}
