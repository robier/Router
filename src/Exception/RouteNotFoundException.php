<?php

namespace Robier\Router\Exception;

use Robier\Router\Contract\Exception\SetDomainNameExceptionInterface;
use Robier\Router\Exception\RouterException;

class RouteNotFoundException extends RouterException implements SetDomainNameExceptionInterface
{
    protected $routeName;

    public function __construct($routeName)
    {
        $this->routeName = $routeName;

        parent::__construct(sprintf('Route with name %s could not be found', $routeName));
    }


    public function setDomainName($name)
    {
        $name = (array)$name;

        if (count($name) == 1) {
            $domains = 'domain ';
        } else {
            $domains = 'domains ';
        }

        $domains .= implode(',', $name);

        $this->message = sprintf('Route with name %s could not be found inside %s', $this->routeName, $domains);
        return $this;
    }
}