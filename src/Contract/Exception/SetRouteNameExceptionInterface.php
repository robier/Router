<?php

namespace Robier\Router\Contract\Exception;

interface SetRouteNameExceptionInterface
{
    /**
     * Should change exception message
     *
     * @param string $name
     * @return $this
     */
    public function setRouteName($name);
}