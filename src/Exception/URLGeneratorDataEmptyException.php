<?php

namespace Robier\Router\Exception;

use Robier\Router\Contract\Exception\SetDomainNameExceptionInterface;

class URLGeneratorDataEmptyException extends RouterException implements SetDomainNameExceptionInterface
{
    protected $routeName;
    protected $domainAlias;

    public function __construct($routeName)
    {
        $this->routeName = $routeName;

        parent::__construct(sprintf('No data provided for URL generation for route %s', $routeName));
    }

    public function setDomainName($alias)
    {
        $this->message = sprintf('No data provided for URL generation for route %s registered in domain %s', $this->routeName, $alias);
        return $this;
    }
}