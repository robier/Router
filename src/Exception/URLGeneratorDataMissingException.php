<?php

namespace Robier\Router\Exception;

use Robier\Router\Contract\Exception\SetDomainNameExceptionInterface;
use Robier\Router\Contract\Exception\SetRouteNameExceptionInterface;

class URLGeneratorDataMissingException extends RouterException implements SetDomainNameExceptionInterface, SetRouteNameExceptionInterface
{
    protected $missingName;
    protected $routeName;

    public function __construct($missingName)
    {
        $this->missingName = $missingName;

        parent::__construct(sprintf('Obligatory data missing %s', $missingName));
    }

    public function setRouteName($name)
    {
        $this->routeName = $name;
        $this->message = sprintf('Obligatory data missing %s for route %s', $this->missingName, $name);
        return $this;
    }

    public function setDomainName($alias)
    {
        $this->message = sprintf('Obligatory data missing %s for route %s in %s domain', $this->missingName, $this->routeName, $alias);
        return $this;
    }
}