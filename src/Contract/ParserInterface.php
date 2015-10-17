<?php

namespace Robier\Router\Contract;

interface ParserInterface
{

    /**
     * @param PatternInterface $pattern
     */
    public function __construct(PatternInterface $pattern);

    /**
     * Parsing url and returning array containing regex parts
     *
     * @param string $url
     * @return array
     * @throws \Exception
     */
    public function parse($url);

    /**
     * Checking if there is a regex in pattern
     *
     * @param string $url
     * @return bool
     */
    public function hasRegex($url);

    /**
     * Getting static prefix of url
     *
     * @param string $url
     * @return string
     */
    public function getStaticPrefix($url);
}