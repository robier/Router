<?php

namespace Robier\Router;

/**
 * Class MatchedData
 *
 * @package Robier\Router
 */
class MatchedRoute
{
    /**
     * @var Route $route
     */
    protected $route;

    /**
     * @var null|array $data
     */
    protected $matchedData = [];

    /**
     * Constructor
     *
     * @param Route $route
     * @param $matchedData
     */
    public function __construct(Route $route, array $matchedData = null)
    {
        $this->route = $route;
        if(!empty($matchedData)){
            $this->matchedData = $matchedData;
        }
    }

    /**
     * Get router object
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Getter for specific data
     *
     * @return array
     */
    public function getMatchedData()
    {
        return $this->matchedData;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->route->getAttributes();
    }
}