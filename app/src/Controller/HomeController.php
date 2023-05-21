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
use App\Factory\Router\RouterException;
use App\Repository\PostRepository;
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
     * @throws RouterException
     */
    #[Route("/", "app_home", methods: ["GET", "POST"])]
    public function index(): Response
    {
        $form = new ContactForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            // Send email
            $email = (new Email())
                ->setSubject("[P5] Formulaire de contact")
                ->setBodyTwig("emails/contact-form.html.twig", [
                    "email" => $form->getData("email"),
                    "subject" => $form->getData("subject"),
                    "message" => $form->getData("message")
                    ]);
            (new Mailer())->send($email);

            return $this->redirectTo("app_home");
        }

        $postsFeatured = (new PostRepository())->getFeaturedPostsWithUser();

        return $this->render("home/index.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted(),
            "postsFeatured" => $postsFeatured
        ]);
    }
}
