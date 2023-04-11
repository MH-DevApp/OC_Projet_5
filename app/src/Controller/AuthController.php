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
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Router\Response;
use App\Service\Container\Container;
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
    public function login(): Response
    {
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

            if (!$auth->authenticate($data)) {
                $form->setError(
                    "global",
                    "L'Email ou le mot de passe sont incorrects."
                );
            }
        }

        return $this->render("auth/connexion.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors()
        ]);
    }


    /**
     * Register page of auth controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     * @throws Exception
     */
    public function register(): Response
    {
        $user = new User();

        $form = new RegisterForm($user);
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
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

            /**
             * @var Manager $manager
             */
            $manager = Container::getService("manager");

            $manager->flush($user);

        }

        return $this->render("auth/register.html.twig", [
            "data" => $form->getData(),
            "errors" => $form->getErrors()
        ]);
    }


    /**
     * Logout page of auth controller
     */
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

        return new Response("SUCCESS");
    }


}
