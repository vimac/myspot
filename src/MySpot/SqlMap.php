<?php


namespace MySpot;


use PDO;
use Psr\Log\LoggerInterface;

class SqlMap
{

    /**
     * @var SqlMapConfig
     */
    private $config;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $sqlMapConfigPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $enableDebugLog;

    public function __construct(SqlMapConfig $config)
    {
        $this->config = $config;

        $this->pdo = $config->getPdo();
        $this->sqlMapConfigPath = $config->getSqlMapConfigPath();
        $this->logger = $config->getLogger();
        $this->enableDebugLog = $config->getEnableDebugLog();
    }


    /**
     * Execute SELECT statement
     *
     * $sqlMap->select('configFile.configKey', [parameters]);
     *
     * @param string $statementId
     * @param array $params
     * @return SqlMapResult
     * @throws SqlMapException
     */
    public function select(string $statementId, array $params = [])
    {
        $sqlStatement = $this->config->getStatementById($statementId);

        $sqlFromConfig = $sqlStatement->getSqlTemplate();

        $template = new SqlMapTemplate($sqlFromConfig);
        list($sql, $params) = $template->render($params);

        $this->enableDebugLog && $this->logger->debug(sprintf('select: "%s", sql: "%s"', $statementId, $sql));
        $stmt = $this->pdo->prepare($sql);

        $this->validatePdoStatementAvailable($stmt, $sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, ...$v);
        }

        $result = $stmt->execute();
        return new SqlMapResult($this->pdo, $stmt, $sql, $sqlStatement, $result);
    }

    /**
     * Execute INSERT statement
     *
     * @param string $statementId
     * @param array $inserts
     * @return SqlMapResult
     * @throws SqlMapException
     */
    public function insert(string $statementId, array $inserts)
    {
        $sqlStatement = $this->config->getStatementById($statementId);

        $sqlFromConfig = $sqlStatement->getSqlTemplate();

        $columns = [];
        $values = [];

        foreach ($inserts as $key => $insert) {
            $columns[] = '`' . $key . '`';
            $values[] = ':' . $key;
        }

        $replacement = '(' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';

        $sql = str_ireplace('#INSERT#', $replacement, $sqlFromConfig);

        $template = new SqlMapTemplate($sql);
        list($sql, $params) = $template->render($inserts);

        $this->enableDebugLog && $this->logger->debug(sprintf('insert: "%s", sql: "%s"', $statementId, $sql));
        $stmt = $this->pdo->prepare($sql);

        $this->validatePdoStatementAvailable($stmt, $sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, ...$v);
        }

        $result = $stmt->execute();

        if ($result) {
            $lastInsertId = $this->pdo->lastInsertId();
        } else {
            $lastInsertId = 0;
        }
        return new SqlMapResult($this->pdo, $stmt, $sql, $sqlStatement, $result, $lastInsertId);
    }

    /**
     * Execute batch INSERT statement
     * Parameter 'insertsList' is the list form of parameter 'inserts' in insert()
     *
     * @param string $statementId
     * @param array $insertsList
     * @return SqlMapResult
     * @throws SqlMapException
     * @see insert()
     */
    public function insertBatch(string $statementId, array $insertsList)
    {
        $sqlStatement = $this->config->getStatementById($statementId);

        $sqlFromConfig = $sqlStatement->getSqlTemplate();

        $columns = [];
        $valueStmts = [];

        if (count($insertsList) < 1) {
            throw new SqlMapException('insertsList cannot be empty');
        }

        $firstInserts = current($insertsList);
        foreach ($firstInserts as $key => $insert) {
            $columns[] = '`' . $key . '`';
        }

        // Should be very careful about reference variable
        $params = [];
        array_walk($insertsList, function ($inserts, $idx) use (&$valueStmts, &$params) {
            $valuesStatement = [];
            array_walk($inserts, function ($insertItem, $key) use (&$valuesStatement, $idx, &$params) {
                $key .= $idx;
                $valuesStatement[] = ':' . $key;
                $params[$key] = $insertItem;
            });
            $stmtFragment = implode(',', $valuesStatement);
            $valueStmts[] = '(' . $stmtFragment . ')';
        });

        $replacement = '(' . implode(',', $columns) . ') VALUES ' . implode(',', $valueStmts);

        $sql = str_ireplace('#INSERT#', $replacement, $sqlFromConfig);
        $this->enableDebugLog && $this->logger->debug(sprintf('insertBatch: "%s", sql: "%s"', $statementId, $sql));
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, ... $v);
        }

        $result = $stmt->execute();

        if ($result) {
            $lastInsertId = $this->pdo->lastInsertId();
        } else {
            $lastInsertId = 0;
        }
        return new SqlMapResult($this->pdo, $stmt, $sql, $sqlStatement, $result, $lastInsertId);
    }

    /**
     * Execute UPDATE statement
     *
     * @param string $statementId
     * @param array $params
     * @param array $updates
     * @return SqlMapResult
     * @throws SqlMapException
     */
    public function update(string $statementId, array $params = [], array $updates = [])
    {
        $sqlStatement = $this->config->getStatementById($statementId);

        $sqlFromConfig = $sqlStatement->getSqlTemplate();

        $replacementFragments = [];
        foreach ($updates as $key => $update) {
            $bindKey = 'mySpotGenerated' . ucfirst($key);
            $params[$bindKey] = $update;
            $replacementFragments[] = '`' . $key . '` = :' . $bindKey;
        }

        $replacement = implode(',', $replacementFragments);

        $generatedSqlTemplate = str_ireplace('#UPDATE#', $replacement, $sqlFromConfig);
        $sqlTemplate = new SqlMapTemplate($generatedSqlTemplate);
        list($sql, $params) = $sqlTemplate->render($params);

        $this->enableDebugLog && $this->logger->debug(sprintf('update: "%s", sql: "%s"', $statementId, $sql));
        $stmt = $this->pdo->prepare($sql);

        $this->validatePdoStatementAvailable($stmt, $sql);

        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $stmt->bindValue($k, ...$v);
            } else {
                $stmt->bindValue($k, $v);
            }
        }

        $result = $stmt->execute();
        return new SqlMapResult($this->pdo, $stmt, $sql, $sqlStatement, $result);
    }

    /**
     * Execute DELETE statement
     *
     * @param string $statementId
     * @param array $params
     * @return SqlMapResult
     * @throws SqlMapException
     */
    public function delete(string $statementId, array $params = [])
    {
        $sqlStatement = $this->config->getStatementById($statementId);

        $sqlFromConfig = $sqlStatement->getSqlTemplate();

        $template = new SqlMapTemplate($sqlFromConfig);
        list($sql, $params) = $template->render($params);

        $this->enableDebugLog && $this->logger->debug(sprintf('delete: "%s", sql: "%s"', $statementId, $sql));
        $stmt = $this->pdo->prepare($sql);

        $this->validatePdoStatementAvailable($stmt, $sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, ...$v);
        }

        $result = $stmt->execute();
        return new SqlMapResult($this->pdo, $stmt, $sql, $sqlStatement, $result);
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Wrapped PDO transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Validate PDOStatement create correctly
     *
     * @param $stmt
     * @param $sql
     * @throws SqlMapException
     */
    private function validatePdoStatementAvailable($stmt, $sql)
    {
        if (!isset($stmt) or $stmt == false) {
            $errorInfo = $this->pdo->errorInfo();
            throw new SqlMapException('sql cannot be prepared: "' . $sql . '", error msg: ' . array_pop($errorInfo));
        }
    }


}
