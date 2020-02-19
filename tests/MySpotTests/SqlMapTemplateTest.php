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
        $this->assertEquals('something', $sql);
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

    public function testUnexpectedNestingCondition2()
    {
        $this->expectExceptionMessage('Nesting condition not support');
        $template = new SqlMapTemplate(":test?{:test2?{}}");
    }

    public function testUnexpectedQuestionMark()
    {
        $this->expectExceptionMessage('Unexpected char');
        $template = new SqlMapTemplate('select ?');
    }

    public function testInAndCondition()
    {
        $template = new SqlMapTemplate(':a::b?{hello}');
        list($sql) = $template->render([
            'a' => [[1, 2]],
            'b' => [true],
        ]);
        $this->assertEquals(
            sprintf('(:%s, :%s)hello', SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 0, SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 1), $sql);

        $template = new SqlMapTemplate(':a::b?{hello}:c::d?{world}');
        list($sql) = $template->render([
            'a' => [[1, 2]],
            'b' => [true],
            'c' => [[1, 2, 3]]
        ]);
        $this->assertEquals(
            sprintf(
                '(:%s, :%s)hello(:%s, :%s, :%s)',
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 2
            )
            , $sql);

        $template = new SqlMapTemplate(':b?{:a:, :c:}');
        $val = $template->getVariables();
        $expectJson = '[["b",1,0,2,3,9],["a",2,4,2],["c",2,9,2]]';
        $this->assertEquals($expectJson, json_encode($val));
        list($sql) = $template->render([
            'b' => [true],
            'a' => [[1, 2]],
            'c' => [[1, 2, 3]]
        ]);
        $this->assertEquals(
            sprintf(
                '(:%s, :%s), (:%s, :%s, :%s)',
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 2
            )
            , $sql);

        $template = new SqlMapTemplate(':b?{:a:, :c:} :d?{world}');
        $val = $template->getVariables();
        $expectJson = '[["b",1,0,2,3,9],["a",2,4,2],["c",2,9,2],["d",1,14,2,17,6]]';
        $this->assertEquals($expectJson, json_encode($val));
        list($sql) = $template->render([
            'b' => [true],
            'a' => [[1, 2]],
            'c' => [[1, 2, 3]],
            'd' => [true]
        ]);
        $this->assertEquals(
            sprintf(
                '(:%s, :%s), (:%s, :%s, :%s) world',
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('a') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 0,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 1,
                SqlMapTemplate::GENERATED_VAR_PREFIX . ucfirst('c') . 2
            )
            , $sql);
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
