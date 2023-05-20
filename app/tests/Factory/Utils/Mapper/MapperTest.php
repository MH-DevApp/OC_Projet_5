<?php

/**
 * MapperTest file
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

namespace tests\Factory\Utils\Mapper;

use App\Entity\Post;
use App\Factory\Utils\Mapper\Mapper;
use App\Factory\Utils\Uuid\UuidV4;
use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Mapper cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(Mapper::class)]
class MapperTest extends TestCase
{


    /**
     * Test should be to map an array to entity with Post.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to map an array to entity with Post")]
    public function itMapperArrayToEntity(): void
    {
        $createdAt = (new DateTime(
            "now",
            new DateTimeZone("Europe/Paris")
        ))->format(DATE_ATOM);

        $updatedAt = (new DateTime(
            "+5 minutes",
            new DateTimeZone("Europe/Paris")
        ))->format(DATE_ATOM);

        $post = [
            "id" => "1",
            "userId" => "3",
            "title" => "Test title",
            "chapo" => "Test chapo",
            "content" => "Test content",
            "createdAt" => $createdAt,
            "updatedAt" => $updatedAt
        ];
        $entity = Mapper::mapArrayToEntity($post, Post::class);

        $this->assertInstanceOf(Post::class, $entity);
        $this->assertEquals("1", $entity->getId());
        $this->assertEquals("3", $entity->getUserId());
        $this->assertEquals("Test title", $entity->getTitle());
        $this->assertEquals("Test chapo", $entity->getChapo());
        $this->assertEquals("Test content", $entity->getContent());
        $this->assertInstanceOf(DateTime::class, $entity->getCreatedAt());
        $this->assertEquals($createdAt, $entity->getCreatedAt()->format(DATE_ATOM));
        $this->assertInstanceOf(DateTime::class, $entity->getUpdatedAt());
        $this->assertEquals($updatedAt, $entity->getUpdatedAt()->format(DATE_ATOM));

        $postEntity = new Post();
        $this->assertEquals($postEntity, Mapper::mapArrayToEntity($post, $postEntity));

        // Tests cases of null
        $entity = Mapper::mapArrayToEntity([], "App\\ClassNotExist::class");
        $this->assertNull($entity);

        $entity = Mapper::mapArrayToEntity([], UuidV4::class);
        $this->assertNull($entity);
    }


    /**
     * Test should be to map an entity to array with Post.
     *
     * @return void
     *
     * @throws Exception
     */
    #[Test]
    #[TestDox("should be to map an entity to array with Post")]
    public function itMapperEntityToArray(): void
    {
        $createdAt = (new DateTime(
            "now",
            new DateTimeZone("Europe/Paris")
        ))->format(DATE_ATOM);

        $updatedAt = (new DateTime(
            "+5 minutes",
            new DateTimeZone("Europe/Paris")
        ))->format(DATE_ATOM);

        $post = (new Post())
            ->setUserId("3")
            ->setTitle("Test title")
            ->setChapo("Test chapo")
            ->setContent("Test content")
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt)
            ->setId("1");

        $array = Mapper::mapEntityToArray($post);

        $this->assertIsArray($array);
        $this->assertEquals("1", $array["id"]);
        $this->assertEquals("3", $array["userId"]);
        $this->assertEquals("Test title", $array["title"]);
        $this->assertEquals("Test chapo", $array["chapo"]);
        $this->assertEquals("Test content", $array["content"]);
        $this->assertIsString($array["createdAt"]);
        $this->assertEquals($createdAt, $array["createdAt"]);
        $this->assertIsString($array["updatedAt"]);
        $this->assertEquals($updatedAt, $array["updatedAt"]);

        // Test case of null
        $array = Mapper::mapEntityToArray(new UuidV4());

        $this->assertNull($array);
    }
}
