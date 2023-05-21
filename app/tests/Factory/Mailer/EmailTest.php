<?php

/**
 * EmailTest file
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

namespace tests\Factory\Mailer;

use App\Factory\Mailer\Email;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Email cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Email::class)]
class EmailTest extends TestCase
{


    /**
     * Test should be to create Email object.
     *
     * @return void
     */
    #[Test]
    #[TestDox("should be to create Email object")]
    public function itCreateEmailObject(): void
    {
        $email = new Email();
        $email->setBody("Test");
        $email->setSubject("Test");

        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals("contact@p5-daps-oc.fr", $email->getFrom());
        $this->assertEquals("contact@p5-daps-oc.fr", $email->getTo());
        $this->assertEquals("Test", $email->getSubject());
        $this->assertEquals("Test", $email->getBody());

        $email->setFrom("test@test.com");
        $email->setTo("test@test.com");

        $this->assertEquals("test@test.com", $email->getFrom());
        $this->assertEquals("test@test.com", $email->getTo());
    }
}
