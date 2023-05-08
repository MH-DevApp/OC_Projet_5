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

    const TABLE_NAME = "post";

    private ?string $userId = null;
    private ?string $title = null;
    private ?string $chapo = null;
    private ?string $content = null;
    private ?bool $isPublished = false;
    private ?bool $isFeatured = false;
    private DateTime|string|null $createdAt = null;
    private DateTime|string|null $updatedAt = null;


    /**
     * Construct
     * @throws Exception
     */
    public function __construct()
    {
        if ($this->createdAt && is_string($this->createdAt)) {
            $this->createdAt = new DateTime($this->createdAt);
        }

        if ($this->updatedAt && is_string($this->updatedAt)) {
            $this->updatedAt = new DateTime($this->updatedAt);
        }
    }


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
     * @param string $userId Id author of the post
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
     * Get the status publish of the post
     *
     * @return bool
     */
    public function getIsPublished(): bool
    {
        return $this->isPublished ?? false;

    }


    /**
     * Set the status publish of the post
     *
     * @param bool $isPublished Status published of the post
     *
     * @return self
     */
    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;
        return $this;

    }


    /**
     * Get the status featured of the post
     *
     * @return bool
     */
    public function getIsFeatured(): bool
    {
        return $this->isFeatured ?? false;

    }


    /**
     * Set the status featured of the post
     *
     * @param bool $isFeatured Status featured of the post
     *
     * @return self
     */
    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;

    }


    /**
     * Get the date of the created at Post
     *
     * @return DateTime|string|null
     *
     * @throws Exception
     */
    public function getCreatedAt(): DateTime|string|null
    {
        return $this->createdAt;

    }


    /**
     * Set the date of the created at Post.
     *
     * @param DateTime|string $createdAtPost Date of created at post
     *
     * @return self
     *
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $createdAtPost): self
    {
        if (is_string($createdAtPost)) {
            $createdAtPost = new DateTime($createdAtPost);
        }

        $this->createdAt = $createdAtPost;
        return $this;

    }


    /**
     * Get the date of the updated at Post
     *
     * @return DateTime|string|null
     *
     * @throws Exception
     */
    public function getUpdatedAt(): DateTime|string|null
    {
        return $this->updatedAt;

    }


    /**
     * Set the date of the updated at Post.
     *
     * @param DateTime|string $updatedAtPost Date of updated at post
     *
     * @return self
     *
     * @throws Exception
     */
    public function setUpdatedAt(DateTime|string $updatedAtPost): self
    {
        if (is_string($updatedAtPost)) {
            $updatedAtPost = new DateTime($updatedAtPost);
        }

        $this->updatedAt = $updatedAtPost;
        return $this;

    }


}
