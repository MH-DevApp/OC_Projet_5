<?php

/**
 * EntityTest file
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

namespace tests\Entity;


use App\Entity\AbstractEntity;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Session;
use App\Entity\User;
use App\Factory\Utils\Uuid\UuidV4;
use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Entities cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(AbstractEntity::class)]
#[CoversClass(User::class)]
#[CoversClass(Session::class)]
#[CoversClass(Post::class)]
#[CoversClass(Comment::class)]
class EntityTest extends TestCase
{


    /**
     * Test should be created a User Entity.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be created a User Entity")]
    public function itCreateUserEntity(): void
    {
        $id = UuidV4::generate();
        $createdAt = new DateTime(
            "now",
            new DateTimeZone("Europe/Paris")
        );
        $password = password_hash("TEST", PASSWORD_ARGON2ID);

        /** @var User $user */
        $user = (new User())
            ->setLastname("Lastname")
            ->setFirstname("Firstname")
            ->setPseudo("Pseudo")
            ->setEmail("mail@test.fr")
            ->setPassword($password)
            ->setCreatedAt($createdAt->format(DATE_ATOM))
            ->setForgottenPasswordToken("Token")
            ->setExpiredTokenAt()
            ->setId($id)
        ;

        $expiredAt = (new DateTime("+5 minutes", new DateTimeZone("Europe/Paris")))->format(DATE_ATOM);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals("Lastname", $user->getLastname());
        $this->assertEquals("Firstname", $user->getFirstname());
        $this->assertEquals("Pseudo", $user->getPseudo());
        $this->assertEquals("mail@test.fr", $user->getEmail());
        $this->assertEquals("ROLE_USER", $user->getRole());
        $this->assertEquals("Token", $user->getForgottenPasswordToken());
        $this->assertEquals(new DateTime($createdAt->format(DATE_ATOM)), $user->getCreatedAt());
        $this->assertTrue(password_verify("TEST", $user->getPassword() ?? ""));
        $this->assertEquals($expiredAt, $user->getExpiredTokenAt()?->format(DATE_ATOM) ?? "");

        $user->setRole("ROLE_ADMIN");

        $this->assertEquals("ROLE_ADMIN", $user->getRole());

    }


    /**
     * Test should be created a Session Entity.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be created a Session Entity")]
    public function itCreateSessionEntity(): void
    {
        $idUser = UuidV4::generate();
        $idSession = UuidV4::generate();

        /** @var Session $session */
        $session = (new Session())
            ->setUserId($idUser)
            ->setId($idSession);

        $this->assertEquals($idSession, $session->getId());
        $this->assertEquals($idUser, $session->getUserId());

    }


    /**
     * Test should be created a Post Entity.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be created a Post Entity")]
    public function itCreatePostEntity(): void
    {
        $idPost = UuidV4::generate();
        $idUser = UuidV4::generate();
        $createdAt = new DateTime(
            "now",
            new DateTimeZone("Europe/Paris")
        );
        $updatedAt = new DateTime(
            "+2 days",
            new DateTimeZone("Europe/Paris")
        );

        /** @var Post $post */
        $post = (new Post())
            ->setUserId($idUser)
            ->setTitle("Title of test")
            ->setChapo("Chapo of test")
            ->setContent("Content of test")
            ->setCreatedAt($createdAt->format(DATE_ATOM))
            ->setUpdatedAt($updatedAt->format(DATE_ATOM))
            ->setId($idPost);

        $this->assertEquals($idPost, $post->getId());
        $this->assertEquals($idUser, $post->getUserId());
        $this->assertEquals("Title of test", $post->getTitle());
        $this->assertEquals("Chapo of test", $post->getChapo());
        $this->assertEquals("Content of test", $post->getContent());
        $this->assertEquals(new DateTime($createdAt->format(DATE_ATOM)), $post->getCreatedAt());
        $this->assertEquals(new DateTime($updatedAt->format(DATE_ATOM)), $post->getUpdatedAt());

    }


    /**
     * Test should be created a Comment Entity.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be created a Comment Entity")]
    public function itCreateCommentEntity(): void
    {
        $idComment = UuidV4::generate();
        $idPost = UuidV4::generate();
        $idUser = UuidV4::generate();
        $createdAt = new DateTime(
            "now",
            new DateTimeZone("Europe/Paris")
        );
        $updatedAt = new DateTime(
            "+2 days",
            new DateTimeZone("Europe/Paris")
        );

        /** @var Comment $comment */
        $comment = (new Comment())
            ->setUserId($idUser)
            ->setPostId($idPost)
            ->setContent("Content of test")
            ->setCreatedAt($createdAt->format(DATE_ATOM))
            ->setUpdatedAt($updatedAt->format(DATE_ATOM))
            ->setId($idComment);

        $this->assertEquals($idComment, $comment->getId());
        $this->assertEquals($idUser, $comment->getUserId());
        $this->assertEquals($idPost, $comment->getPostId());
        $this->assertEquals("Content of test", $comment->getContent());
        $this->assertEquals(new DateTime($createdAt->format(DATE_ATOM)), $comment->getCreatedAt());
        $this->assertEquals(new DateTime($updatedAt->format(DATE_ATOM)), $comment->getUpdatedAt());

    }


}
