<?php

/**
 * ManagerTest file
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

namespace tests\Factory\Manager;

use App\Entity\AbstractEntity;
use App\Entity\Post;
use App\Entity\User;
use App\Factory\Manager\Manager;
use App\Factory\Manager\ManagerException;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Factory\Utils\Uuid\UuidV4;
use App\Repository\PostRepository;
use Exception;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test Manager cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Manager::class)]
class ManagerTest extends TestCase
{

    private int $countEntity;
    private Manager $manager;
    private User $user;


    /**
     * Init environment of test
     *
     * @throws DotEnvException
     * @throws Exception
     */
    #[Before]
    public function init(): void
    {
        $_ENV["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        $this->manager = new Manager();
        $this->countEntity = 0;
        $this->user = $this->createUser();

        $this->manager->flush($this->user);
    }


    /**
     * End tests
     *
     * @return void
     */
    #[After]
    public function endTests(): void
    {
        $this->deleteEntity($this->user);
    }


    /**
     * Test should be flush entity post in the database.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be flush with entity post in the database")]
    public function itFlushWithEntityInDB(): void
    {
        $post = $this->createPost();
        $this->manager->flush($post);

        $this->assertNotNull($post->getId());

        $repo = new PostRepository();
        $entity = $repo->findByOne(["id" => $post->getId()]);

        $this->assertNotNull($entity);

        $this->deleteEntity($post);
    }


    /**
     * Test should be to persist some post's entities in the database.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to persist some post's entities in the database")]
    public function itPersistSomeEntitiesInDB(): void
    {
        /**
         * @var array<int, Post> $posts
         */
        $posts = [];
        $repo = new PostRepository();

        for ($i=0; $i<3; $i++) {
            $posts[] = $this->createPost();
        }

        $this->manager->persist(...$posts);
        $this->manager->flush();

        $entities = $repo->findAll();
        $this->assertCount(3, $entities);

        foreach ($posts as $post) {
            $this->assertNotNull($post->getId());
            $entity = $repo->findByOne(["id" => $post->getId()]);
            $this->assertNotNull($entity);
            $this->deleteEntity($post);
        }
    }


    /**
     * Test should be to persist and flush a post in the database, and
     * update this.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to persist and flush a post in the database, and update this")]
    public function itPersistAndFlushInDBAndUpdateThis(): void
    {
        $repo = new PostRepository();
        $post = $this->createPost();
        $this->manager->flush($post);

        $post->setTitle("Test Manager Update");

        $this->manager->flush($post);

        $this->assertNotNull($post->getUpdatedAt());

        $entity = $repo->findByOne(["id" => $post->getId() ?? ""]);

        $this->assertNotNull($entity);

        $this->deleteEntity($post);
    }


    /**
     * Test should be to throw manager exception with object
     * has not table name.
     *
     * @return void
     *
     * @throws Exception
     * @throws MockException
     */
    #[Test]
    #[TestDox("should be to throw manager exception with object has not table name")]
    public function itThrowManagerExceptionWithObjectHasNotTableName(): void
    {
        $this->expectException(ManagerException::class);
        $this->expectExceptionMessage("The entity has not a table name defined.");

        $entity = $this->createMock(AbstractEntity::class);

        $this->manager->flush($entity);
    }


    /**
     * Test should be to throw manager exception with object
     * is not Entity.
     *
     * @return void
     *
     * @throws Exception
     * @throws MockException
     */
    #[Test]
    #[TestDox("should be to throw manager exception with object is not Entity")]
    public function itThrowManagerExceptionWithObjectIsNotEntity(): void
    {
        $this->expectException(ManagerException::class);
        $this->expectExceptionMessage("The entity has not an instance of AbstractEntity, check the entity.");

        $entity = new UuidV4();

        $this->manager->flush($entity);
    }


    /**
     * Create a Post entity
     *
     * @return Post
     */
    private function createPost(): Post
    {
        $this->countEntity++;
        return (new Post())
            ->setUserId($this->user->getId() ?? "")
            ->setTitle("Test Manager ".$this->countEntity)
            ->setChapo("Test Manager ".$this->countEntity)
            ->setContent("Test Manager ".$this->countEntity);
    }


    /**
     * Create a User entity
     *
     * @return User
     */
    private function createUser(): User
    {
        return (new User())
            ->setFirstname("Test Manager ")
            ->setLastname("Test Manager ")
            ->setPseudo("Test Manager ")
            ->setPassword("Test Manager")
            ->setEmail("test@test.com");
    }


    /**
     * Delete a User entity
     *
     * @param object $entity
     *
     * @return void
     */
    public function deleteEntity(object $entity): void
    {
        $this->manager->delete($entity);
    }
}
