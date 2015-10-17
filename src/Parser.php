<?php

namespace Robier\Router;

use Robier\Router\Contract\ParserInterface;
use Robier\Router\Contract\PatternInterface;

/**
 * Class Parser
 * @package Robier\Router
 */
class Parser implements ParserInterface
{
    /**
     * Pattern object
     *
     * @var Pattern $pattern
     */
    protected $pattern;

    /**
     * Constructor
     *
     * @param PatternInterface $pattern
     */
    public function __construct(PatternInterface $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Parsing url and returning array containing regex parts
     *
     * @param string $url
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function parse($url)
    {
        // cleanse url
        $urlParts = explode('/', trim($url, '/'));

        $returnParts = [];

        foreach($urlParts as $urlPart){
            $optionalFlag = false;
            $length = strlen($urlPart);
            $name = null;

            if(empty($urlPart)){
                continue;
            }

            // check if current part has regex
            if($urlPart[0] != '['){
                $returnParts[] = $urlPart;
                continue;
            }

            if($urlPart[$length-1] == '?'){
                $optionalFlag = true;
                --$length;
                $urlPart = substr($urlPart, 0, -1);
            }

            $quantifier = $this->generateQuantifier($urlPart, $length);

            $parts = explode(':', trim($urlPart, '[]'));

            $name = $parts[0];

            if(empty($name)){
                throw new \InvalidArgumentException('Name not provided for '.$urlPart.' in '.$url);
            }

            if(ctype_digit($name)){
                throw new \InvalidArgumentException('Name should be alphanumeric!');
            }

            if(isset($returnParts[$name])){
                throw new \InvalidArgumentException('Duplicate pattern name in '.$url);
            }

            unset($parts[0]);

            // checking if we have only parameter name in route path
            if(empty($parts)){
                // we want all characters until first /
                $returnParts[$name] = $this->generatePattern('[^/.]', $quantifier, $optionalFlag);
                continue;
            }

            $string = implode(':', $parts);

            // lets parse content inside ()
            // ie. if we have route like /test/[name:(foo|bar)]
            // then only possible route can be /test/foo and /test/bar
            if($string != ($trimmed = trim($string, '()'))){
                $returnParts[$name] = $this->generatePattern($trimmed, '', $optionalFlag);
                continue;
            }

            // lets parse content inside <>
            // so everything that is inside <> will be only copy/paste to regex without
            // modifications
            if($string != ($trimmed = trim($string, '<>'))){
                $returnParts[$name] = $this->generatePattern($trimmed, '', $optionalFlag);
                continue;
            }

            // checking if we need to build regex from patterns
            // also deciding is it strict or combined parameter
            if(!(strpos($string, '-') !== false || strpos($string, '|') !== false)){
                if(!$this->pattern->exist($string) && !$this->pattern->exist($string, true)){
                    throw new \LogicException('Registered pattern with name '.$string.' does not exist');
                }
                if($this->pattern->exist($string, true)){
                    $returnParts[$name] = $this->generatePattern($this->pattern->get($string, true), '', $optionalFlag);
                }else{
                    $returnParts[$name] = $this->generatePattern('['.$this->pattern->get($string).']', $quantifier, $optionalFlag);
                }
                continue;
            }else{
                $returnParts[$name] = $this->generatePattern($this->generateCombinedPatterns($string, $quantifier), '', $optionalFlag);
            }
        }

        return $returnParts;
    }

    /**
     * Checking if given url have regex
     *
     * @param string $url
     * @return bool
     */
    public function hasRegex($url)
    {
        return strpos($url, '[') !== false;
    }

    /**
     * Getting static prefix of url
     *
     * @param $url
     * @return string
     */
    public function getStaticPrefix($url)
    {
        if(($position = strpos($url, '[')) === false){
            return $url;
        }

        return substr($url, 0, $position);
    }

    /**
     * Generate quantifier part of regex
     *
     * @param $urlPart
     * @param $length
     * @return string
     */
    protected function generateQuantifier(&$urlPart, &$length)
    {
        /**
         *  * equivalent to {0,}
         *  + equivalent to {1,}
         *  ? equivalent to {0,1}
         */
        $quantifier = '+';
        if($urlPart[$length-1] != '}') {
            return $quantifier;
        }

        $parts = explode(']{', trim($urlPart, '{}'));
        $urlPart = trim($parts[0], '[]');

        $lengthParts = explode(',', $parts[1]);

        if(count($lengthParts) == 1){
            $quantifier = '{'.$lengthParts[0].'}';
        }else{
            if($lengthParts[0] == $lengthParts[1]){
                $quantifier = '{'.$lengthParts[0].'}';
            }else{
                // http://php.net/manual/en/regexp.reference.repetition.php
                if($lengthParts[0] == 0 && empty($lengthParts[1])){
                    $quantifier = '*';
                }elseif($lengthParts[0] == 1 && empty($lengthParts[1])){
                    $quantifier = '+';
                }elseif($lengthParts[0] == 0 && $lengthParts[1] == 1){
                    $quantifier = '?';
                }else{
                    $quantifier = '{'.(int)$lengthParts[0].','.$lengthParts[1].'}';
                }
            }
        }
        $length = strlen($urlPart);

        return $quantifier;
    }

    /**
     * Generate regex pattern
     *
     * @param string $pattern
     * @param string $quantifier
     * @param bool $optional
     * @return string
     */
    protected function generatePattern($pattern, $quantifier = '+', $optional = false)
    {
        if($optional){
            return '('.$pattern.$quantifier.')?';
        }
        return $pattern.$quantifier;
    }

    /**
     * Generate combined patterns
     *
     * @param string $string
     * @param string $quantifier
     * @return string
     */
    protected function generateCombinedPatterns($string, $quantifier)
    {
        $chars = str_split(trim($string));
        $pattern = '';
        $combinedPattern = '';
        $part = '';
        $connected = true;

        foreach($chars as $char){
            if($char == '-'){
                $pattern .= $this->pattern->get($part);
                $part = '';
                $connected = true;
            }elseif($char == '|'){
                $pattern .= $this->pattern->get($part);
                $combinedPattern .= '['.$pattern.']'.$quantifier.'|';
                $pattern = '';
                $part = '';
                $connected = false;
            }else{
                $part .= $char;
            }
        }

        if($connected){
            $pattern .= $this->pattern->get($part);
            $combinedPattern .= '['.$pattern.']'.$quantifier;
            $combinedPattern = '('.$combinedPattern.')';
        }else{
            $pattern .= $this->pattern->get($part);
            $combinedPattern .= '['.$pattern.']'.$quantifier;
            $combinedPattern = '('.$combinedPattern.')';
        }

        return $combinedPattern;
    }
}
