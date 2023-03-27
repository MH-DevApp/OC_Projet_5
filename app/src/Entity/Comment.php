<?php

/**
 * Comment Entity file
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
 * Comment Entity class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Comment extends AbstractEntity
{

    private ?string $userId = null;
    private ?string $postId = null;
    private ?string $content = null;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;


    /**
     * Get the Id author of the comment
     *
     * @return ?string
     */
    public function getUserId(): ?string
    {
        return $this->userId;

    }


    /**
     * Set the Id author to the comment
     *
     * @param string $userId Id user of the comment
     *
     * @return self
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;

    }


    /**
     * Get the Id post of the comment
     *
     * @return ?string
     */
    public function getPostId(): ?string
    {
        return $this->postId;

    }


    /**
     * Set the Id post to the comment
     *
     * @param string $postId Id post of the comment
     *
     * @return self
     */
    public function setPostId(string $postId): self
    {
        $this->postId = $postId;
        return $this;

    }


    /**
     * Get the content of the comment
     *
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;

    }


    /**
     * Set the content of the comment
     *
     * @param string $commentContent Content of the comment
     *
     * @return self
     */
    public function setContent(string $commentContent): self
    {
        $this->content = $commentContent;
        return $this;

    }


    /**
     * Get the date of the created at Comment
     *
     * @return DateTime|null
     */
    public function getCreatedAt(): DateTime|null
    {
        return $this->createdAt;

    }


    /**
     * Set the date of the created at Comment.
     *
     * @param DateTime|string $createdAtComment Date of created at comment
     *
     * @return self
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $createdAtComment): self
    {
        if (is_string($createdAtComment)) {
            $createdAtComment = new DateTime($createdAtComment, new DateTimeZone('Europe/Paris'));
        }

        $this->createdAt = $createdAtComment;
        return $this;

    }


    /**
     * Get the date of the updated at Comment
     *
     * @return DateTime|null
     */
    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;

    }


    /**
     * Set the date of the updated at Comment.
     *
     * @param DateTime|string $updatedAtComment Date of updated at comment
     *
     * @return self
     * @throws Exception
     */
    public function setUpdatedAt(DateTime|string $updatedAtComment): self
    {
        if (is_string($updatedAtComment)) {
            $updatedAtComment = new DateTime($updatedAtComment, new DateTimeZone('Europe/Paris'));
        }

        $this->updatedAt = $updatedAtComment;
        return $this;

    }


}
