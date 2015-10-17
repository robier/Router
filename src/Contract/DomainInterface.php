<?php

namespace Robier\Router\Contract;

use Robier\Router\MatchedRoute;

interface DomainInterface
{

    /**
     * Generates URL by given name
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function generate($name, array $data = []);

    /**
     * Matches route by given URL and http method
     *
     * @param string $url
     * @param string $method
     * @return bool|MatchedRoute
     */
    public function match($url, $method);

}