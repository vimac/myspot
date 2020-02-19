<?php


namespace MySpotTests;


use MySpot\SqlMapStatement;

class SqlMapStatementTest extends BaseTestCase
{

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setSqlTemplate
     * @covers \MySpot\SqlMapStatement::getSqlTemplate
     */
    public function testSetSql()
    {
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1'
        ]);
        $this->assertEquals('select 1', $sqlMapStatementTest->getSqlTemplate());
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setSqlTemplate
     */
    public function testSetSqlFailure()
    {
        $this->expectExceptionMessage('empty');
        $sqlMapStatementTest = new SqlMapStatement([]);
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setResultType
     * @covers \MySpot\SqlMapStatement::getResultType
     */
    public function testSetResultType()
    {
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1',
            'resultType' => \stdClass::class
        ]);
        $this->assertEquals(\stdClass::class, $sqlMapStatementTest->getResultType());
    }


    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setResultType
     * @covers \MySpot\SqlMapStatement::getResultType
     */
    public function testSetResultTypeFailure()
    {
        $this->expectExceptionMessage('unknown');
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1',
            'resultType' => 'unknown'
        ]);
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setResultType
     * @covers \MySpot\SqlMapStatement::getResultType
     */
    public function testSetResultTypeArray()
    {
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1',
            'resultType' => 'array'
        ]);
        $this->assertEquals('array', $sqlMapStatementTest->getResultType());
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setResultMapStyle
     * @covers \MySpot\SqlMapStatement::getResultMapStyle
     */
    public function testSetResultMapStyle()
    {
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1',
            'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
        ]);
        $this->assertEquals(SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE, $sqlMapStatementTest->getResultMapStyle());
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::setResultMapStyle
     */
    public function testSetResultMapStyleFailure()
    {
        $this->expectExceptionMessage('Unknown result map');
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1',
            'resultMapStyle' => 10240
        ]);
    }

    /**
     * @throws \MySpot\SqlMapException
     * @covers \MySpot\SqlMapStatement::__construct
     */
    public function testConstructor()
    {
        $sqlMapStatementTest = new SqlMapStatement([
            'sql' => 'select 1'
        ]);
        $this->assertEquals(SqlMapStatement::MAP_STYLE_FOLLOW_DEFAULT, $sqlMapStatementTest->getResultMapStyle());
        $this->assertEquals('object', $sqlMapStatementTest->getResultType());
    }
}
