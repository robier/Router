<?php

namespace Robier\Router\Contract\Exception;

interface SetDomainNameExceptionInterface
{

    /**
     * Should change exception message
     *
     * @param string $name
     * @return $this
     */
    public function setDomainName($name);

}