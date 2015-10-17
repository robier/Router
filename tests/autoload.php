<?php

spl_autoload_register(
    function($className)
    {
        $className = str_replace(['Robier\Router', '\\'], ['src', '/'], $className);
        $fileName = dirname(__DIR__).'/'.$className.'.php';
        if(is_readable($fileName)){
            include $fileName;
        }
    }
);