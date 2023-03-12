<?php

namespace Operation\Log\Test\model\illuminate;

/**
 * @property mixed $id
 * @property mixed|string $name
 * @property int|mixed $sex
 * @property mixed $price
 */
class User extends Base
{
    protected $table = "user";
    public $tableComment = "用户";
    public $columnComment = [
        "name" => "姓名",
        "sex" => "性别",
        "price" => "金额",
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

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $this->opensslEncrypt($value);
    }

    public function getPriceAttribute($value)
    {
        return $this->opensslDecrypt($value);
    }

    public function types()
    {
        return $this->hasMany(UserType::class, 'user_id', 'id');
    }
}