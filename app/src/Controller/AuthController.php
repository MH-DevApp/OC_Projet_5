<?php

/**
 * AuthController file
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


use App\Auth\Auth;
use App\Entity\User;
use App\Factory\Form\ConnexionForm;
use App\Factory\Form\RegisterForm;
use App\Factory\Mailer\Email;
use App\Factory\Mailer\Mailer;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Factory\Router\RouterException;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * AuthController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class AuthController extends AbstractController
{
    /**
     * Connexion page of auth controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     * @throws Exception
     */
    #[Route("/auth/login", "app_auth_login", methods: ["GET", "POST"])]
    public function login(): Response
    {
        if (Auth::$currentUser) {
            return $this->redirectTo("app_home");
        }

        /**
         * @var Auth $auth
         */
        $auth = Container::getService("auth");

        $form = new ConnexionForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var array<string, string> $data
             */
            $data = $form->getData();

            if ($auth->authenticate($data)) {
                return $this->redirectTo("app_home");
            }

            $form->setError(
                "global",
                "L'Email et/ou le mot de passe sont incorrects."
            );
        }

        return $this->render("auth/connexion.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);

    }


    /**
     * Register page of auth controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     * @throws Exception
     */
    #[Route("/auth/register", "app_auth_register", methods: ["GET", "POST"])]
    public function register(): Response
    {
        if (Auth::$currentUser) {
            return $this->redirectTo("app_home");
        }

        $user = new User();

        $form = new RegisterForm($user);
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {

            $tokenEmail = UuidV4::generate();

            /**
             * @var string $password
             */
            $password = $user->getPassword();

            $user->setPassword(
                password_hash(
                    $password,
                    PASSWORD_ARGON2ID
                )
            );

            $user->setEmailValidateToken(Csrf::generateTokenCsrf($tokenEmail));
            $user->setExpiredEmailTokenAt();

            /**
             * @var Manager $manager
             */
            $manager = Container::getService("manager");
            $manager->flush($user);

            $urlValidEmail = $this->generateUrl(
                "app_auth_valid_email",
                ["token" => $tokenEmail],
                true
            );

            // Send email
            $email = (new Email())
                ->setSubject("[P5] Bienvenue sur P5 DAPS BLOG - Veuillez confirmer votre adresse email")
                ->setTo($user->getEmail())
                ->setBody(
                    "<p>Bonjour ".$user->getFirstname().", <br><br>".
                        "Je vous souhaite la bienvenue sur P5 DAPS BLOG! <br><br>".
                        "Pour pouvoir accéder à toutes les fonctionnalités de mon blog, y compris la publication ".
                        "de commentaires, nous avons besoin de confirmer votre adresse email. <br /><br/>".
                        "Pour confirmer votre adresse email et activer votre compte, veuillez cliquer sur le lien ".
                        "ci-dessous (Valide pendant une durée de 5 minutes) :<br>".
                        "<a href=\"".$urlValidEmail."\">$urlValidEmail</a><br><br>".
                        "Si vous avez des questions ou des difficultés à confirmer votre adresse email, n'hésitez pas ".
                        "me contacter via le formulaire de contact.<br><br>".
                        "Encore une fois, merci de votre inscription sur P5 DAPS BLOG. Nous espérons vous voir ".
                        "régulièrement sur notre site. <br><br>".
                        "Cordialement, <br>".
                        "Mehdi"
                );
            (new Mailer())->send($email);

            return $this->redirectTo("app_auth_login");

        }

        return $this->render("auth/register.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);

    }


    /**
     * Logout page of auth controller
     *
     * @return Response
     *
     * @throws RouterException
     */
    #[Route("/auth/logout", "app_auth_logout")]
    public function logout(): Response
    {
        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        if ($this->user() && $request->hasCookie("session")) {
            $request->setCookie("session", "", time() - 1);
            Auth::$currentUser = null;

        }

        return $this->redirectTo("app_home");

    }


    /**
     * Valid email of user with token
     *
     * @param string $token
     *
     * @return Response
     * @throws Exception
     */
    #[Route(
        "/auth/valid-email/:token",
        "app_auth_valid_email",
        regexs: ["token" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"]
    )]
    public function validEmail(string $token): Response
    {
        $hashToken = Csrf::generateTokenCsrf($token);
        $userRepo = new UserRepository();

        /**
         * @var User $user
         */
        $user = $userRepo->findByOne(
            ["emailValidateToken" => $hashToken],
            classObject: User::class
        );

        if (!$user || !Csrf::isTokenCsrfValid($user->getEmailValidateToken(), $token)) {
            return $this->responseHttpNotFound();
        }

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");

        if ($user->getExpiredEmailTokenAt() > new DateTime("now")) {
            // Send email
            $email = (new Email())
                ->setSubject("[P5] Bienvenue sur P5 DAPS BLOG - Votre compte est activé!")
                ->setTo($user->getEmail())
                ->setBody(
                    "<p>Bonjour ".$user->getFirstname().", <br><br>".
                    "Je vous souhaite la bienvenue sur P5 DAPS BLOG! Votre adresse email a été confirmée ".
                    "avec succès, et votre compte est maintenant activé. <br>".
                    "Vous pouvez désormais accéder à toutes les fonctionnalités de mon blog, y compris la ".
                    "publication de commentaires. <br><br>".
                    "Je suis toujours heureux d'échanger avec mes utilisateurs, alors n'hésitez pas ".
                    "à me contacter si vous avez des questions ou des suggestions. <br><br>".
                    "Encore une fois, merci de votre inscription sur P5 DAPS BLOG. J'espére vous voir ".
                    "régulièrement sur mon site. <br><br>".
                    "Cordialement, <br>".
                    "Mehdi"
                );
            (new Mailer())->send($email);

            $user
                ->setEmailValidateToken(null)
                ->setStatus(true);

            $manager->flush($user);

            return $this->redirectTo("app_auth_login");
        }

        $token = UuidV4::generate();
        $hashToken = Csrf::generateTokenCsrf($token);

        $urlValidEmail = $this->generateUrl(
            "app_auth_valid_email",
            ["token" => $token],
            true
        );

        $user
            ->setEmailValidateToken($hashToken)
            ->setExpiredEmailTokenAt();

        $manager->flush($user);

        // Send email
        $email = (new Email())
            ->setSubject("[P5] Bienvenue sur P5 DAPS BLOG - Lien de validation de votre email expiré!")
            ->setTo($user->getEmail())
            ->setBody(
                "<p>Bonjour ".$user->getFirstname().", <br><br>".
                "Le lien de validation de votre adresse email est expiré. <br><br>".
                "Pour confirmer votre adresse email et activer votre compte, veuillez cliquer sur le lien ".
                "ci-dessous (Celui-ci est valide pour une durée de 5 minutes) :<br>".
                "<a href=\"".$urlValidEmail."\">$urlValidEmail</a><br><br>".
                "Si vous avez des questions ou des difficultés à confirmer votre adresse email, n'hésitez pas ".
                "me contacter via le formulaire de contact.<br><br>".
                "Cordialement, <br>".
                "Mehdi"
            );
        (new Mailer())->send($email);

        return $this->render("auth/valid-email-failed.html.twig");

    }


}
