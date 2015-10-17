<?php
use Robier\Router\Parser;
use Robier\Router\Pattern;
use Robier\Router\Domain;

class RouteCollectionTest extends PHPUnit_Framework_TestCase
{

    public function testInstancingNewRouteCollection()
    {
        $collection = new Domain(new Parser(new Pattern()));
    }

}