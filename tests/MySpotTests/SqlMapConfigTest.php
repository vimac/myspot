<?php


namespace MySpotTests;


use MySpot\SqlMapConfig;
use MySpot\SqlMapException;
use PDO;

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
     */
    public function testParseEmptyException(string $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $this->expectExceptionMessageMatches('/empty/');
        $sqlMapConfig->getStatementById("config.config_test.empty_exception_test");
    }

    /**
     * @dataProvider dataProvider
     * @param string $path
     * @param PDO $pdoStub
     * @throws SqlMapException
     */
    public function testParseConfig(string $path, PDO $pdoStub)
    {
        $sqlMapConfig = new SqlMapConfig($path, $pdoStub);
        $statement = $sqlMapConfig->getStatementById('config.config_test.select_by_id');
        $this->assertEquals('SELECT * FROM `user` WHERE `id` = :id', $statement->getSqlTemplate());
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