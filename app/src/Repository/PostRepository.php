<?php

/**
 * PostRepository file
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


use App\Entity\Post;

/**
 * Post Repository class
 * Contains statements of Abstract Repository :
 * **FindAll()**,
 * **FindBy()**,
 * **FindByOne()**
 * And Custom statements for Post Entity only
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class PostRepository extends AbstractRepository
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(Post::class);

    }


    /**
     * @param string $order
     *
     * @return array<int, array<string, string|int>>
     */
    public function getPostsPublishedByOrderDate(string $order = "ASC"): array
    {
        $query = "
            SELECT p.id as `post_id`, p.title as `post_title`, p.chapo as `post_chapo`,
                   p.content as `post_content`, p.createdAt as `post_createdAt`,
                   p.updatedAt as `post_updatedAt`, u.id as `user_id`, u.lastname as `user_lastname`,
                   u.firstname as `user_firstname`, u.pseudo as `author_pseudo`, (SELECT COUNT(*) FROM comment as c WHERE c.postId = p.id) as `comment_count`
            FROM post as p
            JOIN user as u on p.userId = u.id
            WHERE p.isPublished = TRUE
            ORDER BY p.createdAt $order
        ";

        $statement = $this->pdo->prepare($query);
        $statement->execute();

        return $statement->fetchAll() ?: [];

    }


    /**
     * Get Post by Id with User
     *
     * @param string $postId
     *
     * @return array<string, string|int>
     */
    public function getPostByIdWithUser(string $postId): array
    {
        $query = "
            SELECT p.title as `post_title`, p.chapo as `post_chapo`, p.content as `post_content`, p.createdAt as `post_createdAt`, p.updatedAt as `post_updatedAt`, u.pseudo as `author_pseudo`
            FROM post as p
            JOIN user u on p.userId = u.id
            WHERE p.id = :postId
        ";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(":postId", $postId);
        $statement->execute();

        /**
         * @var array<string, string|int>
         */
        return $statement->fetch() ?: [];
    }


    /**
     * Get all posts for dashboard
     *
     * @param string|null $id
     * @return array<string, string|int>
     */
    public function getPostsForDashboard(?string $id = null): array
    {
        if ($id) {
            $query = "
                SELECT p.id, p.title, p.chapo, p.content, p.isPublished, p.isFeatured, p.createdAt, p.updatedAt, u.pseudo as `author`, (SELECT COUNT(*) FROM comment WHERE postId = p.id) as `countComments`
                FROM post as p
                JOIN user u on p.userId = u.id
                WHERE p.id = :id
                ORDER BY p.createdAt DESC
            ";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id", $id);
            $statement->execute();

            /**
             * @var array<string, string|int>
             */
            return $statement->fetch() ?: [];
        }

        $query = "
            SELECT p.id, p.title, p.chapo, p.content, p.isPublished, p.isFeatured, p.createdAt, p.updatedAt, u.pseudo as `author`, (SELECT COUNT(*) FROM comment WHERE postId = p.id) as `countComments`
            FROM post as p
            JOIN user u on p.userId = u.id
            ORDER BY p.createdAt DESC
        ";

        $statement = $this->pdo->prepare($query);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }


    /**
     * Get count of featured posts
     *
     * @param string|null $id
     * @return int
     */
    public function getCountFeaturedPosts(?string $id = null): int
    {
        $query = "
            SELECT COUNT(*) from post WHERE isFeatured = TRUE
        ";
        if ($id) {
            $query .= " AND NOT id = :id";
        }

        $statement = $this->pdo->prepare($query);
        if ($statement !== false) {
            if ($id) {
                $statement->bindValue(":id", $id);
            }
            $statement->execute();
            $count = $statement->fetchColumn();

            return is_numeric($count) ? (int)$count : 0;
        }

        return 0;
    }


}
