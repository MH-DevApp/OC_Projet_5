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

$sql = file_get_contents(__DIR__."/sql/admins.sql");
$manager->getPDO()->exec($sql);

$sql = file_get_contents(__DIR__."/sql/users.sql");
$manager->getPDO()->exec($sql);

echo "- Users with role user has created successfully\n";

$sql = file_get_contents(__DIR__."/sql/posts.sql");
$manager->getPDO()->exec($sql);

echo "- Posts has created successfully\n";

$sql = file_get_contents(__DIR__."/sql/comments.sql");
$manager->getPDO()->exec($sql);

echo "- Comments has created successfully\n";

echo "## ENTITIES CREATED SUCCESSFULLY ##\n\n";
