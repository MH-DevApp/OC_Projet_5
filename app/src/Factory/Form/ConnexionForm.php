<?php

/**
 * ConnexionForm file
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
 * ConnexionForm class
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
final class ConnexionForm extends AbstractForm
{
    protected string $csrfKey = "authenticate";


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

                    return true;
                }
            ])
        ;
    }
}
