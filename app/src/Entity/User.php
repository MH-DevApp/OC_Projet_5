<?php

/**
 * User Entity file
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
 * User Entity class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class User extends AbstractEntity
{
    const TABLE_NAME = "user";
    const STATUS_CODE_REGISTERED_WAITING = 0;
    const STATUS_CODE_REGISTERED = 1;
    const STATUS_CODE_DEACTIVATED = 2;

    private ?string $lastname = null;
    private ?string $firstname = null;
    private ?string $pseudo = null;
    private ?string $password = null;
    private ?string $email = null;
    private string $role = "ROLE_USER";
    private ?int $status = 0;
    private DateTime|string|null $createdAt = null;
    private ?string $emailValidateToken = null;
    private Datetime|string|null $expiredEmailTokenAt = null;
    private ?string $forgottenPasswordToken = null;
    private Datetime|string|null $expiredTokenAt = null;

    /**
     * Construct
     * @throws Exception
     */
    public function __construct()
    {
        if ($this->createdAt && is_string($this->createdAt)) {
            $this->createdAt = new DateTime(
                $this->createdAt
            );
        }

        if ($this->expiredEmailTokenAt && is_string($this->expiredEmailTokenAt)) {
            $this->expiredEmailTokenAt = new DateTime(
                $this->expiredEmailTokenAt
            );
        }

        if ($this->expiredTokenAt && is_string($this->expiredTokenAt)) {
            $this->expiredTokenAt = new DateTime(
                $this->expiredTokenAt
            );
        }

        if ($this->status === null) {
            $this->setStatus(User::STATUS_CODE_REGISTERED_WAITING);
        }
    }

    /**
     * Get the Lastname of the User
     *
     * @return ?string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }


    /**
     * Set the Lastname of the User
     *
     * @param string $lastname Lastname of the user
     *
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }


    /**
     * Get the Firstname of the User
     *
     * @return ?string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }


    /**
     * Set the Firstname of the User
     *
     * @param string $firstname Firstname of the user
     *
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }


    /**
     * Get the Pseudo of the User
     *
     * @return ?string
     */
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }


    /**
     * Set the Pseudo of the User
     *
     * @param string $pseudo Pseudo of the user
     *
     * @return self
     */
    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }


    /**
     * Get the Password of the User
     *
     * @return ?string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }


    /**
     * Set the Password of the User.
     * This password must be hashed.
     *
     * @param string $password Hash password of the user
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }


    /**
     * Get the Email of the User
     *
     * @return ?string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }


    /**
     * Set the Email of the User.
     *
     * @param string $email Email of the user
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }


    /**
     * Get the Role of the User
     *
     * @return ?string
     */
    public function getRole(): ?string
    {
        return $this->role;
    }


    /**
     * Set the Role of the User.
     *
     * @param string $role Role of the user
     *
     * @return self
     */
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }


    /**
     * Get the status of the User
     *
     * @return ?int
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }


    /**
     * Set the status of the User.
     *
     * @param int $status Status of the user
     *
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }


    /**
     * Get the date of the created at User
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
     * Set the date of the created at User.
     *
     * @param DateTime|string $createdAt Role of the user
     *
     * @return self
     *
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $createdAt): self
    {
        if (is_string($createdAt)) {
            $createdAt = new DateTime($createdAt);
        }

        $this->createdAt = $createdAt;
        return $this;
    }


    /**
     * Get the token of the User who has to be generated by
     * register user.
     *
     * @return ?string
     */
    public function getEmailValidateToken(): ?string
    {
        return $this->emailValidateToken;
    }


    /**
     * Set the token of the User who has to be generated by
     * register user.
     *
     * @param ?string $token Token generated by forgotten password
     *
     * @return self
     */
    public function setEmailValidateToken(?string $token): self
    {
        $this->emailValidateToken = $token;
        return $this;
    }


    /**
     * Get the expired date of the token email.
     *
     * @return DateTime|string|null
     *
     * @throws Exception
     */
    public function getExpiredEmailTokenAt(): DateTime|string|null
    {
        return $this->expiredEmailTokenAt;
    }


    /**
     * Set the expired date of the token email.
     * 5 minutes since generate token email.
     *
     * @param DateTime|null $date
     *
     * @return self
     *
     */
    public function setExpiredEmailTokenAt(?DateTime $date = null): self
    {
        if (!$date) {
            $date = new DateTime("+5 minutes");
        }

        $this->expiredEmailTokenAt = $date;

        return $this;
    }


    /**
     * Get the token of the User who has to be generated by
     * forgotten password.
     *
     * @return ?string
     */
    public function getForgottenPasswordToken(): ?string
    {
        return $this->forgottenPasswordToken;
    }


    /**
     * Set the token of the User who has to be generated by
     * forgotten password.
     *
     * @param ?string $token Token generated by forgotten password
     *
     * @return self
     */
    public function setForgottenPasswordToken(?string $token): self
    {
        $this->forgottenPasswordToken = $token;
        return $this;
    }


    /**
     * Get the expired date of the token forgotten password.
     *
     * @return DateTime|string|null
     *
     * @throws Exception
     */
    public function getExpiredTokenAt(): DateTime|string|null
    {
        return $this->expiredTokenAt;
    }


    /**
     * Set the expired date of the token forgotten password.
     * 5 minutes since generate token forgotten password.
     *
     * @return self
     *
     * @throws Exception
     */
    public function setExpiredTokenAt(?DateTime $date = null): self
    {
        if (!$date) {
            $date = new DateTime("+5 minutes");
        }

        $this->expiredTokenAt = $date;

        return $this;
    }
}
