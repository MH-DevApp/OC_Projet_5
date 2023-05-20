<?php

/**
 * RegisterForm file
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

namespace App\Factory\Form;

use App\Entity\User;
use App\Repository\UserRepository;

/**
 * RegisterForm class
 *
 * Connexion form builder with sanitize,
 * validation, and errors for fields.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class RegisterForm extends AbstractForm
{
    protected string $csrfKey = "register";

    private UserRepository $userRepository;

    /**
     * Constructor
     *
     * @param object|null $entity
     *
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        parent::__construct($entity);
        $this->userRepository = new UserRepository();
    }


    /**
     * Builder register form
     *
     * @return void
     *
     * @throws FormException
     */
    public function builder(): void
    {
        parent::builder();

        $this
            ->addField("lastname", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "lastname",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 2 || strlen($value) > 50) {
                        $this->setError(
                            "lastname",
                            sprintf(self::ERROR_LENGTH, 2, 50)
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("firstname", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "firstname",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 2 || strlen($value) > 50) {
                        $this->setError(
                            "firstname",
                            sprintf(self::ERROR_LENGTH, 2, 50)
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("pseudo", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "pseudo",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 2 || strlen($value) > 50) {
                        $this->setError(
                            "pseudo",
                            sprintf(self::ERROR_LENGTH, 2, 50)
                        );

                        return false;
                    }

                    $user = $this->userRepository->findByOne(
                        ["pseudo" => $value],
                        classObject: User::class
                    );

                    if (is_object($user)) {
                        $this->setError(
                            "pseudo",
                            sprintf(self::ERROR_UNIQUE, "Le pseudo")
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("email", options: [
                "sanitize" => FILTER_SANITIZE_EMAIL,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "email",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->setError(
                            "email",
                            sprintf(self::ERROR_BAD_FORMAT, "L'email")
                        );

                        return false;
                    }

                    $user = $this->userRepository->findByOne(
                        ["email" => $value],
                        classObject: User::class
                    );

                    if (is_object($user)) {
                        $this->setError(
                            "email",
                            sprintf(self::ERROR_UNIQUE, "L'email")
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("password", options: [
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "password",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 6 || strlen($value) > 20) {
                        $this->setError(
                            "password",
                            sprintf(self::ERROR_LENGTH, 6, 20)
                        );

                        return false;
                    }

                    if (!isset($this->fields["data"]["password"]) ||
                        !isset($this->fields["data"]["confirmPassword"]) ||
                        $this->fields["data"]["password"] !== $this->fields["data"]["confirmPassword"]
                    ) {
                        $this->setError(
                            "password",
                            "La confirmation du mot de passe n'est pas identique."
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("confirmPassword", options: [
                "mapped" => false
            ])
        ;
    }
}
