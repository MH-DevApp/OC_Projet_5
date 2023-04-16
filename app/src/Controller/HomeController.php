<?php

/**
 * HomeController file
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

namespace App\Controller;


use App\Factory\Form\ContactForm;
use App\Factory\Mailer\Email;
use App\Factory\Mailer\Mailer;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use PHPMailer\PHPMailer\Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * HomeController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class HomeController extends AbstractController
{


    /**
     * Index page of home controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     * @throws Exception
     */
    #[Route("/", "app_home", methods: ["GET", "POST"])]
    public function index(): Response
    {
        $form = new ContactForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            // Send email

            /**
             * @var string $subject
             */
            $subject = $form->getData("subject");

            $email = (new Email())
                ->setSubject($subject)
                ->setBody(
                    "<h1 style='text-decoration: underline;'>Formulaire de contact</h1>".
                    "<p>Email: ".$form->getData("email")."</p>".
                    "<p>Subject: ".$form->getData("subject")."</p>".
                    "<p>Message: ".$form->getData("message")."</p>"
                );
            (new Mailer())->send($email);
        }

        return $this->render("home/index.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors()
        ]);
    }


}
