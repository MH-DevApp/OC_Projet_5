<?php

/**
 * AbstractEntity file
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
 * Abstract Entity class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class AbstractEntity
{

    protected ?string $id = null;


    /**
     * Get id of entity
     *
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->id;

    }


    /**
     * Set id to entity
     *
     * @param string $id Id in format UuidV4
     *
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;

    }


}
