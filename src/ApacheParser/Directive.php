<?php
/**
 * Copyright (c) 2014 Flavius Aspra <flavius.as@gmail.com>
 *
 * @license http://mozilla.org/MPL/2.0/ Mozilla Public License v.2.0
 */

namespace ApacheParser;


use Symfony\Component\Yaml\Exception\RuntimeException;

class Directive
{

    const TYPE_SIMPLE = 1;
    const TYPE_DOUBLE = 2;
    const TYPE_COMMENT = 3;
    const TYPE_NESTED = 4;
    const TYPE_CONTAINER = 6;
    /**
     * @var string the name of the directive
     */
    private $name;
    /**
     * @var string the value of the directive, if any
     */
    private $value;
    /**
     * @var int one of the types above
     */
    private $type;
    /**
     * @var Directive[] for nested structures
     */
    private $childDirectives = [];

    /**
     * @var int $level the nesting level
     */
    private $level;

    public function __construct($type, $level, $name = NULL, $value = NULL, array $children = [])
    {
        $this->setType($type);
        $this->setLevel($level);
        if (in_array($type, [self::TYPE_SIMPLE, self::TYPE_DOUBLE, self::TYPE_NESTED])) {
            $this->name = $name;
        }
        if (in_array($type, [self::TYPE_DOUBLE, self::TYPE_NESTED, self::TYPE_COMMENT])) {
            $this->value = $value;
        }
        $this->setChildDirectives($children);
    }

    /**
     * @param Directive $childDirective
     */
    public function addChildDirective(Directive $childDirective)
    {
        $this->childDirectives[] = $childDirective;
        if($this->getType() == self::TYPE_NESTED) {
            $childDirective->setLevel($this->getLevel() + 1);
        }
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $padding = str_repeat(' ', $this->level * 4);
        $representation = '### ERR';
        switch ($this->getType()) {
            case self::TYPE_SIMPLE:
                $representation = $padding . $this->getName() . PHP_EOL;
                break;
            case self::TYPE_DOUBLE:
                $representation = $padding . $this->getName() . ' ' . $this->getValue() . PHP_EOL;
                break;
            case self::TYPE_COMMENT:
                $representation = $padding . '# ' . $this->getValue() . PHP_EOL;
                break;
            case self::TYPE_NESTED:
                $representation = $padding . '<' . $this->getName();
                if ($this->getValue()) {
                    $representation .= ' ' . $this->getValue();
                }
                $representation .= '>' . PHP_EOL;
                foreach ($this->getChildDirectives() as $subDirective) {
                    $representation .= $padding . (string)$subDirective;
                }
                $representation .= $padding . '</' . $this->getName() . '>' . PHP_EOL;
                break;
            case self::TYPE_CONTAINER:
                $representation = '';
                foreach ($this->getChildDirectives() as $subDirective) {
                    $representation .= $padding . (string)$subDirective;
                }
                break;
            default:
                throw new \RuntimeException('Unknown type');
        }
        return $representation;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!in_array($type, [self::TYPE_SIMPLE, self::TYPE_DOUBLE, self::TYPE_COMMENT, self::TYPE_NESTED, self::TYPE_CONTAINER])) {
            throw new \RuntimeException('Not a valid type');
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return Directive[]
     */
    public function getChildDirectives()
    {
        return $this->childDirectives;
    }

    /**
     * @param Directive[] $childDirectives
     */
    public function setChildDirectives(array $childDirectives)
    {
        if (in_array($this->type, [self::TYPE_NESTED, self::TYPE_CONTAINER])) {
            if(is_array($childDirectives)) {
                foreach ($childDirectives as $child) {
                    $this->addChildDirective($child);
                }
            } else {
                throw new RuntimeException('Attempt to set children for incompatible type');
            }
        }
    }
} 