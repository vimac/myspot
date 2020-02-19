<?php


namespace MySpotTests;


use MySpot\SqlMapConst;
use PDO;

/**
 * @covers \MySpot\SqlMapConst
 */
class SqlMapConstTest extends BaseTestCase
{

    public function test()
    {
        $this->assertEquals(SqlMapConst::PARAM_BOOL, PDO::PARAM_BOOL);
        $this->assertEquals(SqlMapConst::PARAM_STR, PDO::PARAM_STR);
        $this->assertEquals(SqlMapConst::PARAM_INT, PDO::PARAM_INT);
        $this->assertEquals(SqlMapConst::PARAM_INPUT_OUTPUT, PDO::PARAM_INPUT_OUTPUT);
        $this->assertEquals(SqlMapConst::PARAM_LOB, PDO::PARAM_LOB);
        $this->assertEquals(SqlMapConst::PARAM_NULL, PDO::PARAM_NULL);
        $this->assertEquals(SqlMapConst::PARAM_STMT, PDO::PARAM_STMT);
        $this->assertEquals(SqlMapConst::PARAM_STR_CHAR, PDO::PARAM_STR_CHAR);
        $this->assertEquals(SqlMapConst::PARAM_STR_NATL, PDO::PARAM_STR_NATL);
    }

}
