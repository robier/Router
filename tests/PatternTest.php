<?php
use Robier\Router\Pattern;

class PatternTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Pattern
     */
    protected $pattern = null;

    protected function setUp()
    {
        $this->pattern = new Pattern();
    }

    protected function tearDown()
    {
        $this->pattern = null;
    }

    public function testGettingStrictPattern()
    {
        $this->gettingPattern(true);
    }

    public function testGettingCombinedPattern()
    {
        $this->gettingPattern();
    }

    protected function gettingPattern($strict = false)
    {
        $strictPatterns = $this->pattern->getAll($strict);

        foreach($strictPatterns as $name => $strictPattern){
            $this->assertEquals($strictPattern, $this->pattern->get($name, $strict));
        }
    }

    public function testRegisterNewStrictPattern()
    {
        $this->registerNewPattern(true);
    }

    public function testRegisterNewCombinedPattern()
    {
        $this->registerNewPattern();
    }

    protected function registerNewPattern($strict = false)
    {
        $name = sha1(time());
        $this->pattern->register($name, '.*', $strict);

        $this->assertEquals('.*', $this->pattern->get($name, $strict));

        if($strict){
            $exceptionMessage = 'Combined pattern '.$name.' does not exist!';
        }else{
            $exceptionMessage = 'Strict pattern '.$name.' does not exist!';
        }

        $this->setExpectedException(\InvalidArgumentException::class, $exceptionMessage);
        $this->pattern->get($name, !$strict);
    }

    protected function removingRegisteredPattern($strict = false)
    {
        $name = sha1(time());
        $this->pattern->register($name, '.*', $strict);

        $this->assertEquals('.*', $this->pattern->get($name, $strict));

        $this->pattern->remove($name, $strict);

        $this->assertFalse($this->pattern->exist($name, $strict));
    }

    public function testRemovingRegisteredStrictPattern()
    {
        $this->removingRegisteredPattern(true);
    }

    public function testRemovingRegisteredCombinedPattern()
    {
        $this->removingRegisteredPattern();
    }
}
