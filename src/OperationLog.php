<?php

namespace Operation\Log;

/**
 * @method getPk($model)
 * @method getTableName($model)
 * @method getDatabaseName($model)
 * @method executeSQL($model, $sql)
 * @method getAttributes($model)
 * @method getChangedAttributes($model)
 * @method getValue($model, string $key)
 * @method getOldValue($model, string $key)
 */
class OperationLog
{
    // 表注释
    protected $tableComment;

    // 字段注释
    protected $columnComment;

    // 日志集合
    protected $log = [""];

    // 单次日志
    protected $logByCurrent = '';

    // 当前操作类型
    protected $operationType;

    // 外部当前操作日志记录类
    protected $recordClass = null;

    const CREATED = "created";
    const BATCH_CREATED = "batch_created";
    const UPDATED = "updated";
    const BATCH_UPDATED = "batch_updated";
    const DELETED = "deleted";
    const BATCH_DELETED = "batch_deleted";

    public function __construct()
    {
        Facade::setResolvedInstance(self::class, $this);
    }

    public function getLog(): string
    {
        $log = $this->log;
        $this->clearLog();
        return trim(implode("", $log), PHP_EOL);
    }

    public function getCurrentLog(): string
    {
        $log = $this->logByCurrent;
        $this->clearCurrentLog();
        return $log;
    }

    public function clearLog()
    {
        $this->log = [""];
    }

    public function clearCurrentLog()
    {
        $this->logByCurrent = '';
    }

    public function setTableModelMapping(array $map)
    {
        $GLOBALS['tableModelMapping'] = $map;
    }

    public function getTableModelMapping(): array
    {
        return $GLOBALS['tableModelMapping'] ?? [];
    }

    public function setExecInfoSchema(bool $exec)
    {
        $GLOBALS['execInfoSchema'] = $exec;
    }

    public function getExecInfoSchema(): bool
    {
        return $GLOBALS['execInfoSchema'] ?? true;
    }

    public function setRecordTypes(array $types)
    {
        $GLOBALS['recordTypes'] = $types;
    }

    public function getRecordTypes(): array
    {
        return $GLOBALS['recordTypes'] ?? [];
    }

    public function getOperationType(): string
    {
        return $this->operationType ?? '';
    }

    public function beginTransaction()
    {
        $this->log[] = "";
    }

    public function rollBackTransaction()
    {
        array_pop($this->log);
        if (count($this->log) === 0) {
            $this->clearLog();
        }
    }

    /**
     * 获取表注释
     * @param $model
     * @return string
     */
    public function getTableComment($model): string
    {
        $table = $this->getTableName($model);
        if (isset($model->tableComment) || !$this->getExecInfoSchema()) {
            return $model->tableComment ?: $table;
        }

        $databaseName = $this->getDatabaseName($model);
        $comment = "";

        if (empty($this->tableComment[$databaseName])) {
            $this->tableComment[$databaseName] = $this->executeSQL($model, "SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$databaseName'");
        }

        foreach ($this->tableComment[$databaseName] as $item) {
            if (is_array($item) && $item["TABLE_NAME"] == $table) {
                $comment = $item["TABLE_COMMENT"];
                break;
            }
            if (is_object($item) && $item->TABLE_NAME == $table) {
                $comment = $item->TABLE_COMMENT;
                break;
            }
        }
        return (string)($comment ?: $table);
    }

    /**
     * 获取字段注释
     * @param $model
     * @param $field
     * @return string
     */
    public function getColumnComment($model, $field): string
    {
        if (isset($model->columnComment) || !$this->getExecInfoSchema()) {
            return $model->columnComment[$field] ?? $field;
        }

        $databaseName = $this->getDatabaseName($model);
        $table = $this->getTableName($model);
        $comment = "";

        if (empty($this->columnComment[$databaseName])) {
            $this->columnComment[$databaseName] = $this->executeSQL($model, "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$databaseName'");
        }
        foreach ($this->columnComment[$databaseName] as $item) {
            if (is_array($item) && $item["TABLE_NAME"] == $table && $item["COLUMN_NAME"] == $field) {
                $comment = $item["COLUMN_COMMENT"];
                break;
            }
            if (is_object($item) && $item->TABLE_NAME == $table && $item->COLUMN_NAME == $field) {
                $comment = $item->COLUMN_COMMENT;
                break;
            }
        }
        return (string)($comment ?: $field);
    }

    public function generateLog($model, string $type)
    {
        if (!empty($this->getRecordTypes()) && !in_array($type, $this->getRecordTypes())) {
            return  true;
        }
        if (isset($model->notRecordLog) && $model->notRecordLog) {
            return true;
        }
        $logKey = $model->logKey ?? $this->getPk($model);
        $typeText = [
            self::CREATED => "创建",
            self::BATCH_CREATED => "批量创建",
            self::UPDATED => "修改",
            self::BATCH_UPDATED => "批量修改",
            self::DELETED => "删除",
            self::BATCH_DELETED => "批量删除",
        ][$type];
        $logHeader = "$typeText {$this->getTableComment($model)}" .
            (in_array($type, [self::CREATED, self::UPDATED, self::BATCH_UPDATED, self::DELETED, self::BATCH_DELETED]) ? " ({$this->getColumnComment($model, $logKey)}:{$model->$logKey})：" : "：");
        $log = "";

        switch ($type) {
            case self::CREATED:
            case self::BATCH_CREATED:
            case self::DELETED:
            case self::BATCH_DELETED:
                foreach ($this->getAttributes($model) as $key => $value) {
                    if ($logKey === $key
                        || (isset($model->ignoreLogFields) && is_array($model->ignoreLogFields) && in_array($key, $model->ignoreLogFields))) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}：{$this->getValue($model, $key)}，";
                }
                break;
            case self::UPDATED:
            case self::BATCH_UPDATED:
                foreach ($this->getChangedAttributes($model) as $key => $value) {
                    $keys = explode(".", $key);
                    $key = end($keys);
                    if ($logKey === $key
                        || (isset($model->ignoreLogFields) && is_array($model->ignoreLogFields) && in_array($key, $model->ignoreLogFields))) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}由：{$this->getOldValue($model, $key)} 改为：{$this->getValue($model, $key)}，";
                }
                break;
        }
        if (!empty($log)) {
            array_splice($this->log, -1, 1, end($this->log) . trim($logHeader . $log, "，") . PHP_EOL);
            $this->logByCurrent = trim($logHeader . $log, "，");
        }
        $this->operationType = $type;
        if (!empty($this->recordClass)) {
            $this->recordCurrentLog(SingletonCustom::getInstance($this->recordClass));
        }
        return  true;
    }

    /**
     * 记录当前操作日志
     * @param OperationLogRecordInterface $logRecord
     */
    public function recordCurrentLog(OperationLogRecordInterface $logRecord)
    {
        $logRecord->execRecordLog();
    }
}