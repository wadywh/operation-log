<?php

namespace Operation\Log\orm\illuminate;

use Operation\Log\facades\IlluminateOrmLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Operation\Log\Singleton;

class Builder extends \Illuminate\Database\Query\Builder
{
    public function insert(array $values): bool
    {
        $result = parent::insert($values);

        $this->insertLog($values);

        return $result;
    }

    public function insertGetId(array $values, $sequence = null): int
    {
        $id = parent::insertGetId($values, $sequence);

        $this->insertLog($values);

        return $id;
    }

    public function insertOrIgnore(array $values): int
    {
        $result = parent::insertOrIgnore($values);

        $this->insertLog($values);

        return $result;
    }

    public function update(array $values): int
    {
        $oldData = $this->get()->toArray();
        if (!empty($oldData)) {
            $model = $this->generateModel();
            if (count($oldData) > 1) {
                IlluminateOrmLog::batchUpdated($model, $oldData, $values);
            } else {
                IlluminateOrmLog::updated($model, (array)$oldData[0], $values);
            }
        }

        return parent::update($values);
    }

    public function delete($id = null): int
    {
        $this->deleteLog($id);

        return parent::delete($id);
    }

    public function truncate()
    {
        $this->deleteLog();

        parent::truncate();
    }

    /**
     * 生成Model对象
     * @return Model
     */
    private function generateModel(): Model
    {
        $name = $this->from;
        $tableMap = Singleton::getInstance()->getTableModelMapping();
        if (isset($tableMap[$name])) {
            $modelNamespace = $tableMap[$name];
            $className = trim($modelNamespace, "\\");
        } else {
            $modelNamespace = $this->getConnection()->getConfig("modelNamespace") ?: "app\model";
            $className = trim($modelNamespace, "\\") . "\\" . Str::studly($name);
        }
        if (class_exists($className)) {
            $model = new $className;
        } else {
            $model = new DbModel();
            $model->setQuery($this->getConnection());
            $model->setTable($name);
            $model->logKey = $this->getConnection()->getConfig("logKey") ?: $model->getKeyName();
        }
        return $model;
    }

    private function insertLog(array $values)
    {
        $model = $this->generateModel();
        if (is_array(reset($values))) {
            // 多条插入
            IlluminateOrmLog::batchCreated($model, $values);
        } else {
            // 单条插入
            $id = $this->getConnection()->getPdo()->lastInsertId();
            $pk = $model->getKeyName();
            $values[$pk] = $id;
            IlluminateOrmLog::created($model, $values);
        }
    }

    private function deleteLog($id = null)
    {
        if (!empty($id)) {
            $data = [(array)$this->find($id)];
        } else {
            $data = $this->get()->toArray();
        }

        if (!empty($data)) {
            $model = $this->generateModel();
            if (count($data) > 1) {
                IlluminateOrmLog::batchDeleted($model, $data);
            } else {
                IlluminateOrmLog::deleted($model, (array)$data[0]);
            }
        }
    }
}