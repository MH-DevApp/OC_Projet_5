<?php

/**
 * Auth file
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

namespace App\Auth;

use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Router\Request;
use App\Factory\Utils\Csrf\Csrf;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use App\Service\Container\ContainerInterface;
use Exception;

/**
 * Auth class
 * Make connexion with DB environment app and return
 * a PDO
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Auth implements ContainerInterface
{

    public static ?User $currentUser;
    public static ?string $messageError;
    private Manager $manager;
    private Request $request;
    private SessionRepository $sessionRepository;


    /**
     * Constructor
     *
     */
    public function __construct()
    {
        self::$currentUser = null;
        self::$messageError = null;

        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");

        /**
         * @var Request $request
         */
        $request = Container::getService("request");

        $this->manager = $manager;
        $this->request = $request;
        $this->sessionRepository = new SessionRepository();
    }


    /**
     * Check credentials with the user in database.
     *
     * @param array<string, string> $credentials
     *
     * @throws Exception
     */
    public function authenticate(array $credentials): bool
    {
        if ($credentials["email"] && $credentials["password"]) {
            /**
             * @var User|false $user
             */
            $user = (new UserRepository())
                ->findByOne(
                    ["email" => $credentials["email"]],
                    classObject: User::class
                );

            if ($user &&
                $user->getStatus() === User::STATUS_CODE_REGISTERED &&
                password_verify(
                    $credentials["password"],
                    $user->getPassword() ?? ""
                )) {
                $this->isAuthenticateSuccessful($user);
                return true;
            }

            if (!$user ||
                !password_verify(
                    $credentials["password"],
                    $user->getPassword() ?? ""
                )) {
                self::$messageError = "L'Email et/ou le mot de passe sont incorrects.";
            }

            if ($user && $user->getStatus() === User::STATUS_CODE_DEACTIVATED) {
                self::$messageError = "Votre compte a été désactivé par un membre de notre équipe.
                N'hésitez pas à nous contacter via notre formulaire si vous souhaitez en savoir plus.";
            }
        }

        return false;
    }


    /**
     * Create session in the database and set the cookie session.
     *
     * @throws Exception
     */
    private function isAuthenticateSuccessful(User $user): void
    {
        if (is_string($user->getId())) {
            // check if old session of user exist, and delete it if true
            /**
             * @var Session $oldSession
             */
            $oldSession = $this->sessionRepository
                ->findByOne(
                    ["userId" => $user->getId()],
                    classObject: Session::class
                );

            if (is_object($oldSession)) {
                $this->manager->delete($oldSession);
            }

            // Save the new session of user authenticated
            $session = (new Session())
                ->setUserId($user->getId());
            $this->manager->flush($session);

            if (is_string($session->getId())) {
                // Generate the signature of cookie session
                $sign = Csrf::generateTokenCsrf($session->getId());

                // Set cookie session with format Json
                $this->request->setCookie(
                    "session",
                    json_encode([
                        "id" => $session->getId(),
                        "sign" => $sign
                    ]) ?: "",
                    time() + 60 * 60 * 24 * 14
                );

                self::$currentUser = $user;
            }
        }
    }


    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        // check if cookie session exist
        /**
         * @var string $sessionCookieJson
         */
        $sessionCookieJson = $this->request->getCookie("session");

        if ($sessionCookieJson) {
            /**
             * @var array<string, string> $sessionCookie
             */
            $sessionCookie = json_decode($sessionCookieJson, true);

            // Check if signature is valid
            if (Csrf::isTokenCsrfValid($sessionCookie["sign"], $sessionCookie["id"])) {
                /**
                 * @var Session $session
                 */
                $session = (new SessionRepository())
                    ->findByOne(
                        ["id" => $sessionCookie["id"]],
                        classObject: Session::class
                    ) ?? null;

                if (is_object($session) && is_string($session->getUserId())) {
                    /**
                     * @var ?User $user
                     */
                    $user = (new UserRepository())
                        ->findByOne(
                            ["id" => $session->getUserId()],
                            classObject: User::class
                        );

                    if ($user && $user->getStatus() === User::STATUS_CODE_REGISTERED) {
                        self::$currentUser = $user;
                    } else {
                        // Delete cookie session
                        $this->request->setCookie("session", "", time() - 1);
                    }
                }

                return self::$currentUser !== null;
            } else {
                // Delete cookie session invalid
                $this->request->setCookie("session", "", time() - 1);
            }
        }

        return false;
    }
}
