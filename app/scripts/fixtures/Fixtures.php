<?php

/**
 * UserFixtures file : create random users in oc_p5_{env}
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

namespace scripts;


use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Mapper\Mapper;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use DateTime;
use Exception;
use PDO;
use ReflectionException;

require_once __DIR__.'/../../vendor/autoload.php';

try {
    $_ENV["TEST_PATH"] = getenv("TEST_PATH") ?: "";
    (new DotEnv())->load();
    Container::loadServices();
} catch (DotEnvException|ReflectionException $e) {
    echo $e;
    return;
}

/**
 * @var Manager $manager
 */
$manager = Container::getService("manager");

if (!$manager->getPDO() instanceof PDO) {
    echo "- Database not exists, execute the command : 'composer run make:database' for create database.\n\n";
    return;
}

// DROP ALL DATA IN DATABASE
echo "\n## DROP ALL DATA IN DATABASE ##\n";

// Comments
/**
 * @var array<int, Comment>|false $comments
 */
$comments = (new CommentRepository())->findAll([PDO::FETCH_CLASS, Comment::class]);

if ($comments !== false) {
    foreach ($comments as $comment) {
        if (!($comment instanceof Comment)) {
            $comment = Mapper::mapArrayToEntity($comment, Comment::class);
        }

        $manager->delete($comment);
    }
}

echo "- Comments has deleted successfully\n";

// Posts
/**
 * @var array<int, Post>|false $posts
 */
$posts = (new PostRepository())->findAll([PDO::FETCH_CLASS, Post::class]);

if ($posts !== false) {
    foreach ($posts as $post) {
        if (!($post instanceof Post)) {
            $post = Mapper::mapArrayToEntity($post, Post::class);
        }

        $manager->delete($post);
    }
}

echo "- Posts has deleted successfully\n";

// Sessions

/**
 * @var array<int, Session>|false $sessions
 */
$sessions = (new SessionRepository())->findAll([PDO::FETCH_CLASS, Session::class]);

if ($sessions !== false) {
    /**
     * @var User|false $user
     */
    $user = (new UserRepository())->findByOne(["pseudo" => "Test"], classObject: User::class);

    if ($user !== false) {
        $sessions = array_filter(
            $sessions,
            fn ($session) => $session->getUserId() !== $user->getId()
        );
    }

    foreach ($sessions as $session) {
        if (!($session instanceof Session)) {
            $session = Mapper::mapArrayToEntity($session, Session::class);
        }

        $manager->delete($session);
    }
}

echo "- Sessions has deleted successfully\n";

// Users

/**
 * @var array<int, User>|false $users
 */
$users = (new UserRepository())->findAll([PDO::FETCH_CLASS, User::class]);

if ($users !== false) {
    $users = array_filter(
        $users,
        fn ($user) => $user->getPseudo() !== "Test"
    );

    foreach ($users as $user) {
        if (!($user instanceof User)) {
            $user = Mapper::mapArrayToEntity($user, User::class);
        }

        $manager->delete($user);
    }
}

echo "- Users has deleted successfully\n";

echo "## DROP ALL DATA SUCCESSFULLY ##\n\n";

// Create entities
echo "## CREATE ENTITIES ##\n";

// Admin and users

for ($i=0; $i < 3; $i++) {
    try {
        $createdAt = (new DateTime("-".random_int(1, 180)."days"))->format(DATE_ATOM);
        $password = password_hash("123456", PASSWORD_ARGON2ID);

        $user = (new User())
            ->setLastname("Admin".($i===0 ? "" : $i))
            ->setFirstname("Admin".($i===0 ? "" : $i))
            ->setPseudo("Admin".($i===0 ? "" : $i))
            ->setEmail("admin".($i===0 ? "" : $i)."@test.fr")
            ->setPassword($password)
            ->setRole("ROLE_ADMIN")
            ->setStatus(User::STATUS_CODE_REGISTERED);

        $manager->flush($user);

        $user->setCreatedAt($createdAt);
        $manager->flush($user);

    } catch (Exception $e) {
        echo "$e";
        return;
    }
}

echo "- Users with role admin has created successfully (x3)\n";

