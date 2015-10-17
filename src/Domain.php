<?php

namespace Robier\Router;

use Robier\Router\Contract\DomainInterface;
use RouteNotFoundException;

/**
 * Class Routes
 *
 * Main class for route collection.
 *
 * @package Robier\Router
 */
class Domain implements DomainInterface
{
    /**
     * @var array $allRoutes List of all routes
     */
    protected $allRoutes = [];

    /**
     * @var array $namedRouted List of all named routes
     */
    protected $namedRoutes = [];

    /**
     * @var array $literalRoutes List of routes without regular patterns
     */
    protected $literalRoutes = [];

    /**
     * @var array $regularRoutes List of routes with regular patterns
     */
    protected $regexRoutes = [];

    /**
     * @var Parser $parser
     */
    protected $parser;

    /**
     * @var string $domain Domain name for collection
     */
    protected $domain;

    public function __construct($domain, Parser $parser)
    {
        $this->domain = $domain;
        $this->setParser($parser);
    }

    /**
     * Parser setter
     *
     * @param Parser $parser
     * @return $this
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return bool
     */
    public function isParserSet()
    {
        return !empty($this->parser);
    }

    /**
     * @return Parser|null
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Adding Route in collection
     *
     * @param Route $route
     * @return $this
     */
    public function add(Route $route)
    {
        $route->setParser($this->parser);

        $hash = sha1($route->getMethod() . $route->getUrl());
        if (isset($this->allRoutes[$hash])) {
            throw new \InvalidArgumentException('Duplicate route url ' . $route->getMethod() . ' ' . $route->getUrl());
        }
        $this->allRoutes[$hash] = $route;

        if ($route->hasName()) {
            if (isset($this->namedRoutes[$route->getName()])) {
                throw new \InvalidArgumentException('Duplicate route name ' . $route->getName());
            }
            $this->namedRoutes[$route->getName()] = $route;
        }
        if (!$route->hasRegex()) {
            if (isset($this->literalRoutes[$this->getLiteralRouteKey($route->getUrl(), $route->getMethod())])) {
                throw new \InvalidArgumentException('Duplicate route url ' . $route->getUrl());
            }
            $this->literalRoutes[$route->getMethod() . ' ' . $route->getUrl()] = $route;
        } else {
            $this->regexRoutes[$route->getStaticPrefix()][$route->getMethod()][] = $route;
        }

        return $this;
    }

    /**
     * Generating all url variation.
     * For example for url /company/offer/test/1 it will return array with:
     * - /company/offer/test/1
     * - /company/offer/
     * - /company/
     * - /
     *
     * @param $url
     * @return array
     */
    protected function getUrlVariations($url)
    {
        $variations = [];
        $explodedUrl = explode('/', trim($url, '/'));

        $i = count($explodedUrl);
        while ($i > 0) {
            unset($explodedUrl[$i]);
            $variations[] = '/' . implode('/', $explodedUrl) . '/';
            --$i;
        }
        $variations[] = '/';
        return $variations;
    }

    /**
     * Gets route by name
     *
     * @param $routeName
     * @throws RouteNotFoundException
     * @return Route
     */
    public function get($routeName)
    {
        if (isset($this->namedRoutes[$routeName])) {
            return $this->namedRoutes[$routeName];
        }
        throw new RouteNotFoundException($routeName);
    }

    /**
     * Checks if route exist
     *
     * @param string $routeName
     * @return bool
     */
    public function has($routeName)
    {
        return isset($this->namedRoutes[$routeName]);
    }

    /**
     * Match current url
     *
     * @param string $url
     * @param string $method
     * @return bool|MatchedRoute
     */
    public function match($url, $method)
    {
        $method = strtoupper($method);

        // checks literal routs first
        $literalRouteKey = $this->getLiteralRouteKey($url, $method);
        /** @var Route $route */
        if (isset($this->literalRoutes[$literalRouteKey])) {
            return new MatchedRoute($this->literalRoutes[$literalRouteKey]);
        }

        // we didn't find route in literals so lets check regex routes
        $urlVariations = $this->getUrlVariations($url);
        foreach ($urlVariations as $urlVariation) {
            if (!isset($this->regexRoutes[$urlVariation][$method])) {
                continue;
            }

            foreach ($this->regexRoutes[$urlVariation][$method] as $route) {
                if (!$route->isMatch($url, $method, $matchedData)) {
                    continue;
                }
                return new MatchedRoute($route, $matchedData);
            }
        }

        // there was no match
        return false;
    }

    /**
     * @param string $url
     * @param string $method
     * @return string
     */
    protected function getLiteralRouteKey($url, $method)
    {
        return $method . ' ' . $url;
    }

    /**
     * @param $name
     * @param array $data
     * @return URL
     */
    public function generate($name, array $data = [])
    {
        return $this->get($name)->generate($this->domain, $data);
    }
}
