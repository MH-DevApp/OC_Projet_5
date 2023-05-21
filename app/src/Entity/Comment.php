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

use App\Repository\UserRepository;
use DateTime;
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

    const TABLE_NAME = "comment";

    private ?string $userId = null;
    private ?string $postId = null;
    private ?string $content = null;
    private ?bool $isValid = false;
    private ?string $validByUserId = null;
    private ?string $validAt = null;
    private Datetime|string|null $createdAt = null;
    private Datetime|string|null $updatedAt = null;


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
     * Get the status valid of the comment
     *
     * @return bool
     */
    public function getIsValid(): bool
    {
        return $this->isValid ?? false;
    }


    /**
     * Set the status valid of the comment
     *
     * @param bool $isValid Status of the comment
     *
     * @return self
     */
    public function setIsValid(bool $isValid): self
    {
        $this->setValidAt();
        $this->isValid = $isValid;
        return $this;
    }


    /**
     * Get the admin user who valid the comment
     *
     * @return ?User
     */
    public function getValidByUserId(): ?User
    {
        $user = null;

        if ($this->validByUserId) {
            /**
             * @var User|false $user
             */
            $user = (new UserRepository())->findByOne(
                ["id" => $this->validByUserId],
                classObject: User::class
            );

            if (!$user) {
                $user = null;
            }
        }

        return $user;
    }


    /**
     * Set the admin user who valid the comment
     *
     * @param string|User $validByUserId Admin user who valid the comment
     *
     * @return self
     */
    public function setValidByUserId(string|User $validByUserId): self
    {
        if ($validByUserId instanceof User) {
            $validByUserId = $validByUserId->getId();
        }

        $this->validByUserId = $validByUserId;
        return $this;
    }


    /**
     * Get the date when the comment has validated
     *
     * @return ?DateTime
     * @throws Exception
     */
    public function getValidAt(): ?DateTime
    {
        if ($this->validAt) {
            return new DateTime($this->validAt);
        }

        return null;
    }


    /**
     * Set the date when the comment has validated
     *
     * @param string|DateTime|null $validAt Date when the comment has validated
     *
     * @return self
     */
    public function setValidAt(string|DateTime|null $validAt = null): self
    {
        if ($validAt instanceof DateTime) {
            $validAt = $validAt->format(DATE_ATOM);
        } elseif ($validAt === null) {
            $validAt = (new DateTime('now'))->format(DATE_ATOM);
        }

        $this->validAt = $validAt;
        return $this;
    }


    /**
     * Get the date of the created at Comment
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
     * Set the date of the created at Comment.
     *
     * @param DateTime|string|null $createdAtComment Date of created at comment
     *
     * @return self
     *
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string|null $createdAtComment = null): self
    {
        if (is_string($createdAtComment)) {
            $createdAtComment = new DateTime($createdAtComment);
        } elseif ($createdAtComment === null) {
            $createdAtComment = new DateTime("now");
        }

        $this->createdAt = $createdAtComment;
        return $this;
    }


    /**
     * Get the date of the updated at Comment
     *
     * @return ?DateTime
     *
     * @throws Exception
     */
    public function getUpdatedAt(): ?DateTime
    {
        if (is_string($this->updatedAt)) {
            return new DateTime($this->updatedAt);
        } elseif ($this->updatedAt instanceof DateTime) {
            return $this->updatedAt;
        }

        return null;
    }


    /**
     * Set the date of the updated at Comment.
     *
     * @param DateTime|string|null $updatedAtComment Date of updated at comment
     *
     * @return self
     *
     * @throws Exception
     */
    public function setUpdatedAt(DateTime|string|null $updatedAtComment = null): self
    {
        if (is_string($updatedAtComment)) {
            $updatedAtComment = new DateTime($updatedAtComment);
        } elseif ($updatedAtComment === null) {
            $updatedAtComment = new DateTime("now");
        }

        $this->updatedAt = $updatedAtComment;
        return $this;
    }
}
