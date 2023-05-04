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
     */
    public function __construct()
    {
        parent::__construct(User::class);

    }


    /**
     *
     *
     * @return array<int, User>
     */
    public function getUsersForDashboard(): array
    {
        $query = "
            SELECT u.id, u.lastname, u.firstname, u.pseudo, u.email, u.createdAt, u.role, u.status, (SELECT COUNT(*) FROM post WHERE userId = u.id) AS `countPosts`, (SELECT COUNT(*) FROM comment WHERE userId = u.id) as `countComments`
            FROM `user` as u
            ORDER BY u.lastname
        ";

//        $query2 = "
//            SELECT u.id, u.lastname, u.firstname, u.pseudo, u.email, u.createdAt, u.role, u.status, IFNULL(countPosts, 0), IFNULL(countComments,0)
//            FROM `user` as u
//            left join (
//                SELECT COUNT(*) as countPosts, userId FROM post p  group by userId
//
//            ) tmp1 on tmp1.userId = u.id
//                left join (
//               SELECT COUNT(*) as countComments, userId FROM comment group by userId
//
//            ) tmp2 on tmp2.userId = u.id
//
//            ORDER BY u.lastname
//        ";
        $statement = $this->pdo->prepare($query);
        $statement->execute();

        return $statement->fetchAll();

    }


}
