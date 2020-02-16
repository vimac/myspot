<?php


namespace MySpot;


class SqlMapStatement
{
    /**
     * Do not convert
     */
    public const MAP_STYLE_DO_NOT_CONVERT = 0;

    /**
     * Convert fetched column name separated with underscore to lower camelcase
     */
    public const MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE = 1;

    /**
     * Convert fetched column name with lower camelcase to underscore
     */
    public const MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE = 2;

    /**
     * Follow the default from SqlMapConfig
     */
    public const MAP_STYLE_FOLLOW_DEFAULT = 1000;

    /**
     * @var array
     * original data from sql map configuration
     */
    private $originalConfig;

    /**
     * @var string
     */
    private $sqlTemplate;

    /**
     * @var string
     */
    private $resultType;

    /**
     * @var integer
     */
    private $resultMapStyle;

    /**
     * StatementConfig constructor.
     * @param array $config
     * @throws SqlMapException
     */
    public function __construct(array $config)
    {
        $this->setSqlTemplate($config['sql'] ?? '');
        $this->setResultMapStyle($config['resultMapStyle'] ?? self::MAP_STYLE_FOLLOW_DEFAULT);
        $this->setResultType($config['resultType'] ?? 'object');
        $this->originalConfig = $config;
    }

    /**
     * @return string
     */
    public function getSqlTemplate(): string
    {
        return $this->sqlTemplate;
    }

    /**
     * @return string
     */
    public function getResultType(): string
    {
        return $this->resultType;
    }

    /**
     * @return int
     */
    public function getResultMapStyle(): int
    {
        return $this->resultMapStyle;
    }

    /**
     * @param string $sql
     * @throws SqlMapException
     */
    protected function setSqlTemplate(string $sql): void
    {
        if (empty($sql)) {
            throw new SqlMapException("sql cannot be empty");
        }
        $this->sqlTemplate = $sql;
    }

    /**
     * @param string $resultType , allowed value: array or a class name
     * @throws SqlMapException when cannot resolve result type as array or a class name
     */
    protected function setResultType(string $resultType): void
    {
        if ($resultType != 'array' and $resultType != 'class' and $resultType != 'object' and !class_exists($resultType, true)) {
            throw new SqlMapException(sprintf("Unknown result type: %s", $resultType));
        }
        $this->resultType = $resultType;
    }

    /**
     * @param int $resultMapStyle
     * @throws SqlMapException
     */
    public function setResultMapStyle(int $resultMapStyle): void
    {
        if (!in_array(
            $resultMapStyle,
            [
                SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE,
                SqlMapStatement::MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE,
                SqlMapStatement::MAP_STYLE_DO_NOT_CONVERT,
                SqlMapStatement::MAP_STYLE_FOLLOW_DEFAULT
            ])) {
            throw new SqlMapException("Unknown result map style");
        }
        $this->resultMapStyle = $resultMapStyle;
    }

}