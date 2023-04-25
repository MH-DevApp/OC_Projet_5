<?php

/**
 * ForgottenPasswordResetForm file
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


/**
 * ForgottenPasswordResetForm class
 *
 * ForgottenPassword form builder with sanitize,
 * validation, and errors for fields.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class ForgottenPasswordResetForm extends AbstractForm
{
    protected string $csrfKey = "forgotten-password-reset";


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

                    if (
                        !isset($this->fields["data"]["password"]) ||
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
