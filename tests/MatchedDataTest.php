<?php
use Robier\Router\MatchedRoute;
use Robier\Router\Route;

class MatchedDataTest extends PHPUnit_Framework_TestCase
{
    protected $matchedData = null;

    public function testPassingWrongStatusToConstructor()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Wrong status provided');
        new MatchedRoute(9999);
    }

    public function testNotPassingRouteOnFoundStatusToConstructor()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Route needs to be provided if route is found');
        new MatchedRoute(MatchedRoute::STATUS_FOUND);
    }

    public function testNotPassingRouteOnMethodNotImplementedStatusToConstructor()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Route needs to be provided if route is found');
        new MatchedRoute(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED);
    }

    public function testCheckingPassedStatus()
    {
        $route = new Route('/');

        $data = new MatchedRoute(MatchedRoute::STATUS_NOT_FOUND);
        $this->assertTrue($data->isStatus(MatchedRoute::STATUS_NOT_FOUND));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_FOUND));

        $data = new MatchedRoute(MatchedRoute::STATUS_FOUND, $route);
        $this->assertTrue($data->isStatus(MatchedRoute::STATUS_FOUND));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_NOT_FOUND));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED));

        $data = new MatchedRoute(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED, $route);
        $this->assertTrue($data->isStatus(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_NOT_FOUND));
        $this->assertFalse($data->isStatus(MatchedRoute::STATUS_FOUND));
    }

    public function testIsRouteMatchedMethodReturningRight()
    {
        $route = new Route('/');

        $data = new MatchedRoute(MatchedRoute::STATUS_FOUND, $route);
        $this->assertTrue($data->isRouteMatched());

        $data = new MatchedRoute(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED, $route);
        $this->assertTrue($data->isRouteMatched());

        $data = new MatchedRoute(MatchedRoute::STATUS_NOT_FOUND);
        $this->assertFalse($data->isRouteMatched());
    }

    public function testGetRouteMethodReturningRight()
    {
        $route = new Route('/');

        $data = new MatchedRoute(MatchedRoute::STATUS_NOT_FOUND);
        $this->assertNull($data->getRoute());

        $data = new MatchedRoute(MatchedRoute::STATUS_FOUND, $route);
        $this->assertInstanceOf(Route::class, $data->getRoute());

        $data = new MatchedRoute(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED, $route);
        $this->assertInstanceOf(Route::class, $data->getRoute());
    }

    public function testDataReturningRight()
    {
        $data = ['data' => 'test'];
        $route = new Route('/', $data);

        $matchedData = new MatchedRoute(MatchedRoute::STATUS_FOUND, $route);
        $this->assertEquals($data['data'], $matchedData->getData('data'));
        $this->assertEquals($data, $matchedData->getAllData());

        $matchedData = new MatchedRoute(MatchedRoute::STATUS_METHOD_NOT_IMPLEMENTED, $route);
        $this->assertEquals($data['data'], $matchedData->getData('data'));
        $this->assertEquals($data, $matchedData->getAllData());

        $matchedData = new MatchedRoute(MatchedRoute::STATUS_NOT_FOUND);
        $this->assertNull($matchedData->getData($matchedData->getData('data')));
        $this->assertNull($matchedData->getAllData());
    }
}
