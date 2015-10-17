<?php
include dirname(dirname(__DIR__)).'/Source/Builder/RegexBuilder.php';

use Robier\Router\Builder\RegexBuilder;

class RegexBuilderTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testPassEmptyArrayToConstructor()
    {
        $pattern = new RegexBuilder([]);
        $this->assertEquals('&^$&Uu', $pattern);
    }

    public function testChangingOfPatternDelimiter()
    {
        $delimiters = RegexBuilder::$delimiterList;
        $count = count(RegexBuilder::$delimiterList)-2;
        $patterns = [];

        foreach($delimiters as &$delimiter){
            $patterns[] = implode('', $delimiter);
            unset($delimiter);
        }


    }


}