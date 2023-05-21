<?php

/**
 * UuidTest file
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

namespace tests\Factory\Utils\Uuid;

use App\Factory\Utils\Uuid\UuidV4;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Uuid cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(UuidV4::class)]
class UuidTest extends TestCase
{


    /**
     * Test should be to generate a unique string id with V4.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to generate a unique string id with V4")]
    public function itGenerateUuidV4(): void
    {
        $id = UuidV4::generate();
        $idParts = explode("-", $id);

        $this->assertIsString($id);
        $this->assertEquals(36, strlen($id));
        $this->assertCount(5, $idParts);

        foreach ($idParts as $idPart) {
            $this->assertMatchesRegularExpression("#[a-z\d]+#", $idPart);
        }
    }
}