for ($i=0; $i < 40; $i++) {
    try {
        $createdAt = (new DateTime("-".random_int(1, 180)."days"))->format(DATE_ATOM);
        $password = password_hash("123456", PASSWORD_ARGON2ID);



        $user = (new User())
            ->setLastname("User".($i===0 ? "" : $i))
            ->setFirstname("User".($i===0 ? "" : $i))
            ->setPseudo("User".($i===0 ? "" : $i))
            ->setEmail("user".($i===0 ? "" : $i)."@test.fr")
            ->setPassword($password)
            ->setStatus(random_int(0, 2));

        $manager->flush($user);

        $user->setCreatedAt($createdAt);
        $manager->flush($user);

    } catch (Exception $e) {
        echo "$e";
        return;
    }
}

echo "- Users with role user has created successfully (x40)\n";

// POSTS

/**
 * @var array<int, User>|false
 */
$users = (new UserRepository())->findBy(
    ["role" => "ROLE_ADMIN"],
    [PDO::FETCH_CLASS, User::class]
);

if ($users) {
    $countFeatured = 0;
    for ($i=0; $i < 80; $i++) {
        try {
            $user = $users[random_int(0, count($users)-1)];
            $isPublished = random_int(0, 1) === 1;
            $isFeatured = $countFeatured < 5 && random_int(0, 1) === 1;

            $countFeatured = $isFeatured ?
                ++$countFeatured :
                $countFeatured;

            $createdAt = (new DateTime("-".random_int(1, 180)."days"))->format(DATE_ATOM);

            $post = (new Post())
                ->setTitle("Post ".($i===0?"":$i))
                ->setChapo("Chapo ".($i===0?"":$i))
                ->setContent("Contenu ".($i===0?"":$i))
                ->setUserId($user->getId() ?? "")
                ->setIsPublished($isPublished)
                ->setIsFeatured($isFeatured);

            $manager->flush($post);

            $post->setCreatedAt($createdAt);

            $manager->flush($post);

        } catch (Exception $e) {
            echo "$e";
            return;
        }
    }
}

echo "- Posts has created successfully (x80)\n";

// COMMENTS

/**
 * @var array<int, User>|false
 */
$users = (new UserRepository())->findAll([PDO::FETCH_CLASS, User::class]
);

/**
 * @var array<int, Post>|false
 */
$posts = (new PostRepository())->findAll([PDO::FETCH_CLASS, Post::class]);

if ($users && $posts) {
    for ($i=0; $i < 300; $i++) {
        try {
            /**
             * @var array<int, User> $usersWithRoleUser
             */
            $usersWithRoleUser = array_values(array_filter(
                $users,
                fn ($user) => $user->getRole() === "ROLE_USER"
            ));

            $user = $usersWithRoleUser[
                random_int(
                    0,
                    count($usersWithRoleUser) > 0 ?
                        count($usersWithRoleUser)-1 :
                        0
                )
            ];

            $post = $posts[random_int(0, count($posts)-1)];
            $isValid = random_int(0, 1) === 1;

            $comment = (new Comment())
                ->setUserId($user->getId() ?: "")
                ->setPostId($post->getId() ?: "")
                ->setContent("Comment ".($i===0?"":$i));

            $manager->flush($comment);

            $createdAt = (new DateTime("-".random_int(1, 180)."days"));
            $comment->setCreatedAt($createdAt);

            if ($isValid) {
                $comment->setIsValid(true);
                /**
                 * @var array<int, User> $usersWithRoleAdmin
                 */
                $usersWithRoleAdmin = array_values(array_filter(
                    $users,
                    fn ($user) => $user->getRole() === "ROLE_ADMIN"
                ));

                $validByUserId = $usersWithRoleAdmin[
                    random_int(
                        0,
                        count($usersWithRoleAdmin) > 0 ?
                            count($usersWithRoleAdmin)-1 :
                            0
                    )
                ];
                $validAt = $createdAt;
                $validAt->add(new \DateInterval("P".random_int(0, 2)."D"));

                $comment->setValidAt($validAt->format(DATE_ATOM));
                $comment->setValidByUserId($validByUserId->getId() ?: "");

            }

            $manager->flush($comment);

        } catch (Exception $e) {
            echo "$e";
            return;
        }
    }
}

echo "- Comments has created successfully (x300)\n";

echo "## ENTITIES CREATED SUCCESSFULLY ##\n\n";
