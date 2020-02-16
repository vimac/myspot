<?php


namespace MySpotTests\Core;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpot\SqlMapException;
use MySpot\SqlMapResult;
use MySpot\SqlMapStatement;
use MySpotTests\TestDataObject\TestUserDO;

class SqlMapResultTest extends SqlMapAbstractTest
{
    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapResult
     */
    public function testArrayResult(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $pdo = $sqlMap->getPdo();
        $sql = 'select * from `user`';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $sqlMapStatement = new SqlMapStatement(['sql' => $sql, 'resultType' => 'array']);
        $sqlMapStatement->setResultMapStyle(SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);

        $result = new SqlMapResult($pdo, $statement, $sql, $sqlMapStatement, true);

        $this->assertTrue($result->getExecutedResult());
        $this->assertEquals(0, $result->getAffectedLines());
        $fetched = $result->fetchAll();
        $this->assertIsArray($fetched);
        $this->assertCount(8, $fetched);

        $this->assertIsArray(current($fetched));
        $this->assertEquals(1, current($fetched)['id']);
        $this->assertEquals('赵', current($fetched)['name']);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapResult
     */
    public function testAnonymousObjectResult(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $pdo = $sqlMap->getPdo();
        $sql = 'select * from `user`';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $sqlMapStatement = new SqlMapStatement(['sql' => $sql, 'resultType' => 'object']);
        $sqlMapStatement->setResultMapStyle(SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);

        $result = new SqlMapResult($pdo, $statement, $sql, $sqlMapStatement, true);

        $this->assertTrue($result->getExecutedResult());
        $this->assertEquals(0, $result->getAffectedLines());
        $fetched = $result->fetchAll();
        $this->assertIsArray($fetched);
        $this->assertCount(8, $fetched);

        $this->assertIsObject(current($fetched));
        $this->assertEquals(1, current($fetched)->id);
        $this->assertEquals('赵', current($fetched)->name);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMapResult
     */
    public function testObjectResult(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $pdo = $sqlMap->getPdo();
        $sql = 'select * from `user`';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $sqlMapStatement = new SqlMapStatement(['sql' => $sql, 'resultType' => TestUserDO::class]);
        $sqlMapStatement->setResultMapStyle(SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);

        $result = new SqlMapResult($pdo, $statement, $sql, $sqlMapStatement, true);

        $this->assertTrue($result->getExecutedResult());
        $this->assertEquals(0, $result->getAffectedLines());
        $fetched = $result->fetchAll();
        $this->assertIsArray($fetched);
        $this->assertCount(8, $fetched);

        $this->assertInstanceOf(TestUserDO::class, current($fetched));
        $this->assertEquals(1, current($fetched)->getId());
        $this->assertEquals('赵', current($fetched)->getName());
    }

}