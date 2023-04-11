<?php

/**
 * ContactForm file
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
 * ContactForm class
 *
 * Contact form builder with sanitize,
 * validation, and errors for fields.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class ContactForm extends AbstractForm
{
    protected string $csrfKey = "contact";

    public function __construct(?object $entity = null)
    {
        parent::__construct($entity);
    }

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
            ->addField("subject", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "subject",
                            self::ERROR_REQUIRED
                        );

                        return false;

                    }

                    if (strlen($value) < 5 || strlen($value) > 120) {
                        $this->setError(
                            "subject",
                            sprintf(self::ERROR_LENGTH, 5, 120)
                        );

                        return false;

                    }

                    return true;

                }
            ])
            ->addField("message", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "message",
                            self::ERROR_REQUIRED
                        );

                        return false;

                    }

                    if (strlen($value) < 10) {
                        $this->setError(
                            "message",
                            sprintf(self::ERROR_MIN_LENGTH, 10)
                        );

                        return false;

                    }

                    return true;

                }
            ])
        ;

    }


}
