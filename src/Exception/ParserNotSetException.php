<?php

namespace Robier\Router\Exception;

class ParserNotSetException extends RouterException
{
    public function __construct()
    {
        parent::__construct('Parser not set!');
    }
}