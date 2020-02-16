<?php


namespace MySpotTests;


use MySpot\SqlMapMapFunctions;
use MySpot\SqlMapStatement;

class SqlMapFunctionsTest extends BaseTestCase
{

    /**
     * @covers \MySpot\SqlMapMapFunctions::convertUnderScoreToLowerCase
     */
    public function testUnderScoreToLowerCase()
    {
        $func = SqlMapMapFunctions::getFunction(SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);
        $word = $func('hello_wonderful_world');
        $this->assertEquals('helloWonderfulWorld', $word);
    }

    /**
     * @covers \MySpot\SqlMapMapFunctions::convertLowerCaseToUnderScore
     */
    public function testLowerCaseToUnderScore()
    {
        $func = SqlMapMapFunctions::getFunction(SqlMapStatement::MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE);
        $word = $func('helloWonderfulWorld');
        $this->assertEquals('hello_wonderful_world', $word);
    }

}