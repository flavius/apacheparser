<?php
/**
 * Copyright (c) 2014 Flavius Aspra <flavius.as@gmail.com>
 *
 * @license http://mozilla.org/MPL/2.0/ Mozilla Public License v.2.0
 */

namespace ApacheParser;

use Testunit\TestCase;

class DirectiveTreeGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function line_type_simple_directive()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_SIMPLE, $generator->getLineType('helloworld'));
        $this->assertEquals(Directive::TYPE_SIMPLE, $generator->getDirectiveForLine('helloworld')->getType());
    }

    /**
     * @test
     */
    public function line_type_simple_directive_with_indentation()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_SIMPLE, $generator->getLineType('  helloworld'));
        $this->assertEquals(Directive::TYPE_SIMPLE, $generator->getDirectiveForLine('  helloworld')->getType());
    }

    /**
     * @test
     */
    public function line_type_double_directive()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_DOUBLE, $generator->getLineType('hello world'));
        $directive = $generator->getDirectiveForLine('hello world out there');
        $this->assertEquals(Directive::TYPE_DOUBLE, $directive->getType());
        $this->assertEquals('hello', $directive->getName());
        $this->assertEquals('world out there', $directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_double_directive_with_indentation()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_DOUBLE, $generator->getLineType('  hello world'));
        $directive = $generator->getDirectiveForLine('   hello world ');
        $this->assertEquals(Directive::TYPE_DOUBLE, $directive->getType());
        $this->assertEquals('hello', $directive->getName());
        $this->assertEquals('world', $directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_comment_directive()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_COMMENT, $generator->getLineType('# helloworld'));
        $directive = $generator->getDirectiveForLine('# hello world  ');
        $this->assertEquals(Directive::TYPE_DOUBLE, $directive->getType());
        $this->assertNull($directive->getName());
        $this->assertEquals('hello world', $directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_comment_directive_with_indentation()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(Directive::TYPE_COMMENT, $generator->getLineType('  # helloworld'));
    }

    /**
     * @test
     */
    public function line_type_nested_start()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $generator->getLineType('<foo>'));
        $directive = $generator->getDirectiveForLine('<foo>');
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $directive->getType());
        $this->assertEquals('foo', $directive->getName());
        $this->assertNull($directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_nested_start_indented()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $generator->getLineType(' <foo>'));
        $directive = $generator->getDirectiveForLine(' <foo>');
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $directive->getType());
        $this->assertEquals('foo', $directive->getName());
        $this->assertNull($directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_nested_start_with_value()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $generator->getLineType('<foo 42>'));
        $directive = $generator->getDirectiveForLine('<foo 42>');
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $directive->getType());
        $this->assertEquals('foo', $directive->getName());
        $this->assertEquals(42, $directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_nested_start_with_value_indented()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $generator->getLineType(' <foo 42>'));
        $directive = $generator->getDirectiveForLine('  <foo 42>');
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_START, $directive->getType());
        $this->assertEquals('foo', $directive->getName());
        $this->assertEquals(42, $directive->getValue());
    }

    /**
     * @test
     */
    public function line_type_nested_end()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_END, $generator->getLineType('</foo>'));
    }

    /**
     * @test
     */
    public function line_type_nested_end_indented()
    {
        $generator = new DirectiveTreeGenerator();
        $this->assertEquals(DirectiveTreeGenerator::TYPE_NESTED_END, $generator->getLineType(' </foo>'));
    }

}