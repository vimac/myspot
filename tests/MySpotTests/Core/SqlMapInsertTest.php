<?php


namespace MySpotTests\Core;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpot\SqlMapException;

class SqlMapInertTest extends SqlMapAbstractTest
{
    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::insert
     */
    public function testInsert(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->insert('config.insert.insert', [
                'name' => ['冯'],
                'gender' => [1],
                'created_at' => [$date],
                'deleted_at' => ['0000-00-00 00:00:00'],
            ]
        );

        $this->assertEquals(1, $result->getAffectedLines());
        $this->assertEquals(9, $result->getLastInsertId());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::insert
     */
    public function testInsert2(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->insert('config.insert.insert2', [
                'name' => ['冯'],
                'gender' => [1],
                'createdAt' => [$date]
            ]
        );

        $this->assertEquals(1, $result->getAffectedLines());
        $this->assertEquals(9, $result->getLastInsertId());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::insertBatch
     */
    public function testBatchInsert(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->insertBatch('config.insert.insert', [
                [
                    'name' => ['冯'],
                    'gender' => [1],
                    'created_at' => [$date],
                    'deleted_at' => ['0000-00-00 00:00:00'],
                ],
                [
                    'name' => ['陈'],
                    'gender' => [2],
                    'created_at' => [$date],
                    'deleted_at' => ['0000-00-00 00:00:00'],
                ],
                [
                    'name' => ['诸'],
                    'gender' => [1],
                    'created_at' => [$date],
                    'deleted_at' => ['0000-00-00 00:00:00'],
                ],
                [
                    'name' => ['卫'],
                    'gender' => [2],
                    'created_at' => [$date],
                    'deleted_at' => ['0000-00-00 00:00:00'],
                ],
            ]
        );

        $this->assertEquals(4, $result->getAffectedLines());
        $this->assertEquals(12, $result->getLastInsertId());

        $sqlResult = $sqlMap->select('config.select.select_count');
        $r = $sqlResult->fetchColumn();
        $this->assertEquals(12, $r);
    }


}
