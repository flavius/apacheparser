<?php
/**
 * Copyright (c) 2014 Flavius Aspra <flavius.as@gmail.com>
 *
 * @license http://mozilla.org/MPL/2.0/ Mozilla Public License v.2.0
 */

namespace ApacheParser;

use Testunit\TestCase;

class DirectiveTest extends TestCase
{
    /**
     * @test
     */
    public function simple_directive()
    {
        $directive = new Directive(Directive::TYPE_SIMPLE, 0, 'foo');
        $this->assertEquals('foo' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function simple_directive_with_indentation()
    {
        $directive = new Directive(Directive::TYPE_SIMPLE, 1, 'foo');
        $this->assertEquals('    foo' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function double_directive()
    {
        $directive = new Directive(Directive::TYPE_DOUBLE, 0, 'foo', 'bar');
        $this->assertEquals('foo bar' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function double_directive_with_indentation()
    {
        $directive = new Directive(Directive::TYPE_DOUBLE, 1, 'foo', 'bar');
        $this->assertEquals('    foo bar' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function comment_directive()
    {
        $directive = new Directive(Directive::TYPE_COMMENT, 0, 'foo', 'bar');
        $this->assertEquals('# bar' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function comment_directive_with_indentation()
    {
        $directive = new Directive(Directive::TYPE_COMMENT, 1, 'foo', 'bar');
        $this->assertEquals('    # bar' . PHP_EOL, (string)$directive);
    }

    /**
     * @test
     */
    public function container_directive_linear()
    {
        $expected = <<<HERE
foo
hello world

HERE;

        $fooDirective = new Directive(Directive::TYPE_SIMPLE, 0, 'foo');
        $helloWorldDirective = new Directive(Directive::TYPE_DOUBLE, 0, 'hello', 'world');
        $container = new Directive(Directive::TYPE_CONTAINER, 0, NULL, NULL, array($fooDirective, $helloWorldDirective));

        $this->assertEquals($expected, (string)$container);
    }

    /**
     * @test
     */
    public function container_directive_linear_indented()
    {
        $this->markTestIncomplete("FIX");
        $expected = <<<HERE
    foo
    hello world

HERE;

        $fooDirective = new Directive(Directive::TYPE_SIMPLE, 0, 'foo');
        $helloWorldDirective = new Directive(Directive::TYPE_DOUBLE, 0, 'hello', 'world');
        $container = new Directive(Directive::TYPE_CONTAINER, 1, NULL, NULL, array($fooDirective, $helloWorldDirective));

        $this->assertEquals($expected, (string)$container);
    }

    /**
     * @test
     */
    public function container_directive_linear_double_indented()
    {
        $this->markTestIncomplete("FIX");
        $expected = <<<HERE
    foo
        hello world

HERE;

        $fooDirective = new Directive(Directive::TYPE_SIMPLE, 0, 'foo');
        $helloWorldDirective = new Directive(Directive::TYPE_DOUBLE, 1, 'hello', 'world');
        $container = new Directive(Directive::TYPE_CONTAINER, 1, NULL, NULL, array($fooDirective, $helloWorldDirective));

        $this->assertEquals($expected, (string)$container);
    }

    /**
     * @test
     */
    public function nested_empty()
    {
        $expected = <<<HERE
<foo>
</foo>

HERE;
        $fooDirective = new Directive(Directive::TYPE_NESTED, 0, 'foo');
        $this->assertEquals($expected, (string)$fooDirective);
    }

    /**
     * @test
     */
    public function nested_empty_with_value()
    {
        $expected = <<<HERE
<foo *:80>
</foo>

HERE;
        $fooDirective = new Directive(Directive::TYPE_NESTED, 0, 'foo', '*:80');
        $this->assertEquals($expected, (string)$fooDirective);
    }

    /**
     * @test
     */
    public function nested_empty_indented()
    {
        $expected = <<<HERE
    <foo>
    </foo>

HERE;
        $fooDirective = new Directive(Directive::TYPE_NESTED, 1, 'foo');
        $this->assertEquals($expected, (string)$fooDirective);
    }

    /**
     * @test
     */
    public function nested_with_children()
    {
        $expected = <<<HERE
<foo>
    # TO DO
</foo>

HERE;
        $commentDirective = new Directive(Directive::TYPE_COMMENT, 0, NULL, 'TO DO');
        $fooDirective = new Directive(Directive::TYPE_NESTED, 0, 'foo', NULL, array($commentDirective));
        $this->assertEquals($expected, (string)$fooDirective);
    }
}
