<?php

/**
 * MailerTest file
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
use App\Factory\Mailer\Mailer;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;
use PHPMailer\PHPMailer\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test Mailer cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Mailer::class)]
class MailerTest extends TestCase
{


    /**
     * Test should be to send email successfully with Mailer class.
     *
     * @return void
     *
     * @throws DotEnvException|ReflectionException|Exception
     */
    #[Test]
    #[TestDox("should be to send email successfully with Mailer class")]
    public function itSendEmailSuccessfully(): void
    {
        $_SERVER["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        Container::loadServices();

        $email = new Email();
        $email->setSubject("[TEST] Test cases !");
        $email->setBody("This is a test !");

        $mailer = new Mailer();
        $statusMailer = $mailer->send($email);

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertTrue($statusMailer);

    }


    /**
     * Test should be to send email failed with Mailer class.
     *
     * @return void
     *
     * @throws DotEnvException|ReflectionException|Exception
     */
    #[Test]
    #[TestDox("should be to send email failed with Mailer class")]
    public function itSendEmailFailed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Email could not be send, try again.");

        $_SERVER["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        $_ENV["MAILER_HOST"] = "test";
        Container::loadServices();

        $email = new Email();
        $email->setSubject("[TEST] Test cases !");
        $email->setBody("This is a test !");

        $mailer = new Mailer();
        $mailer->send($email);

    }


}
