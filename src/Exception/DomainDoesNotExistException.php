<?php

namespace Robier\Router\Exception;

class DomainDoesNotExistException extends RouterException
{
    public function __construct($domainName)
    {
        parent::__construct(sprintf('Domain with name %s does not exist!', $domainName));
    }
}