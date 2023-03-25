<?php

/**
 * ContainerTest file
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


use App\Factory\Router\Request;
use App\Factory\Router\Router;
use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Container cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{


    /**
     * Test should be loaded all service in container of services.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be loaded all service in container of services")]
    public function itLoadAllServices(): void
    {
        $container = new Container();

        foreach (Container::$containers["services"] as $service)
        {
            $this->assertInstanceOf(ContainerInterface::class, $service);
        }

        $this->assertInstanceOf(Request::class, Container::$containers["services"]["request"]);
        $this->assertInstanceOf(Router::class, Container::$containers["services"]["router"]);

    }


}
