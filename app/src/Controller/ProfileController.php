<?php

/**
 * ProfileController file
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

use App\Factory\Form\ProfileResetPasswordForm;
use App\Factory\Mailer\Email;
use App\Factory\Mailer\Mailer;
use App\Factory\Manager\Manager;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Service\Container\Container;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * ProfileController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class ProfileController extends AbstractController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

    }


    /**
     * Show profile user
     *
     * @return Response
     *
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws Exception
     */
    #[Route(
        "/profile",
        "app_profile",
        methods: ["GET", "POST"],
        granted: "ROLE_USER",
    )]
    public function index(): Response
    {
        $form = new ProfileResetPasswordForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->user()) {
                /**
                 * @var string $password
                 */
                $password = $form->getData("newResetPassword");

                $this->user()->setPassword(password_hash($password, PASSWORD_ARGON2ID));

                /**
                 * @var Manager $manager
                 */
                $manager = Container::getService("manager");
                $manager->flush($this->user());

                /**
                 * @var string $userEmail
                 */
                $userEmail = $this->user()->getEmail();

                // Send email
                $email = (new Email())
                    ->setSubject("[P5] P5 DAPS BLOG - Votre mot de passe a été modifié !")
                    ->setTo($userEmail)
                    ->setBodyTwig(
                        "emails/reset-password.html.twig",
                        [
                            "firstname" => $this->user()->getFirstname()
                        ]
                    );
                (new Mailer())->send($email);

                return $this->render("profile/reset-password-success.html.twig");
            }
        }

        return $this->render("profile/index.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);

    }


}
