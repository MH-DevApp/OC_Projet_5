<?php

/**
 * Mapper file
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

namespace App\Factory\Utils\Mapper;


use App\Entity\AbstractEntity;
use DateTime;
use ReflectionClass;

/**
 * Mapper class
 *
 * Map an array associative to entity and entity to array
 * associative.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class Mapper
{


    /**
     * Map array associative to entity
     *
     * @param array<string, string|int> $obj
     * @param class-string|string|object $entity
     *
     * @return object|null
     */
    public static function mapArrayToEntity(
        array $obj,
        string|object $entity
    ): ?object
    {
        if (class_exists(is_string($entity) ? $entity : $entity::class)) {
            $entity = is_string($entity) ?
                new $entity() :
                $entity;

            if (!$entity instanceof AbstractEntity) {
                return null;

            }

            foreach ($obj as $key => $value) {
                if (property_exists($entity, $key)) {
                    if ($value) {
                        $key[0] = strtoupper($key[0]);
                        $method = "set".$key;

                        if (method_exists($entity, $method)) {
                            $entity->$method($value);
                        }

                    }

                }
            }

            return $entity;

        }

        return null;

    }


    /**
     * Map Entity to array associative
     *
     * @param object $entity
     *
     * @return array<string, string|int>|null
     */
    public static function mapEntityToArray(object $entity): ?array
    {
        if (class_exists($entity::class) && $entity instanceof AbstractEntity) {
            $reflexionClass = new ReflectionClass($entity::class);
            $obj = [];

            foreach ($reflexionClass->getProperties() as $props) {
                $name = $props->getName();
                $name[0] = strtoupper($name[0]);
                $method = "get".$name;

                if ($reflexionClass->hasMethod($method)) {
                    $obj[$props->getName()] = $entity->$method() instanceof DateTime ?
                        $entity->$method()->format(DATE_ATOM) :
                        $entity->$method();

                }

            }

            return $obj;

        }

        return null;

    }


}
