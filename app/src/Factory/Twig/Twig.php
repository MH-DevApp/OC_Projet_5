<?php

/**
 * Twig file
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

namespace App\Factory\Twig;


use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Twig class
 *
 * Instance Environment Twig.
 * Add some functions and globals properties to template twig.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Twig
{
    private Environment $twig;


    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__."/../../../templates");
        $this->twig = new Environment(
            $loader, [
                "cache" => false,
                "debug" => true,
                "strict_variables" => true
            ]
        );
        $this->twig->addExtension(new DebugExtension());

    }


    /**
     * Get environment twig of the application to
     * render templates html.
     *
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;

    }


}
