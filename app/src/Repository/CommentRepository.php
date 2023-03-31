<?php

/**
 * CommentRepository file
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


use App\Entity\Comment;

/**
 * Comment Repository class
 * Contains statements of Abstract Repository :
 * **FindAll()**,
 * **FindBy()**,
 * **FindByOne()**
 * And Custom statements for Comment Entity only
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class CommentRepository extends AbstractRepository
{


    /**
     * Construct
     *
     * @throws RepositoryException
     */
    public function __construct()
    {
        parent::__construct(Comment::class);

    }


}
