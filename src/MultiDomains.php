<?php

namespace Robier\Router;

use Robier\Router\Contract\DomainInterface;
use Robier\Router\Contract\Exception\SetDomainNameExceptionInterface;
use Robier\Router\Exception\DomainDoesNotExistException;
use Robier\Router\Exception\DuplicateDomainNameException;
use RouteNotFoundException;

/**
 * Class MultipleDomains
 *
 * We are using this class for easier working with defined Routes collections and multiple
 * domains.
 *
 * @package Robier\Router
 */
class MultiDomains implements DomainInterface
{

    /**
     * @var string $nameSeparator
     */
    protected $nameSeparator = ':';

    /**
     * @var Domain[] $domains
     */
    protected $domains = [];

    /**
     * @var array $lazyFunctions
     */
    protected $lazyFunctions = [];

    /**
     * @var array $matchPriorities
     */
    protected $matchPriorities = [];

    /**
     * @var array $generatePriorities
     */
    protected $generatePriorities = [];

    /**
     * @param array $matchPriorities
     * @param bool $setAlsoAsGeneratePriority
     * @return $this
     */
    public function setMatchPriorities(array $matchPriorities, $setAlsoAsGeneratePriority = false)
    {
        $this->matchPriorities = $matchPriorities;

        if ($setAlsoAsGeneratePriority) {
            $this->generatePriorities = $matchPriorities;
        }

        return $this;
    }

    /**
     * @param array $generatePriorities
     * @param bool $setAlsoAsMatchPriority
     * @return $this
     */
    public function setGeneratePriorities(array $generatePriorities, $setAlsoAsMatchPriority = false)
    {
        $this->generatePriorities = $generatePriorities;

        if ($setAlsoAsMatchPriority) {
            $this->matchPriorities = $generatePriorities;
        }

        return $this;
    }

    /**
     * Gets list of domain names where we will look on matching
     *
     * @return array
     */
    protected function getMatchPriorities()
    {
        if (empty($this->matchPriorities)) {
            return array_keys($this->domains);
        }
        return $this->matchPriorities;
    }

    /**
     * Gets list of domain names where we will look on generate
     *
     * @return array
     */
    protected function getGeneratePriorities()
    {
        if (empty($this->generatePriorities)) {
            return array_keys($this->domains);
        }
        return $this->generatePriorities;
    }

    /**
     * Setting name separator for matching
     *
     * @param $separator
     * @return $this
     */
    public function setNameSeparator($separator)
    {
        $this->nameSeparator = $separator;
        return $this;
    }

    /**
     * Adds Routes collection
     *
     * @param $name
     * @param DomainInterface $collection
     * @throws DuplicateDomainNameException
     * @return $this
     */
    public function add($name, DomainInterface $collection)
    {
        if (isset($this->domains[$name])) {
            throw new DuplicateDomainNameException($name);
        }

        $this->domains[$name] = $collection;
        return $this;
    }

    /**
     * Adding lazy collection
     *
     * @param $name
     * @param callable $function
     * @return $this
     */
    public function lazyAdd($name, callable $function)
    {
        $this->lazyFunctions[$name] = $function;
        return $this;
    }

    /**
     * Match url by url and method
     *
     * @param string $url
     * @param string $method
     * @throws DomainDoesNotExistException
     * @return MatchedRoute
     */
    public function match($url, $method)
    {
        foreach ($this->getMatchPriorities() as $collectionName) {

            $this->lazyLoadCollectionIfNeeded($collectionName);

            if (!isset($this->domains[$collectionName])) {
                throw new DomainDoesNotExistException($collectionName);
            }

            if ($match = $this->domains[$collectionName]->match($url, $method)) {
                return $match;
            }
        }

        return false;
    }

    /**
     * Loading lazy collection by name if needed
     *
     * @param string $name
     */
    public function lazyLoadCollectionIfNeeded($name)
    {
        if (!isset($this->domains[$name]) && isset($this->lazyFunctions[$name])) {
            $this->lazyLoadCollection($name);
        }
    }

    /**
     * Lazy loading collection by name
     *
     * @param string $name
     */
    protected function lazyLoadCollection($name)
    {
        if (!isset($this->lazyFunctions[$name])) {
            throw new \LogicException('Lazy collection with name ' . $name . ' is not defined');
        }
        $collection = $this->lazyFunctions[$name]();
        unset($this->lazyFunctions[$name]);

        $this->add($name, $collection);
    }

    /**
     * Generates url by name
     *
     * @param string $name
     * @param array $data
     * @throws DomainDoesNotExistException
     * @throws \Exception
     * @return URL
     */
    public function generate($name, array $data = [])
    {
        $explodedName = explode($this->nameSeparator, $name);

        switch (count($explodedName)) {
            case 1:
                $searchIn = $this->getGeneratePriorities();
                $routeName = $name;
                break;
            case 2:
                $searchIn = (array)$explodedName[0];
                $routeName = $explodedName[1];
                break;
            default:
                throw new \LogicException(sprintf('Too many separators %s in route name %s', $this->nameSeparator, $name));
                break;
        }

        foreach ($searchIn as $collectionName) {

            $this->lazyLoadCollectionIfNeeded($collectionName);

            if (!isset($this->domains[$collectionName])) {
                throw new DomainDoesNotExistException($collectionName);
            }

            if ($this->domains[$collectionName]->has($routeName)) {
                try {
                    return $this->domains[$collectionName]->generate($routeName, $data);

                    // catch exceptions and add domain name
                } catch (SetDomainNameExceptionInterface $e) {
                    throw $e->setDomainName($collectionName);
                }
            }
        }

        throw (new RouteNotFoundException($routeName))->setDomainName($searchIn);
    }

}