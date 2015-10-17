<?php
namespace Robier\Router\Builder;

/**
 * Class RegexBuilder
 *
 * Regex generator
 */
class RegexBuilder
{
    static public $delimiterList = ['&', '%', '#', '"', '!', '='];
    /**
     * @var string Generated regex
     */
    protected $value = '';

    /**
     * Generating regex
     *
     * @param string|array $regex
     * @param string $delimiter
     * @throws \LogicException
     */
    public function __construct($regex, $delimiter = '')
    {
        $regex = (array)$regex;

        $this->value .= '^';
        foreach ($regex as $key => $value) {
            if ((int)$key === $key) {
                $this->value .= $delimiter . $value;
            } else {
                $this->value .= $delimiter . '(?<' . $key . '>' . $value . ')';
            }
        }
        $this->value .= '$';

        $this->addBoundariesToPattern();
    }

    /**
     * Adding boundary to regex
     *
     * @throws \LogicException
     */
    protected function addBoundariesToPattern()
    {
        $boundary = null;

        // we are checking for chars that do not exist in pattern
        foreach (self::$delimiterList as $char) {
            if (strpos($this->value, $char) === false) {
                $boundary = $char;
                break;
            }
        }
        if (null === $boundary) {
            throw new \LogicException('Could not add boundary characters to regex');
        }

        $this->value = $boundary . $this->value . $boundary . 'Uu';
    }

    public function __toString()
    {
        return $this->value;
    }
}