<?php


namespace MySpotTests;


use MySpot\SqlMapConfig;
use MySpot\SqlMapException;
use MySpot\SqlMapStatement;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * @covers \MySpot\SqlMapConfig
 */
class SqlMapConfigTest extends BaseTestCase
{


    /**
     * @dataProvider dataProvider
     * @param string $path
     * @param PDO $pdoStub
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapConfig::getStatementById
     */
    public function testParseEmptyException(string $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $this->expectExceptionMessage('empty');
        $sqlMapConfig->getStatementById("config.config_test.empty_exception_test");
    }

    /**
     * @dataProvider dataProvider
     * @param string $path
     * @param PDO $pdoStub
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapConfig::getStatementById
     */
    public function testParseConfig(string $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $statement = $sqlMapConfig->getStatementById('config.config_test.select_by_id');
        $this->assertEquals('SELECT * FROM `user` WHERE `id` = :id', $statement->getSqlTemplate());
    }

    /**
     * @dataProvider dataProvider
     * @param string $path
     * @param PDO $pdoStub
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapConfig::getStatementById
     */
    public function testNotExists(string $path, PDO $pdoStub)
    {
        $this->expectExceptionMessage('exists');
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $statement = $sqlMapConfig->getStatementById('config.config_test.not_exists');
    }

    /**
     * @dataProvider dataProvider
     * @param string $path
     * @param PDO $pdoStub
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapConfig::getStatementById
     */
    public function testWrongType(string $path, PDO $pdoStub)
    {
        $this->expectExceptionMessage('wrong');
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $statement = $sqlMapConfig->getStatementById('wrong_type');
    }

    /**
     * @dataProvider dataProvider
     * @param String $path
     * @param PDO $pdoStub
     * @covers       \MySpot\SqlMapConfig::getPdo
     * @covers       \MySpot\SqlMapConfig::getSqlMapConfigPath
     */
    public function testGetter(String $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $this->assertSame($pdoStub, $sqlMapConfig->getPdo());
        $this->assertSame($path, $sqlMapConfig->getSqlMapConfigPath());
    }

    /**
     * @dataProvider dataProvider
     * @param String $path
     * @param PDO $pdoStub
     * @covers       \MySpot\SqlMapConfig::setDefaultResultMapStyle
     * @throws SqlMapException
     */
    public function testSetDefaultResultType(String $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $sqlMapConfig->setDefaultResultMapStyle(SqlMapStatement::MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE);

        $this->expectExceptionMessage('Unknown');
        $sqlMapConfig->setDefaultResultMapStyle(10241);
    }

    /**
     * @dataProvider dataProvider
     * @param String $path
     * @param PDO $pdoStub
     * @covers       \MySpot\SqlMapConfig::__construct
     * @covers       \MySpot\SqlMapConfig::getLogger
     */
    public function testConstruct(String $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $logger = $sqlMapConfig->getLogger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);

//        $this->expectOutputString("this is logger test information");
        $sqlMapConfig->getLogger()->info("this is logger test information");
    }

    public function dataProvider()
    {
        $path = __DIR__ . '/../assets';

        /** @var PDO $pdoStub */
        $pdoStub = $this->getMockBuilder('\PDO')->disableOriginalConstructor()->getMock();

        return [[
            $path, $pdoStub
        ]];
    }

}
