<?php

namespace Operation\Log\orm\think;

use think\db\BaseQuery as Query;
use think\Model;

class DbModel extends Model
{
    // 日志记录的主键名称
    public $logKey = "id";

    /** @var Query $query */
    private $query;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        parent::__construct($data);
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * Get a fresh instance of the model's attributes.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array|mixed
     */
    public function getRawOriginal($key = null, $default = null)
    {
        if (!is_null($key)) {
            return $this->getOriginal($key, $default);
        }

        return $this->original;
    }
}