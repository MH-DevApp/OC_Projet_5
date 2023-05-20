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
     */
    public function __construct()
    {
        parent::__construct(Comment::class);
    }


    /**
     * Get all comments by Post id
     *
     * @return array<string, string|int|bool>
     */
    public function getCommentsByPostId(string $postId): array
    {
        $query = "
            SELECT c.id, u.pseudo as `author`, c.content, c.isValid, (SELECT pseudo FROM user WHERE id = c.validByUserId) as `validBy`, c.validAt, c.createdAt, c.updatedAt
            FROM comment as c
            JOIN user u on c.userId = u.id
            WHERE c.postId = :postId
            ORDER BY c.createdAt DESC
        ";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(":postId", $postId);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }


    /**
     * Get all comments for dashboard
     *
     * @return array<string, string|int|bool>
     */
    public function getCommentsForDashboard(): array
    {
        $query = "
            SELECT c.id, u.pseudo as `author`, p.title as `titlePost`, c.content, c.isValid, (SELECT pseudo FROM user WHERE id = c.validByUserId) as `validBy`, c.validAt, c.createdAt, c.updatedAt
            FROM comment as c
            JOIN user u on c.userId = u.id
            JOIN post p on c.postId = p.id
            ORDER BY c.createdAt DESC
        ";

        $statement = $this->pdo->prepare($query);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }
}
