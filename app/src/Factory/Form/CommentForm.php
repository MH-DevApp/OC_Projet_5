<?php

/**
 * CommentForm file
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

use App\Entity\Post;
use App\Repository\PostRepository;

/**
 * CommentForm class
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
final class CommentForm extends AbstractForm
{
    protected string $csrfKey = "comment-form";

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
            ->addField("content", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "content",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) > 1024) {
                        $this->setError(
                            "content",
                            sprintf(self::ERROR_MAX_LENGTH, 1024)
                        );

                        return false;
                    }

                    return true;
                }
            ])
        ;
    }
}
