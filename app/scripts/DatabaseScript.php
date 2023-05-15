<?php

/**
 * DatabaseScript file : create database oc_p5_{env}
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


use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use PDO;

require_once __DIR__.'/../vendor/autoload.php';

try {
    $_ENV["TEST_PATH"] = getenv("TEST_PATH") ?: "";
    (new DotEnv())->load();
} catch (DotEnvException $e) {
    echo $e;
}

$dnsEnv = explode("dbname=", $_ENV["DB_DNS"]);
[$dns, $dbname] = $dnsEnv;
$dbname .= "_".strtolower($_ENV["APP_ENV"]);
$user = $_ENV['DB_USER'] ?? 'root';
$pwd = $_ENV['DB_PWD'] ?? 'password';

$pdo = new PDO($dns, $user, $pwd);

// DROP DATABASE IF EXISTS
echo "## CHECK IF DATABASE EXIST AND DROP IT IF EXIST ##\n";

$statement = $pdo->prepare("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :dbname;");
$statement->bindValue(':dbname', $dbname);
$statement->execute();
$result = $statement->fetchColumn();

if ($result) {
    $pdo->exec("DROP DATABASE $dbname");
    echo "- Database $dbname has deleted successfully.\n\n";
} else {
    echo "- Database $dbname not exists.\n\n";
}

// CREATE DATABASE IF NOT EXISTS
echo "## CREATE DATABASE IF NOT EXISTS ##\n";

$result = $pdo->query("CREATE DATABASE IF NOT EXISTS $dbname COLLATE utf8mb4_general_ci;");

if (!$result) {
    echo "- An error occurred while creating the database.\n";
    echo "- The script has stopped.\n";
    return;
}

echo "- Database has created successfully.\n\n";

// Reload pdo with dbname
echo "## CONNECT PDO TO DATABASE ##\n";

$pdo = new PDO($dns."dbname=$dbname", $user, $pwd);
echo "- Connected to Database\n\n";

// Create tables
echo "## CREATE TABLES ##\n";

// USER
$pdo->query("
    CREATE TABLE `user` (
        `id` VARCHAR(255) NOT NULL UNIQUE,
        `lastname` VARCHAR(255) NOT NULL,
        `firstname` VARCHAR(255) NOT NULL,
        `pseudo` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `createdAt` DATETIME NOT NULL,
        `role` VARCHAR(255) NOT NULL,
        `status` INTEGER NOT NULL DEFAULT 0,
        `forgottenPasswordToken` VARCHAR(255),
        `expiredTokenAt` DATETIME,
        `emailValidateToken` VARCHAR(255),
        `expiredEmailTokenAt` DATETIME,
        PRIMARY KEY(`id`)
    )
");
echo "- User table has created successfully.\n";

// BLOG POST
$pdo->query("
    CREATE TABLE `post` (
        `id` VARCHAR(255) NOT NULL UNIQUE,
        `userId` VARCHAR(255) NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `chapo` TEXT(500) NOT NULL,
        `content` TEXT NOT NULL,
        `isPublished` BOOL NOT NULL DEFAULT FALSE,
        `isFeatured` BOOL NOT NULL DEFAULT FALSE,
        `createdAt` DATETIME NOT NULL,
        `updatedAt` DATETIME,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`userId`) REFERENCES `user`(`id`)
    )
");
echo "- BlogPost table has created successfully.\n";

// COMMENT
$pdo->query("
    CREATE TABLE `comment` (
        `id` VARCHAR(255) NOT NULL UNIQUE,
        `userId` VARCHAR (255) NOT NULL,
        `postId` VARCHAR(255) NOT NULL,
        `content` TEXT NOT NULL,
        `isValid` BOOL NOT NULL DEFAULT FALSE,
        `validByUserId` VARCHAR(255) NULL,
        `validAt` DATETIME NULL,
        `createdAt` DATETIME NOT NULL,
        `updatedAt` DATETIME NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`userId`) REFERENCES `user`(`id`),
        FOREIGN KEY(`postId`) REFERENCES `post`(`id`),
        FOREIGN KEY (`validByUserId`) REFERENCES `user`(`id`)
    )
");
echo "- Comment table has created successfully.\n";

//SESSION
$pdo->query("
    CREATE TABLE `session` (
        `id` VARCHAR(255) NOT NULL UNIQUE,
        `userId` VARCHAR(255) NOT NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`userId`) REFERENCES `user`(`id`)
    )
");
echo "- Auth table has created successfully.\n\n";
