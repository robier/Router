<?php

namespace Robier\Router\Exception;

use Robier\Router\Contract\Exception\SetDomainNameExceptionInterface;
use Robier\Router\Contract\Exception\SetRouteNameExceptionInterface;

class ValueDoesNotMatchTheRegexException extends RouterException implements SetDomainNameExceptionInterface, SetRouteNameExceptionInterface
{
    protected $value;
    protected $regex;
    protected $routeName;

    public function __construct($value, $regex, $routeName = null, $domainAlias = null)
    {
        $this->value = $value;
        $this->regex = $regex;
        $this->routeName = $routeName;

        parent::__construct(sprintf('Value %s does not match the regex pattern %s', $value, $regex));
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
        $this->message = sprintf('Value %s does not match the regex pattern %s in route %s', $this->value, $this->regex, $routeName);
        return $this;
    }

    public function setDomainName($alias)
    {
        $this->message = sprintf('Value %s does not match the regex pattern %s in route %s inside %s domain', $this->value, $this->regex, $this->routeName, $alias);
        return $this;
    }
}