<?php

namespace Operation\Log\Test\model\illuminate;

/**
 * @property mixed $id
 * @property mixed|string $name
 * @property int|mixed $sex
 */
class User extends Base
{
    protected $table = "user";
    public $tableComment = "用户";
    public $columnComment = [
        "name" => "姓名",
        "sex" => "性别",
    ];
    const CREATED_AT = "create_time";
    const UPDATED_AT = "update_time";
    public $ignoreLogFields = [
        "create_time",
        "update_time",
    ];

    public function getSexTextAttribute($key): string
    {
        return ["女", "男"][($key ?? $this->sex)] ?? "未知";
    }
}