<?php

/**
 * ProfileResetPasswordForm file
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

use App\Auth\Auth;

/**
 * ProfileResetPasswordForm class
 *
 * ProfileResetPassword form builder with sanitize,
 * validation, and errors for fields.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class ProfileResetPasswordForm extends AbstractForm
{
    protected string $csrfKey = "profile-password-reset";


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
    }


    /**
     * Builder connexion form
     *
     * @return void
     *
     * @throws FormException
     */
    public function builder(): void
    {
        parent::builder();

        $this
            ->addField("actualPassword", options: [
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "actualPassword",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    /**
                     * @var string $currentUserPassword
                     */
                    $currentUserPassword = Auth::$currentUser?->getPassword();

                    if (!$currentUserPassword ||
                        !password_verify($value, $currentUserPassword)
                    ) {
                        $this->setError(
                            "actualPassword",
                            self::ERROR_BAD_ACTUAL_PASSWORD
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("newResetPassword", options: [
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "newResetPassword",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 6 || strlen($value) > 20) {
                        $this->setError(
                            "newResetPassword",
                            sprintf(self::ERROR_LENGTH, 6, 20)
                        );

                        return false;
                    }

                    if (!isset($this->fields["data"]["newResetPassword"]) ||
                        !isset($this->fields["data"]["confirmNewResetPassword"]) ||
                        $this->fields["data"]["newResetPassword"] !== $this->fields["data"]["confirmNewResetPassword"]
                    ) {
                        $this->setError(
                            "newResetPassword",
                            "La confirmation du mot de passe n'est pas identique."
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField("confirmNewResetPassword", options: [
                "mapped" => false
            ])
        ;
    }
}
