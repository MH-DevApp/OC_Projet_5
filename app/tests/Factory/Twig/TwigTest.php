<?php

/**
 * TwigTest file
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

namespace tests\Factory\Twig;

use App\Factory\Twig\Twig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

/**
 * Test Twig cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Twig::class)]
class TwigTest extends TestCase
{


    /**
     * Test should be to instance Environment Twig
     * and get this Environment.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be to instance Environment Twig and get this Environment")]
    public function itInstanceAndGetEnvironment(): void
    {
        $twig = new Twig();
        $this->assertInstanceOf(Twig::class, $twig);
        $this->assertInstanceOf(Environment::class, $twig->getTwig());
    }
}
