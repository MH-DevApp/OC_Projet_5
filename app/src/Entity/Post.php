<?php

/**
 * Post Entity file
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

namespace App\Entity;


use DateTime;
use DateTimeZone;
use Exception;

/**
 * Post Entity class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Post extends AbstractEntity
{

    private ?string $userId = null;
    private ?string $title = null;
    private ?string $chapo = null;
    private ?string $content = null;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;


    /**
     * Get the Id author of the post
     *
     * @return ?string
     */
    public function getUserId(): ?string
    {
        return $this->userId;

    }


    /**
     * Set the Id author to the post
     *
     * @param string $userId Id user of the session
     *
     * @return self
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;

    }


    /**
     * Get the title of the post
     *
     * @return ?string
     */
    public function getTitle(): ?string
    {
        return $this->title;

    }


    /**
     * Set the title of the post
     *
     * @param string $title Title of the post
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;

    }


    /**
     * Get the chapo of the post
     *
     * @return ?string
     */
    public function getChapo(): ?string
    {
        return $this->chapo;

    }


    /**
     * Set the chapo of the post
     *
     * @param string $chapo Chapo of the post
     *
     * @return self
     */
    public function setChapo(string $chapo): self
    {
        $this->chapo = $chapo;
        return $this;

    }


    /**
     * Get the content of the post
     *
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;

    }


    /**
     * Set the content of the post
     *
     * @param string $contentPost Content of the post
     *
     * @return self
     */
    public function setContent(string $contentPost): self
    {
        $this->content = $contentPost;
        return $this;

    }


    /**
     * Get the date of the created at Post
     *
     * @return DateTime|null
     */
    public function getCreatedAt(): DateTime|null
    {
        return $this->createdAt;

    }


    /**
     * Set the date of the created at Post.
     *
     * @param DateTime|string $createdAtPost Date of created at post
     *
     * @return self
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $createdAtPost): self
    {
        if (is_string($createdAtPost)) {
            $createdAtPost = new DateTime($createdAtPost, new DateTimeZone('Europe/Paris'));
        }

        $this->createdAt = $createdAtPost;
        return $this;

    }


    /**
     * Get the date of the updated at Post
     *
     * @return DateTime|null
     */
    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;

    }


    /**
     * Set the date of the updated at Post.
     *
     * @param DateTime|string $updatedAtPost Date of updated at post
     *
     * @return self
     * @throws Exception
     */
    public function setUpdatedAt(DateTime|string $updatedAtPost): self
    {
        if (is_string($updatedAtPost)) {
            $updatedAtPost = new DateTime($updatedAtPost, new DateTimeZone('Europe/Paris'));
        }

        $this->updatedAt = $updatedAtPost;
        return $this;

    }


}
