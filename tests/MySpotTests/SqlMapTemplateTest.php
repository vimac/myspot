<?php


namespace MySpotTests;


use MySpot\SqlMapException;
use MySpot\SqlMapTemplate;

/**
 * @covers \MySpot\SqlMapTemplate
 */
class SqlMapTemplateTest extends BaseTestCase
{
    public function testBasic()
    {
        $template = new SqlMapTemplate(":a :b");
        $this->assertContains('a', $template->getParsedNormalVariables());
        $this->assertContains('b', $template->getParsedNormalVariables());
    }

    /**
     * @throws SqlMapException
     */
    public function testConditions()
    {
        $template = new SqlMapTemplate(":test?{some}:test?{thing}");
        $this->assertNotContains('test', $template->getParsedNormalVariables());
        list($sql,) = $template->render([]);
        $this->assertEquals('', $sql);
        list($sql,) = $template->render(['test' => [1]]);
        $this->assertEquals('some        thing', $sql);
        list($sql,) = $template->render(['test' => [null]]);
        $this->assertEquals('', $sql);
    }

    public function testUnexpectedVariableName()
    {
        $this->expectExceptionMessage('Unexpected variable name');
        $template = new SqlMapTemplate(": test");
    }

    public function testUnexpectedCurlyBrace()
    {
        $this->expectExceptionMessage('Unexpected curly brace');
        $template = new SqlMapTemplate(":test{}");
    }

    public function testUnexpectedCurlyBrace2()
    {
        $this->expectExceptionMessage('Unexpected curly brace');
        $template = new SqlMapTemplate(":{}");
    }

    public function testUnexpectedNestingCondition()
    {
        $this->expectExceptionMessage('Nesting condition not support');
        $template = new SqlMapTemplate(":test?{{}}");
    }

    public function testUnexpectedQuestionMark()
    {
        $this->expectExceptionMessage('Unexpected char');
        $template = new SqlMapTemplate('select ?');
    }

    public function testComplexSql()
    {
        $template = new SqlMapTemplate("select * from `user` where id = :uid :enablePage?{limit :offset, :rows}");
        list($sql) = $template->render([]);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $this->assertEquals('select * from `user` where id = :uid', $sql);

        list($sql) = $template->render(['enablePage' => [true]]);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $this->assertEquals('select * from `user` where id = :uid limit :offset, :rows', $sql);
    }
}
