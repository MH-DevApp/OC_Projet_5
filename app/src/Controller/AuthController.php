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
use App\Factory\Form\ForgottenPasswordForm;
use App\Factory\Form\ForgottenPasswordResetForm;
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
                Auth::$messageError ?? ""
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
             * @var string $hashTokenEmail
             */
            $hashTokenEmail = Csrf::generateTokenCsrf($tokenEmail);

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

            $user->setEmailValidateToken($hashTokenEmail);
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

            /**
             * @var string $userEmail
             */
            $userEmail = $user->getEmail();

            // Send email
            $email = (new Email())
                ->setSubject("[P5] Bienvenue sur P5 DAPS BLOG - Veuillez confirmer votre adresse email")
                ->setTo($userEmail)
                ->setBodyTwig(
                    "emails/register.html.twig",
                    [
                        "firstname" => $user->getFirstname(),
                        "urlValidEmail" => $urlValidEmail
                    ]
                );
            (new Mailer())->send($email);

            return $this->render("auth/register-success.html.twig");
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
        regexs: ["token" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        methods: ["GET", "POST"]
    )]
    public function validEmail(string $token): Response
    {
        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);
        $userRepo = new UserRepository();

        /**
         * @var User|false $user
         */
        $user = $userRepo->findByOne(
            ["emailValidateToken" => $hashToken],
            classObject: User::class
        );

        if (!$user || !Csrf::isTokenCsrfValid($user->getEmailValidateToken() ?: "", $token)) {
            return $this->responseHttpNotFound();
        }

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");

        /**
         * @var string $userEmail
         */
        $userEmail = $user->getEmail();

        if ($user->getExpiredEmailTokenAt() > new DateTime("now")) {
            // Send email
            $email = (new Email())
                ->setSubject("[P5] P5 DAPS BLOG - Votre compte est activé!")
                ->setTo($userEmail)
                ->setBodyTwig(
                    "emails/confirm-email.html.twig",
                    [
                        "firstname" => $user->getFirstname(),
                    ]
                );
            (new Mailer())->send($email);

            $user
                ->setEmailValidateToken(null)
                ->setStatus(User::STATUS_CODE_REGISTERED);

            $manager->flush($user);

            return $this->render("auth/valid-email-success.html.twig");
        }

        $form = new ForgottenPasswordForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            $token = UuidV4::generate();

            /**
             * @var string $hashToken
             */
            $hashToken = Csrf::generateTokenCsrf($token);

            $urlValidEmail = $this->generateUrl(
                "app_auth_valid_email",
                ["token" => $token],
                true
            );

            if ($form->getData("email") === $user->getEmail()) {
                $user
                ->setEmailValidateToken($hashToken)
                ->setExpiredEmailTokenAt();

                $manager->flush($user);

                /**
                 * @var string $emailUser
                 */
                $emailUser = $user->getEmail();

                // Send email
                $email = (new Email())
                    ->setSubject("[P5] P5 DAPS BLOG - Nouveau lien d'activation de votre email")
                    ->setTo($emailUser)
                    ->setBodyTwig(
                        "emails/refresh-link-confirm-email.html.twig",
                        [
                            "firstname" => $user->getFirstname(),
                            "urlValidEmail" => $urlValidEmail
                        ]
                    );
                (new Mailer())->send($email);
            }

            return $this->render("auth/valid-email-send-new-email.html.twig");
        }

        return $this->render("auth/valid-email-token-expired.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);
    }


    /**
     * Forgotten password to reset password
     *
     * @return Response
     *
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws RouterException
     * @throws Exception
     */
    #[Route(
        "/auth/forgotten-password",
        "app_auth_forgotten_password",
        methods: ["GET", "POST"]
    )
    ]
    public function forgottenPassword(): Response
    {
        if (Auth::$currentUser) {
            return $this->redirectTo("app_home");
        }

        $form = new ForgottenPasswordForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepo = new UserRepository();

            /**
             * @var string $email
             */
            $email = $form->getData("email");

            /**
             * @var User|false $user
             */
            $user = $userRepo->findByOne(["email" => $email], classObject: User::class);

            if ($user) {
                /**
                 * @var Manager $manager
                 */
                $manager = Container::getService("manager");

                $token = UuidV4::generate();

                /**
                 * @var string $hashToken
                 */
                $hashToken = Csrf::generateTokenCsrf($token);

                $link = $this->generateUrl(
                    "app_auth_forgotten_password_reset",
                    ["token" => $token],
                    true
                );

                $user
                    ->setForgottenPasswordToken($hashToken)
                    ->setExpiredTokenAt();

                $manager->flush($user);

                /**
                 * @var string $emailUser
                 */
                $emailUser = $user->getEmail();

                // Send email
                $emailTemplate = (new Email())
                    ->setSubject("[P5] P5 DAPS BLOG - Réinitialisation de votre mot de passe")
                    ->setTo($emailUser)
                    ->setBodyTwig(
                        "emails/forgotten-password.html.twig",
                        [
                            "firstname" => $user->getFirstname(),
                            "link" => $link
                        ]
                    );
                (new Mailer())->send($emailTemplate);
            }

            return $this->render("auth/forgotten-password-send-email.html.twig", [
                "email" => $email
            ]);
        }


        return $this->render("auth/forgotten-password.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);
    }


    /**
     * Reset password with forgotten password token
     *
     * @param string $token
     *
     * @return Response
     *
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     *
     */
    #[Route(
        "/auth/forgotten-password/reset-password/:token",
        "app_auth_forgotten_password_reset",
        regexs: ["token" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        methods: ["GET", "POST"]
    )]
    public function resetForgottenPassword(string $token): Response
    {
        /**
         * @var string $hashToken
         */
        $hashToken = Csrf::generateTokenCsrf($token);
        $userRepo = new UserRepository();

        /**
         * @var User|false $user
         */
        $user = $userRepo->findByOne(
            ["forgottenPasswordToken" => $hashToken],
            classObject: User::class
        );

        if (!$user || !Csrf::isTokenCsrfValid($user->getForgottenPasswordToken() ?: "", $token)) {
            return $this->responseHttpNotFound();
        }

        if ($user->getExpiredTokenAt() < new DateTime("now")) {
            return $this->render("auth/forgotten-password-token-expired.html.twig");
        }

        $form = new ForgottenPasswordResetForm();
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Manager $manager
             */
            $manager = Container::getService("manager");

            /**
             * @var string $password
             */
            $password = $form->getData("newPassword");

            $user
                ->setPassword(password_hash($password, PASSWORD_ARGON2ID))
                ->setForgottenPasswordToken(null);

            $manager->flush($user);

            $link = $this->generateUrl("app_auth_login", isAbsolute: true);

            /**
             * @var string $emailUser
             */
            $emailUser = $user->getEmail();

            // Send email
            $emailTemplate = (new Email())
                ->setSubject("[P5] P5 DAPS BLOG - Votre mot de passe a été réinitialisé")
                ->setTo($emailUser)
                ->setBodyTwig(
                    "emails/forgotten-password-success.html.twig",
                    [
                        "firstname" => $user->getFirstname(),
                        "link" => $link
                    ]
                );
            (new Mailer())->send($emailTemplate);
            
            return $this->redirectTo("app_auth_login");
        }


        return $this->render("auth/forgotten-password-reset.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors(),
            "submitted" => $form->isSubmitted()
        ]);
    }
}
