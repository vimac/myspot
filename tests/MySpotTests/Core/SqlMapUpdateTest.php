<?php


namespace MySpotTests\Core;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpot\SqlMapConst;
use MySpot\SqlMapException;

class SqlMapUpdateTest extends SqlMapAbstractTest
{
    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMap::update
     */
    public function testUpdateAll(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->update('config.update.update_all', [], [
                'deleted_at' => [$date]
            ]
        );

        $this->assertEquals(8, $result->getAffectedLines());
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMap::update
     */
    public function testUpdateWithIn(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->update('config.update.update_with_in',
            [
                'id' => [[1, 2], SqlMapConst::PARAM_INT]
            ], [
                'deleted_at' => [$date]
            ]
        );

        $this->assertEquals(2, $result->getAffectedLines());

        $sqlResult = $sqlMap->select('config.select.select_in_id_as_anonymous_class', [
                'id' => [[1, 3]],
            ]
        );
        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first->id);
        $this->assertEquals($date, $first->deletedAt);

        $second = $sqlResult->fetch();
        $this->assertEquals(3, $second->id);
        $this->assertEquals('0000-00-00 00:00:00', $second->deletedAt);
    }

    /**
     * @dataProvider dataProvider
     * @param SqlMapConfig $config
     * @throws SqlMapException
     * @covers       \MySpot\SqlMap::update
     */
    public function testUpdateWithConditionIn(SqlMapConfig $config)
    {
        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->update('config.update.update_with_condition_in',
            [
                'id' => [[1, 2], SqlMapConst::PARAM_INT]
            ], [
                'deleted_at' => [$date]
            ]
        );

        $this->assertEquals(2, $result->getAffectedLines());

        $sqlResult = $sqlMap->select('config.select.select_in_id_as_anonymous_class', [
                'id' => [[1, 3]],
            ]
        );
        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first->id);
        $this->assertEquals($date, $first->deletedAt);

        $second = $sqlResult->fetch();
        $this->assertEquals(3, $second->id);
        $this->assertEquals('0000-00-00 00:00:00', $second->deletedAt);

        $sqlMap = new SqlMap($config);
        $date = date('Y-m-d H:i:s');
        $result = $sqlMap->update('config.update.update_with_condition_in', [], [
                'deleted_at' => [$date]
            ]
        );

        $this->assertEquals(8, $result->getAffectedLines());

        $sqlResult = $sqlMap->select('config.select.select_in_id_as_anonymous_class', [
                'id' => [[1, 3]],
            ]
        );
        $first = $sqlResult->fetch();
        $this->assertEquals(1, $first->id);
        $this->assertEquals($date, $first->deletedAt);

        $second = $sqlResult->fetch();
        $this->assertEquals(3, $second->id);
        $this->assertEquals($date, $second->deletedAt);
        $this->assertEquals($date, $second->deletedAt);
    }

}
