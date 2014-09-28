<?php
/**
 * Copyright (c) 2014 Flavius Aspra <flavius.as@gmail.com>
 *
 * @license http://mozilla.org/MPL/2.0/ Mozilla Public License v.2.0
 */

namespace ApacheParser;


use Symfony\Component\Yaml\Exception\RuntimeException;

class DirectiveTreeGenerator
{
    const TYPE_NESTED_START = 4;
    const TYPE_NESTED_END = 5;
    /**
     * @var Directive[]
     */
    private $directiveStack;

    public function __construct($rootType = Directive::TYPE_CONTAINER, $name = NULL, $value = NULL)
    {
        //$this->directiveStack[] = new Directive($rootType, 0, $name, $value);
    }

    public function feedLine($stringLine)
    {
        if(!trim($stringLine)) {
            return NULL;
        }
        switch ($this->getLineType($stringLine)) {
            case self::TYPE_NESTED_END:
                array_pop($this->directiveStack);
                break;
            case self::TYPE_NESTED_START:
                $topDirective = $this->deepestDirective();
                $newDirective = $this->getDirectiveForLine($stringLine);
                if($topDirective) {
                    $topDirective->addChildDirective($newDirective);
                }
                $this->directiveStack[] = $newDirective;
                break;
            default:
                $topDirective = $this->deepestDirective();
                $newDirective = $this->getDirectiveForLine($stringLine);
                $topDirective->addChildDirective($newDirective);
                break;
        }
    }

    public function getLineType($stringLine)
    {
        $trimmed = trim($stringLine);
        $firstChar = substr($trimmed, 0, 1);
        $secondChar = substr($trimmed, 1, 1);
        if ($firstChar == '<') {
            if ($secondChar == '/') {
                return self::TYPE_NESTED_END;
            } else {
                return self::TYPE_NESTED_START;
            }
        } elseif ($firstChar == '#') {
            return Directive::TYPE_COMMENT;
        }
        $spaceInside = preg_match('/\s/', $trimmed);
        if ($spaceInside === 1) {
            return Directive::TYPE_DOUBLE;
        } elseif ($spaceInside === 0) {
            return Directive::TYPE_SIMPLE;
        } else {
            throw new RuntimeException('Unrecognized type for " ' . $trimmed . '"');
        }
    }

    /**
     * @return Directive
     */
    public function deepestDirective()
    {
        if(isset($this->directiveStack[count($this->directiveStack) - 1])) {
            return $this->directiveStack[count($this->directiveStack) - 1];
        }
        return NULL;
    }

    /**
     * @param $stringLine
     * @return Directive
     */
    public function getDirectiveForLine($stringLine)
    {
        $trimmedLine = trim($stringLine);
        switch ($this->getLineType($stringLine)) {
            case Directive::TYPE_SIMPLE:
                return new Directive(Directive::TYPE_SIMPLE, count($this->directiveStack), $trimmedLine);
            case Directive::TYPE_DOUBLE:
                $components = preg_split('/\s/', $trimmedLine, 2);
                $name = $components[0];
                $value = $components[1];
                return new Directive(Directive::TYPE_DOUBLE, count($this->directiveStack), $name, $value);
            case Directive::TYPE_COMMENT:
                preg_match('/#\s+(?P<comment>.+)/', $trimmedLine, $matches);
                return new Directive(Directive::TYPE_DOUBLE, count($this->directiveStack), NULL, $matches['comment']);
            case self::TYPE_NESTED_START:
                $default = array('value' => NULL);
                preg_match('/<(?P<name>[^\s]+)(\s+(?P<value>[^>]+))?>/', $trimmedLine, $matches);
                $matches += $default;
                return new Directive(self::TYPE_NESTED_START, count($this->directiveStack), $matches['name'], $matches['value']);
            default:
                throw new RuntimeException('Cannot create directive, invalid type');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getRootDirective();
    }

    /**
     * @return Directive
     */
    public function getRootDirective()
    {
        return $this->directiveStack[0];
    }
} 
