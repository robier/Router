<?php
namespace Robier\Router;

use Robier\Router\Builder\RegexBuilder;
use Robier\Router\Builder\UrlBuilder;
use Robier\Router\Contract\Exception\SetRouteNameExceptionInterface;
use Robier\Router\Exception\ParserNotSetException;
use Robier\Router\Exception\URLGeneratorDataEmptyException;
use Robier\Router\Exception\URLGeneratorDataMissingException;
use Robier\Router\Exception\ValueDoesNotMatchTheRegexException;

/**
 * Class Route
 *
 * Main class that holds all data regarding one route.
 *
 * @package Robier\Router
 */
class Route
{
    /**
     * @var Parser $parser
     */
    protected $parser;

    /**
     * @var string $name Name of the route
     */
    protected $name;

    /**
     * @var string $method Method of the route
     */
    protected $method = 'GET';

    /**
     * @var bool $hasRegex Flag so we know if url is fixed or variable
     */
    protected $hasRegex = false;

    /**
     * @var string $staticPrefix Static prefix of variable route, if route is not variable, this value is the
     * same as $url property
     */
    protected $staticPrefix;

    /**
     * @var array $regex List of names and regex patterns for variable route
     */
    protected $regex;

    /**
     * @var string $regexString Cached regex string for matching url
     */
    protected $regexString;

    /**
     * @var string $url Url provided in constructor
     */
    protected $url;

    /**
     * @var array $attributes Additional data assigned to the route
     */
    protected $attributes = [];

    /**
     * @var array $allowedMethods List of allowed methods
     */
    protected $allowedMethods =
        [
            'GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'
        ];

    /**
     * @param string $url
     * @param array $attributes
     * @param string $name
     * @param string $method
     */
    public function __construct($url, array $attributes = [], $name = null, $method = 'GET')
    {
        $this->url = $url;
        $this->attributes = $attributes;
        $this->setName($name);
        $this->setMethod($method);
    }

    /**
     * Getter for route attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Setter for route attributes
     *
     * @param array $data
     * @return $this
     */
    public function setAttributes(array $data)
    {
        $this->attributes = $data;
        return $this;
    }

    /**
     * @param Parser $parser
     * @return $this
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
        $this->staticPrefix = $this->parser->getStaticPrefix($this->url);
        if ($this->staticPrefix != $this->url) {
            $this->hasRegex = true;
        } else {
            $this->hasRegex = false;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasRegex()
    {
        return $this->hasRegex;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Checking if we have a url match
     *
     * @param string $url
     * @param string $method
     * @param null $matchedData
     * @return bool
     * @throws \LogicException
     */
    public function isMatch($url, $method, &$matchedData = null)
    {
        if (!$this->isMethod($method)) {
            return false;
        }

        if (!$this->parser instanceof Parser) {
            throw new \LogicException('Parser is not set! Set parer with method setParser()');
        }

        if (!$this->hasRegex()) {
            return $url == $this->url;
        }

        if (preg_match($this->getRegexString(), $url, $matchedDataTemp)) {
            $matchedData = $this->parseMatchedData($matchedDataTemp);
            return true;
        }
        return false;
    }

    /**
     * @return null|string
     */
    public function getRegexString()
    {
        if (!$this->hasRegex()) {
            return null;
        }

        if (null === $this->regexString) {
            $this->regexString = (string)(new RegexBuilder($this->getRegexArray(), '/'));
        }
        return $this->regexString;
    }

    /**
     * @return array
     */
    protected function getRegexArray()
    {
        if (null === $this->regex) {
            $this->regex = $this->parser->parse($this->url);
        }
        return $this->regex;
    }

    /**
     * Generates URL with provided data
     *
     * @param $host
     * @param array $data
     * @throws ParserNotSetException
     * @throws URLGeneratorDataEmptyException
     * @return URL
     */
    public function generate($host, array $data = [])
    {
        if (!$this->parser instanceof Parser) {
            throw new ParserNotSetException();
        }

        if (!$this->hasRegex()) {
            return new URL($host, $this->url, $data);
        }

        if (empty($data)){
            throw new URLGeneratorDataEmptyException($this->name);
        }

        try{
            $urlBuilder = new UrlBuilder($this->getRegexArray(), $data);
        }catch (SetRouteNameExceptionInterface $e){
            throw $e->setRouteName($this->getName());
        }

        return new URL($host, $urlBuilder->getPath(), $urlBuilder->getData());
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseMatchedData(array $data = [])
    {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ((int)$key === $key) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Setting http method
     * GET, POST, PATCH, PUT or DELETE
     *
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->allowedMethods)) {
            throw new \InvalidArgumentException('Method with name ' . $method . ' not allowed');
        }
        $this->method = $method;
        return $this;
    }

    /**
     * Checking if route is registered under certain http method
     * GET, POST, PATCH, PUT or DELETE
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method)
    {
        $method = strtoupper($method);

        return $method == $this->method;
    }

    /**
     * Set name of the route
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get http method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Route name getter
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if route has name
     *
     * @return bool
     */
    public function hasName()
    {
        return (bool)$this->name;
    }

    /**
     * Returning static prefix of url
     *
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function post($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'POST');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function get($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'GET');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function patch($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'PATCH');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function put($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'PUT');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function delete($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'DELETE');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function head($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'HEAD');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null|string $name
     * @return $this
     */
    public static function options($url, array $attributes = [], $name = null)
    {
        return new static($url, $attributes, $name, 'OPTIONS');
    }
}