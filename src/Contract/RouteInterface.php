<?php


/**
 * Interface RouteInterface
 */
interface RouteInterface
{

    /**
     * Constructor for setting all necessary stuff for route
     *
     * @param string $url
     * @param array $data
     * @param null|string $name
     * @param string $method
     */
    public function __construct($url, array $data = [], $name = null, $method = 'GET|POST');

    /**
     * Setter for parser
     *
     * @param ParserInterface $parser
     * @return $this
     */
    public function setParser(ParserInterface $parser);

    /**
     * Checks if url is variable ie. have regex pattern
     *
     * @return bool
     */
    public function hasRegex();

    /**
     * Getter for url provided in constructor
     *
     * @return string
     */
    public function getUrl();

    /**
     * Checking if we have a url match
     *
     * @param string $url
     * @param string $method
     * @return bool
     * @throws \LogicException
     */
    public function isMatch($url, $method);

    /**
     * Get regex string or null if this route do not have regex pattern
     *
     * @return string|null
     */
    public function getRegexString();

    /**
     * Generates URL with provided data
     *
     * @param array $data
     * @return string
     * @throws \LogicException
     */
    public function generate(array $data = []);

    /**
     * Setting http method
     * GET|POST|PATCH|PUT|DELETE
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Checking if route is registered under certain http method
     * GET|POST|PATCH|PUT|DELETE
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method);

    /**
     * Set name of the route
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set data of the route
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * Get http method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Route name getter
     *
     * @return string|null
     */
    public function getName();

    /**
     * Route data getter, matched data need to override defined values
     *
     * @return array
     */
    public function getData();

    /**
     * Check if route has name
     *
     * @return bool
     */
    public function hasName();

    /**
     * Returning static prefix of url
     *
     * @return string
     */
    public function getStaticPrefix();

}