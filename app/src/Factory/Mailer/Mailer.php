<?php

/**
 * Mailer file
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

namespace App\Factory\Mailer;

use App\Factory\Router\Request;
use App\Service\Container\Container;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mailer class
 *
 * Connexion to a server SMTP.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Mailer
{

    private PHPMailer $serverSMTP;


    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        /**
         * @var string $host
         */
        $host = $request->getEnv("MAILER_HOST");
        $port = (int)$request->getEnv("MAILER_PORT");

        $this->serverSMTP = new PHPMailer(true);
        $this->serverSMTP->isSMTP();
        $this->serverSMTP->CharSet = "UTF-8";
        $this->serverSMTP->Host = $host;
        $this->serverSMTP->Port = $port;
    }


    /**
     * Send Email
     *
     * @throws Exception
     */
    public function send(Email $email): bool
    {

        try {
            $this->serverSMTP->setFrom($email->getFrom(), "Mailer");
            $this->serverSMTP->addAddress($email->getTo());
            $this->serverSMTP->isHTML();
            $this->serverSMTP->Subject = $email->getSubject();
            $this->serverSMTP->Body = $email->getBody();
            $this->serverSMTP->send();

            return true;
        } catch (Exception $exception) {
            throw new Exception("Email could not be send, try again.");
        }
    }
}
