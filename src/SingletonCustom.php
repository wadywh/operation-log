<?php


namespace Operation\Log;

/**
 * 自定义单例
 * Class SingletonCustom
 * @package Operation\Log
 */
class SingletonCustom
{
    private static $instance;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance($class)
    {
        if(!self::$instance instanceof $class){
            self::$instance = new $class;
        }
        return self::$instance;
    }
}