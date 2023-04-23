<?php

/**
 * Manager file
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

namespace App\Factory\Manager;

use App\Database\Database;
use App\Entity\AbstractEntity;
use App\Factory\Utils\Mapper\Mapper;
use App\Factory\Utils\Uuid\UuidV4;
use App\Service\Container\ContainerInterface;
use DateTime;
use Exception;
use PDO;

/**
 * Manager class
 *
 * Persist tasks for flush and save in the database.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Manager implements ContainerInterface
{
    private PDO $pdo;

    /**
     * @var array<int, array<string, string|object>> $tasks
     */
    private array $tasks = [];


    /**
     * Constructor
     *
     */
    public function __construct()
    {

        $this->pdo = (new Database())->connect();

    }


    /**
     * Get PDO instance
     *
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;

    }


    /**
     * Persist tasks request to DB in the list of Tasks
     *
     * @param object ...$entities
     *
     * @return void
     * @throws Exception
     */
    public function persist(object ...$entities): void
    {
        /**
         * @var AbstractEntity $entity
         */
        foreach ($entities as $entity) {
            if (!$entity instanceof AbstractEntity) {
                throw new ManagerException("The entity has not an instance of AbstractEntity, check the entity.");
            }

            if (!$entity::TABLE_NAME) {
                throw new ManagerException("The entity has not a table name defined.");
            }

            $action = $entity->getId() ? "UPDATE" : "CREATE";

            if ($action === "CREATE") {
                $entity->setId(UuidV4::generate());

            }

            $this->tasks[] = [
                "action" => $action,
                "entity" => $entity
            ];
        }
    }


    /**
     * Execute all tasks in the list of tasks to the database
     *
     * @param object ...$entities
     *
     * @return void
     * @throws Exception
     */
    public function flush(object ...$entities): void
    {
        if ($entities) {
            $this->persist(...$entities);

        }

        foreach ($this->tasks as $task) {

            /**
             * @var AbstractEntity $entity
             */
            $entity = $task['entity'];

            if ($task["action"] === "CREATE") {
                if (method_exists($entity, "setCreatedAt")) {
                    $entity->setCreatedAt(
                        new DateTime("now")
                    );
                }

                $arrayEntity = Mapper::mapEntityToArray($entity);

                if ($arrayEntity) {
                    $this->createEntity($arrayEntity, $entity::TABLE_NAME);
                }

            } else {
                if (
                    !$this->isInCreateTasksOfList($entity) &&
                    method_exists($entity, "setUpdatedAt")
                ) {
                    $entity->setUpdatedAt(
                        new DateTime("now")
                    );

                }

                $arrayEntity = Mapper::mapEntityToArray($entity);

                if ($arrayEntity) {
                    $this->updateEntity($arrayEntity, $entity::TABLE_NAME);
                }

            }
        }

        $this->tasks = [];
    }


    /**
     * Create an entity in the db
     *
     * @param array<string, string|int> $obj
     * @param string $tableName
     *
     * @return void
     */
    private function createEntity(array $obj, string $tableName): void
    {
        $keys = array_keys($obj);
        $query = "INSERT INTO $tableName (";
        $query .= join(", ", $keys).")";
        $query .= " VALUES ";
        $query .= "(".join(", ", array_map(fn ($key) => ":val_".$key, $keys)).")";

        $statement = $this->pdo->prepare($query);

        foreach ($keys as $key) {
            $value = $obj[$key];

            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $statement->bindValue(":val_".$key, $value);

        }

        $statement->execute();
    }


    /**
     * Update an entity in the DB by ID
     *
     * @param array<string, string|int> $obj
     * @param string $tableName
     *
     * @return void
     */
    private function updateEntity(array $obj, string $tableName): void
    {
        // without ID key
        $keys = array_filter(
            array_keys($obj),
            fn ($key) => $key !== "id"
        );

        $query = "UPDATE $tableName SET ";
        $query .= join(
            ", ",
            array_map(
                fn ($key) => $key." = :".$key, $keys
            ));
        $query .= " WHERE id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(":id", $obj["id"]);

        foreach ($keys as $key) {
            $value = $obj[$key];

            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $statement->bindValue(":".$key, $value);

        }

        $statement->execute();

    }


    /**
     * Delete the entity in the db
     *
     * @param object $entity
     *
     * @return void
     */
    public function delete(object $entity): void
    {
        /**
         * @var AbstractEntity $entity
         */
        $tableName = $entity::TABLE_NAME;

        $query = "DELETE FROM $tableName WHERE id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(":id", $entity->getId());
        $statement->execute();

    }


    /**
     * Check if exist a task of create in the list
     * for the param entity.
     *
     * @param AbstractEntity $entity
     *
     * @return bool
     */
    private function isInCreateTasksOfList(AbstractEntity $entity): bool
    {
        return count(
            array_filter($this->tasks, function ($task) use ($entity) {
                /**
                 * @var AbstractEntity $entityTask
                 */
                $entityTask = $task["entity"];

                return $task["action"] === "CREATE" &&
                    $entityTask->getId() === $entity->getId();

            })) > 0;

    }


}
