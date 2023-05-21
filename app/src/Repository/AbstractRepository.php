<?php

/**
 * AbstractRepository file
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

use App\Entity\AbstractEntity;
use App\Factory\Manager\Manager;
use App\Service\Container\Container;
use PDO;

/**
 * Abstract Repository class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
abstract class AbstractRepository
{

    protected PDO $pdo;


    /**
     * Construct
     *
     * @param class-string $entityName Class name of entity
     */
    public function __construct(protected string $entityName)
    {
        /** @var Manager $manager */
        $manager = Container::getService("manager");
        $this->pdo = $manager->getPDO();
    }


    /**
     * @param array<int, int|class-string> $returnType [Optional] Type Entity for return,
     *                                            Default value []
     *                                            example value : [PDO::FETCH_CLASS, User::class]
     *
     * @return array<int, array<string, string>|AbstractEntity>
     */
    public function findAll(array $returnType = []): array
    {
        $statement = $this->pdo->prepare(
            "SELECT *".
            " FROM ".$this->entityName::TABLE_NAME
        );
        $statement->execute();

        /**
         * @var int $mode
         * @var class-string $classObject
         */
        [$mode, $classObject] = $returnType;

        if ($mode && $classObject) {
            return $statement->fetchAll($mode, $classObject);
        }

        return $statement->fetchAll();
    }


    /**
     * @param array<string, string> $where [Optional] Search all values where params,
     *                                      Default value [],
     *                                      example : ["id" => "f93af1bf-cace-4d4c-a319-bca215cfa4f4"]
     *                                   Default value all columns (*)
     * @param array<int|class-string> $returnType [Optional] Type Entity for return,
     *                                            Default value []
     *                                            example value : [PDO::FETCH_CLASS, User::class]
     *
     * @return array<int, array<string, string>|AbstractEntity>
     */
    public function findBy(array $where = [], array $returnType = []): array
    {
        $query = "SELECT *".
            " FROM ".$this->entityName::TABLE_NAME;

        if (count($where)) {
            $query .= $this->addWhere($where);
        }

        $statement = $this->pdo->prepare($query);

        foreach ($where as $k => $v) {
            $statement->bindValue(":$k", $v);
        }

        $statement->execute();

        /**
         * @var int $mode
         * @var class-string $classObject
         */
        [$mode, $classObject] = $returnType;

        if ($mode && $classObject) {
            return $statement->fetchAll($mode, $classObject);
        }

        return $statement->fetchAll();
    }

    /**
     * @param  array<string, string> $where [Optional] Search all values where params,
     *                                      Default value [],
     *                                      example : ["id" => "f93af1bf-cace-4d4c-a319-bca215cfa4f4"]
     *                                   Default value all columns (*)
     * @param  class-string|null $classObject [Optional] Entity if not null,
     *                                        Default value is null.
     *
     * @return mixed
     */
    public function findByOne(
        array $where,
        ?string $classObject = null
    ): mixed {
        $query = "SELECT *".
            " FROM ".$this->entityName::TABLE_NAME.
            $this->addWhere($where).
            " LIMIT 1 ";

        $statement = $this->pdo->prepare($query);

        foreach ($where as $k => $v) {
            $statement->bindValue(":$k", $v);
        }

        $statement->execute();

        if ($classObject) {
            return $statement->fetchObject($classObject);
        }

        return $statement->fetch();
    }


    /**
     * Return string with all where params for query
     *
     * @param  array<string, string> $where Params to Find with key and value
     *
     * @return string
     */
    private function addWhere(array $where): string
    {
        $i = 0;
        $query = " WHERE ";

        foreach ($where as $k => $v) {
            if ($i) {
                $query .= " AND ";
            }
            $query .= $k." = :".$k;
            $i++;
        }

        return $query;
    }
}
