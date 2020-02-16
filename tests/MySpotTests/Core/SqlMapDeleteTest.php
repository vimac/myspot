<?php


namespace MySpotTests\Core;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpot\SqlMapConst;
use MySpot\SqlMapException;

class SqlMapDeleteTest extends SqlMapAbstractTest
{
    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::delete
     */
    public function testDeleteAll(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $result = $sqlMap->delete('config.delete.delete_all');

        $this->assertEquals(8, $result->getAffectedLines());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::delete
     */
    public function testDeleteById(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $result = $sqlMap->delete('config.delete.delete_by_id',
            [
                'id' => [1, SqlMapConst::PARAM_INT]
            ]
        );
        $this->assertEquals(1, $result->getAffectedLines());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers \MySpot\SqlMap::delete
     */
    public function testDeleteWithConditionIn(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $result = $sqlMap->delete('config.delete.delete_with_condition_in',
            [
                'id' => [[1, 2, 3], SqlMapConst::PARAM_INT]
            ]
        );
        $this->assertEquals(3, $result->getAffectedLines());

        $sqlResult = $sqlMap->select('config.select.select_count');
        $r = $sqlResult->fetchColumn();
        $this->assertEquals(5, $r);

        $result = $sqlMap->delete('config.delete.delete_with_condition_in');
        $this->assertEquals(5, $result->getAffectedLines());

        $sqlResult = $sqlMap->select('config.select.select_count');
        $r = $sqlResult->fetchColumn();
        $this->assertEquals(0, $r);
    }

}