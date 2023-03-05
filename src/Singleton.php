<?php


namespace Operation\Log;

/**
 * 单例
 * Class Singleton
 * @package Operation\Log
 */
class Singleton
{
    private static $instance;

    // 表模型与命名空间映射关系
    private $tableModelMapping = [];

    // 是否查information_schema库获取表及字段注释
    private $execInfoSchema = true;

    // 设置允许记录的操作类型
    private $recordTypes = [];

    // 外部当前操作日志记录类
    private $recordClass = null;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance()
    {
        if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setTableModelMapping(array $map)
    {
        $this->tableModelMapping = $map;
    }

    public function getTableModelMapping(): array
    {
        return $this->tableModelMapping;
    }

    public function setExecInfoSchema(bool $exec)
    {
        $this->execInfoSchema = $exec;
    }

    public function getExecInfoSchema(): bool
    {
        return $this->execInfoSchema;
    }

    public function setRecordTypes(array $types)
    {
        $this->recordTypes = $types;
    }

    public function getRecordTypes(): array
    {
        return $this->recordTypes;
    }

    public function setRecordClass(object $class)
    {
        $this->recordClass = new $class;
    }

    public function getRecordClass()
    {
        return $this->recordClass;
    }

}