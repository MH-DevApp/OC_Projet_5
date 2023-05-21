<?php

/**
 * Session Entity file
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

/**
 * Session Entity class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Session extends AbstractEntity
{

    const TABLE_NAME = "session";

    private ?string $userId = null;


    /**
     * Get the Id user of the session
     *
     * @return ?string
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }


    /**
     * Set the Id user to the session
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
}
