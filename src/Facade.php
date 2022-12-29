<?php

namespace Operation\Log;

abstract class Facade
{
    protected static $resolvedInstance;

    protected static function getFacadeClass(): string
    {
        return "";
    }

    public static function setResolvedInstance($class, $instance)
    {
        self::$resolvedInstance[$class] = $instance;
    }

    public static function __callStatic($method, $args)
    {
        $class = static::getFacadeClass();
        $instance = self::$resolvedInstance[$class] ?? new $class();
        self::$resolvedInstance[$class] = $instance;
        return call_user_func_array([$instance, $method], $args);
    }
}