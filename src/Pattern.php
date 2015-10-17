<?php

namespace Robier\Router;

use Robier\Router\Contract\PatternInterface;

/**
 * Class Pattern
 * @package Robier\Router
 */
class Pattern implements PatternInterface
{
    /**
     * @var array List of strict patterns
     */
    protected $strict =
        [
            'sha1' => '[0-9A-Fa-f]{40}',
            'md5' => '[0-9A-Fa-f]{32}',
            '*' => '.+',
        ];

    /**
     * @var array List of combined patterns
     */
    protected $combined =
        [
            'n' => '0-9',
            'a' => 'a-zA-Z',
            'al' => 'a-z',
            'au' => 'A-Z',
            'c' => '-_',
            'h' => 'a-fA-F0-9',
        ];

    /**
     * Get pattern by name
     *
     * @param string $name
     * @param bool $strict
     * @exception \InvalidArgumentException
     * @return string
     */
    public function get($name, $strict = false)
    {
        if ($strict) {
            if (!isset($this->strict[$name])) {
                throw new \InvalidArgumentException('Strict pattern ' . $name . ' does not exist!');
            }
            return $this->strict[$name];
        } else {
            if (!isset($this->combined[$name])) {
                throw new \InvalidArgumentException('Combined pattern ' . $name . ' does not exist!');
            }
            return $this->combined[$name];
        }
    }

    /**
     * Register pattern by name
     *
     * @param string $name
     * @param string $pattern
     * @param bool $strict
     * @return $this
     */
    public function register($name, $pattern, $strict = false)
    {
        if ($strict) {
            if (isset($this->strict[$name])) {
                throw new \InvalidArgumentException('Strict pattern with name ' . $name . ' already exists!');
            }
            $this->strict[$name] = $pattern;
        } else {
            if (isset($this->combined[$name])) {
                throw new \InvalidArgumentException('Combined pattern with name ' . $name . ' already exists!');
            }
            $this->combined[$name] = $pattern;
        }
        return $this;
    }

    /**
     * Remove pattern from collection
     *
     * @param $name
     * @param bool $strict
     * @return $this
     */
    public function remove($name, $strict = false)
    {
        if ($strict) {
            if (isset($this->strict[$name])) {
                unset($this->strict[$name]);
            }
        } else {
            if (isset($this->combined[$name])) {
                unset($this->combined[$name]);
            }
        }

        return $this;
    }

    /**
     * Checking if pattern with certain name exists
     *
     * @param string $name
     * @param bool $strict
     * @return bool
     */
    public function exist($name, $strict = false)
    {
        if ($strict) {
            return isset($this->strict[$name]);
        }
        return isset($this->combined[$name]);
    }

    /**
     * Get array of registered patterns
     *
     * @param bool $strict
     * @return array
     */
    public function getAll($strict = false)
    {
        if ($strict) {
            return $this->strict;
        }
        return $this->combined;
    }
}