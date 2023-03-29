<?php

/**
 * UserRepository file
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

namespace App\Repository;


use App\Entity\User;

/**
 * User Repository class
 * Contains statements of Abstract Repository :
 * **FindAll()**,
 * **FindBy()**,
 * **FindByOne()**
 * And Custom statements for User Entity only
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class UserRepository extends AbstractRepository
{


    /**
     * Construct
     *
     * @throws RepositoryException
     */
    public function __construct()
    {
        parent::__construct(User::class);

    }


}
