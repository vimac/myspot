<?php


namespace MySpot;


use PDO;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class SqlMapConfig
{

    private $pdo;

    private $sqlMapConfigPath;

    private $logger;

    private $suffixList = ['.php'];

    private $cache = [];

    private $enableDebugLog;

    private $defaultResultMapStyle = SqlMapStatement::MAP_STYLE_DO_NOT_CONVERT;

    private $defaultParameterMapStyle = SqlMapStatement::MAP_STYLE_DO_NOT_CONVERT;

    public function __construct(string $sqlMapConfigPath, PDO $pdo, LoggerInterface $logger = null, bool $enableDebugLog = false)
    {
        $this->sqlMapConfigPath = $sqlMapConfigPath;
        $this->pdo = $pdo;
        $this->enableDebugLog = $enableDebugLog;

        $this->logger = $logger ?? new class extends AbstractLogger
            {
                public function log($level, $message, array $context = array())
                {
                    fwrite(STDOUT, sprintf("[%s] %s %s %s\n",
                            date('Y-m-d H:i:s'),
                            'MySpot.' . strtoupper($level),
                            $message,
                            json_encode($context)
                        )
                    );
                }
            };
    }

    /**
     * @param string $statementId should be a string like 'some_config_file.select' or 'top_config_dir.second_config_dir.some_config_file.select'
     * @return SqlMapStatement
     * @throws SqlMapException
     */
    public function getStatementById(string $statementId): SqlMapStatement
    {
        $ids = explode('.', $statementId);

        if (count($ids) < 1) {
            throw new SqlMapException(sprintf('Error statement id: "%s"', $ids));
        }

        $statementKey = array_pop($ids);
        $notFound = [];

        foreach ($this->suffixList as $suffix) {
            $filename = array_pop($ids) . $suffix;
            $fullFilePath = realpath($this->sqlMapConfigPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $ids) . DIRECTORY_SEPARATOR . $filename);
            $sqlConfig = (function () use ($statementKey, $fullFilePath) {
                if (!isset($this->cache[$fullFilePath])) {
                    $this->cache[$fullFilePath] = @include $fullFilePath;
                }
                return @$this->cache[$fullFilePath][$statementKey];
            })();

            if (empty($sqlConfig)) {
                $notFound[] = $fullFilePath;
                continue;
            }

            if (!is_array($sqlConfig)) {
                throw new SqlMapException(sprintf("Wrong return type, excepted an array, but retrieved a %s: key [%s] in %s", gettype($sqlConfig), $statementKey, $fullFilePath));
            }

            $statement = new SqlMapStatement($sqlConfig);

            if ($statement->getResultMapStyle() == SqlMapStatement::MAP_STYLE_FOLLOW_DEFAULT) {
                $statement->setResultMapStyle($this->defaultResultMapStyle);
            }

            return $statement;
        }

        if (!empty($notFound)) {
            throw new SqlMapException(sprintf("Config '%s' not found or empty in these files: %s", $statementKey, implode(",", $notFound)));
        }
    }

    public function getSqlMapConfigPath()
    {
        return $this->sqlMapConfigPath;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getEnableDebugLog(): bool
    {
        return $this->enableDebugLog;
    }

    /**
     * @param int $resultMapStyle
     * @throws SqlMapException
     */
    public function setDefaultResultMapStyle(int $resultMapStyle): void
    {
        if (!in_array(
            $resultMapStyle,
            [
                SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE,
                SqlMapStatement::MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE,
                SqlMapStatement::MAP_STYLE_DO_NOT_CONVERT
            ])) {
            throw new SqlMapException("Unknown result map style");
        }
        $this->defaultResultMapStyle = $resultMapStyle;
    }


    /**
     * Set the map config file suffix extension name
     * @param array $suffixList something similar to [".php", ".inc"]
     * @throws SqlMapException
     */
    public function setSuffixList(array $suffixList): void
    {
        foreach ($suffixList as $suffix) {
            if (!is_string($suffix) or $suffix[0] != '.') {
                throw new SqlMapException("Unsupported suffixList: " . json_encode($suffixList));
            }
        }
        $this->suffixList = $suffixList;
    }

}