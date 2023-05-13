<?php

/**
 * PostForm file
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
final class PostForm extends AbstractForm
{
    protected string $csrfKey = "admin-post-form";

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
            ->addField("title", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "title",
                            self::ERROR_REQUIRED
                        );

                        return false;

                    }

                    if (strlen($value) < 5 || strlen($value) > 255) {
                        $this->setError(
                            "title",
                            sprintf(self::ERROR_LENGTH, 5, 255)
                        );

                        return false;

                    }

                    return true;

                }
            ])
            ->addField("chapo", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function ($value) {
                    if (empty($value)) {
                        $this->setError(
                            "chapo",
                            self::ERROR_REQUIRED
                        );

                        return false;

                    }

                    if (strlen($value) < 5) {
                        $this->setError(
                            "chapo",
                            sprintf(self::ERROR_MIN_LENGTH, 5)
                        );

                        return false;

                    }

                    return true;

                }
            ])
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

                    if (strlen($value) < 10) {
                        $this->setError(
                            "content",
                            sprintf(self::ERROR_MIN_LENGTH, 10)
                        );

                        return false;

                    }

                    return true;

                }
            ])
            ->addField("isPublished", options: [
                "sanitize" => FILTER_VALIDATE_BOOL,
                "validation" => function ($value) {
                    if ($value !== null && !filter_var($value, FILTER_VALIDATE_BOOL)) {
                        $this->setError(
                            "isPublished",
                            sprintf(self::ERROR_BAD_FORMAT, "La valeur")
                        );

                        return false;
                    }

                    if ($value === null) {
                        /**
                         * @var Post $post
                         */
                        $post = $this->entity;
                        $post->setIsPublished(false);
                    }

                    return true;

                }
            ])
            ->addField("isFeatured", options: [
                "sanitize" => FILTER_VALIDATE_BOOL,
                "validation" => function ($value) {
                    if ($value !== null && !filter_var($value, FILTER_VALIDATE_BOOL)) {
                        $this->setError(
                            "isFeatured",
                            sprintf(self::ERROR_BAD_FORMAT, "La valeur")
                        );

                        return false;
                    }

                    if (!$this->fields["data"]["isPublished"] && filter_var($value, FILTER_VALIDATE_BOOL)) {
                        $this->setError(
                            "isFeatured",
                            "Pour mettre en avant ce post, veuillez cocher la case 'publier'."
                        );

                        return false;
                    }

                    /**
                     * @var Post $post
                     */
                    $post = $this->entity;
                    $countPostsFeatured = (new PostRepository())->getCountFeaturedPosts($post->getId() ?? "");
                    if ($value === true && $countPostsFeatured === 5) {
                        $this->setError(
                            "isFeatured",
                            "Le nombre de posts mis en avant a été atteint."
                        );

                        return false;
                    }

                    if ($value === null) {
                        $post->setIsFeatured(false);
                    }

                    return true;

                }
            ])
        ;

    }


}
