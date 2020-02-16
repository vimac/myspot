<?php


namespace MySpotTests\Core;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpot\SqlMapException;
use MySpotTests\TestDataObject\TestUserDO;
use stdClass;

class SqlMapSelectTest extends SqlMapAbstractTest
{

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSimpleSelect(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_by_id', [
                'id' => [1]
            ]
        );
        $r = $sqlResult->fetch();
        $this->assertInstanceOf(stdClass::class, $r);
        $this->assertObjectHasAttribute('id', $r);
        $this->assertSame(1, intval($r->id));
    }


    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSimpleSelectOptional(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_by_gender_optional', [
                'gender' => [1],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $sqlResult = $sqlMap->select('config.select.select_by_gender_optional');
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(8, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSimpleSelectIn(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id', [
                'id' => [[1, 2, 3, 4]],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $sqlResult = $sqlMap->select('config.select.select_in_id', [
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(8, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSimpleSelectWithConditions(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_by_gender_with_pagination', [
                'gender' => [1],
                'pagination' => [1],
                'offset' => [0],
                'rows' => [2],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $sqlResult = $sqlMap->select('config.select.select_by_gender_with_pagination', [
                'gender' => [1],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
    }


    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSelectInWithPagination(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_with_pagination', [
                'id' => [[1, 2, 3, 4]],
                'pagination' => [1],
                'offset' => [0],
                'rows' => [2],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_with_pagination', [
                'id' => [[1, 2, 3, 4]],
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_with_pagination', [
            ]
        );
        $result = $sqlResult->fetchAll();
        $this->assertIsArray($result);
        $this->assertCount(8, $result);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSelectCount(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_count');
        $r = $sqlResult->fetchColumn();
        $this->assertEquals(8, $r);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSelectInAsNamedClass(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_as_named_class', [
                'id' => [[1, 2]],
            ]
        );

        /** @var TestUserDO $first */
        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first->getId());
        $this->assertEquals('赵', $first->getName());
        $this->assertEquals('0000-00-00 00:00:00', $first->getDeletedAt());

        $second = $sqlResult->fetch();
        $this->assertEquals(2, $second->getId());
        $this->assertEquals('钱', $second->getName());
        $this->assertEquals('0000-00-00 00:00:00', $second->getDeletedAt());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSelectInAsAnonymousClass(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_as_anonymous_class', [
                'id' => [[1, 2]],
            ]
        );

        /** @var stdClass $first */
        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first->id);
        $this->assertEquals('赵', $first->name);
        $this->assertEquals('0000-00-00 00:00:00', $first->deletedAt);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::select
     */
    public function testSelectInAsArray(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $sqlResult = $sqlMap->select('config.select.select_in_id_as_array', [
                'id' => [[1, 2]],
            ]
        );

        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first['id']);
        $this->assertEquals('赵', $first['name']);
        $this->assertEquals('0000-00-00 00:00:00', $first['deletedAt']);
    }

}