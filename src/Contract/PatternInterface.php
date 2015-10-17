<?php

namespace Robier\Router\Contract;

/**
 * Interface PatternInterface
 *
 * Patterns can be strict and combined.
 * The difference is that combined patterns can be combined
 * and strict ones can not.
 */
interface PatternInterface
{

    /**
     * Get pattern by name
     *
     * @param string $name
     * @param bool $strict
     * @exception \InvalidArgumentException
     * @return string
     */
    public function get($name, $strict = false);

    /**
     * Register pattern by name
     *
     * @param string $name
     * @param string $pattern
     * @param bool $strict
     * @return $this
     */
    public function register($name, $pattern, $strict = false);

    /**
     * Remove pattern from collection
     *
     * @param $name
     * @param bool $strict
     * @return $this
     */
    public function remove($name, $strict = false);

    /**
     * Checking if pattern with certain name exists
     *
     * @param string $name
     * @param bool $strict
     * @return bool
     */
    public function exist($name, $strict = false);

    /**
     * Get array of registered patterns
     *
     * @param bool $strict
     * @return array
     */
    public function getAll($strict = false);
}