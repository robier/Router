<?php

namespace Robier\Router\Exception;

class DuplicateDomainNameException extends RouterException
{
    public function __construct($domainName)
    {
        parent::__construct(sprintf('Domain with name %s already exists!', $domainName));
    }
}