<?php


namespace MySpot;


use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use stdClass;

/**
 * SQLMap execution & query result
 */
class SqlMapResult
{

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var PDOStatement
     */
    private $stmt;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var SqlMapStatement
     */
    private $sqlMapStatement;

    /**
     * @var bool
     */
    private $executedResult;

    /**
     * @var int
     */
    private $lastInsertId;


    /**
     * SqlStatement constructor.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdoStatement
     * @param string $prepareSql
     * @param SqlMapStatement $sqlMapStatement
     * @param bool $result
     * @param int $lastInsertId
     */
    public function __construct(PDO $pdo, PDOStatement $pdoStatement, string $prepareSql, SqlMapStatement $sqlMapStatement, bool $result, int $lastInsertId = 0)
    {
        $this->pdo = $pdo;
        $this->stmt = $pdoStatement;
        $this->sql = $prepareSql;
        $this->sqlMapStatement = $sqlMapStatement;
        $this->executedResult = $result;
        $this->lastInsertId = $lastInsertId;
    }

    public function getStatement(): PDOStatement
    {
        return $this->stmt;
    }

    /**
     * Get affected lines.
     *
     * @return int
     */
    public function getAffectedLines(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get the value by specific column number, useful for SELECT COUNT() query
     *
     * @param int $column
     * @return mixed|bool The data or false if there's no data
     */
    public function fetchColumn($column = 0)
    {
        return $this->stmt->fetchColumn($column);
    }

    /**
     * Fetch all data
     *
     * @param array|null $keyMap
     * @return array The result sets or empty array if there's no data
     * @throws SqlMapException
     */
    public function fetchAll(array $keyMap = null): array
    {
        $resultType = $this->sqlMapStatement->getResultType();
        $resultSets = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($resultSets) == 0) {
            return [];
        }
        $firstRow = current($resultSets);
        $keyMap = $this->generateKeyMap($firstRow, $keyMap);

        $result = [];
        foreach ($resultSets as $row) {
            $result[] = $this->buildResult($row, $keyMap, $resultType);
        }

        return $result;
    }

    /**
     * Fetch by row, move cursor forward
     *
     * @param array|null $keyMap
     * @return array|object|null
     * @throws SqlMapException
     */
    public function fetch(array $keyMap = null)
    {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if (!isset($row) or $row == false) {
            return null;
        }

        $resultType = $this->sqlMapStatement->getResultType();
        $keyMap = $this->generateKeyMap($row, $keyMap);

        return $this->buildResult($row, $keyMap, $resultType);
    }

    /**
     * Get the execute result
     *
     * @return bool
     */
    public function getExecutedResult(): bool
    {
        return $this->executedResult;
    }

    /**
     * Get the last autoincrement Id
     *
     * @return int
     */
    public function getLastInsertId(): int
    {
        return $this->lastInsertId;
    }

    /**
     * Generate keymap according to configuration
     * If passed in keymap as a parameter, use it as highest priority.
     *
     * @param mixed $data fetched data
     * @param array|null $keyMap Specific keymap
     * @return array
     */
    private function generateKeyMap($data, array $keyMap = null): array
    {
        if (!isset($keyMap)) {
            $keys = array_keys($data);
            $keyMap = [];
            if ($this->sqlMapStatement->getResultMapStyle() === SqlMapStatement::MAP_STYLE_DO_NOT_CONVERT) {
                foreach ($keys as $key) {
                    $keyMap[$key] = $key;
                }
            } else {
                $mapFunction = (SqlMapMapFunctions::getFunction($this->sqlMapStatement->getResultMapStyle()));
                foreach ($keys as $key) {
                    $keyMap[$key] = $mapFunction($key);
                }
            }
        }
        return $keyMap;
    }

    /**
     * Build result array or object
     *
     * @param array $data
     * @param array $keyMap
     * @param string $resultType
     * @return array|object|stdClass
     * @throws SqlMapException
     */
    private function buildResult(array $data, array $keyMap, string $resultType)
    {
        if ($resultType == 'array') {
            $result = [];
            foreach ($keyMap as $dataKey => $resultKey) {
                $result[$resultKey] = $data[$dataKey];
            }
        } else if ($resultType == 'class' || $resultType == 'object') {
            $result = new stdClass;
            foreach ($keyMap as $dataKey => $resultKey) {
                $result->$resultKey = $data[$dataKey];
            }
        } else {
            try {
                $result = $this->buildObject($data, $keyMap, $resultType);
            } catch (ReflectionException $e) {
                throw new SqlMapException("Object initialization failed", 0, $e);
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param array $keyMap
     * @param string $targetClassName
     * @return object
     * @throws ReflectionException
     */
    private function buildObject(array $data, array $keyMap, string $targetClassName): object
    {
        $reflectionClass = new ReflectionClass($targetClassName);

        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $reflectionObj = new ReflectionObject($instance);

        foreach ($keyMap as $dataKey => $propertyName) {
            $methodName = 'set' . ucfirst($propertyName);
            if ($reflectionObj->hasProperty($propertyName)) {
                if ($reflectionObj->hasMethod($methodName)) {
                    $reflectionObj->getMethod($methodName)->invoke($instance, $data[$dataKey]);
                } else {
                    $reflectionProperty = $reflectionObj->getProperty($propertyName);
                    if ($reflectionProperty->isPublic() and !$reflectionProperty->isStatic()) {
                        $reflectionProperty->setValue($instance, $data[$dataKey]);
                    }
                }
            }
        }

        return $instance;
    }


}