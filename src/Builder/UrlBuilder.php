<?php
namespace Robier\Router\Builder;

use Robier\Router\Exception\URLGeneratorDataMissingException;
use Robier\Router\Exception\ValueDoesNotMatchTheRegexException;

class UrlBuilder
{
    protected $path;
    protected $data = [];

    public function __construct(array $regexUrl, array $data = [])
    {
        $path = '';
        foreach($regexUrl as $name => $regex){
            // we are checking if $name is int, if it is then this is not a regex
            if((int)$name === $name){
                $path .= '/'.$regex;
                continue;
            }

            if(!isset($data[$name])){
                throw new URLGeneratorDataMissingException($name);
            }

            $regex = new RegexBuilder($regex);
            if(preg_match($regex, $data[$name])){
                $path .= '/'.$data[$name];
                unset($data[$name]);
            }else{
                throw new ValueDoesNotMatchTheRegexException($data[$name], (string)$regex);
            }
        }

        if(!empty($data)){
            $this->data = $data;
        }

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getData()
    {
        return $this->data;
    }
}