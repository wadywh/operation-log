<?php

namespace Operation\Log\Test\model\illuminate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;

class Base extends Model
{
    // 加密
    protected function opensslEncrypt($data)
    {
        return (new Encrypter(md5('operation_log'), 'AES-256-CBC'))->encrypt($data);
    }

    // 解密
    protected function opensslDecrypt($data)
    {
        return (new Encrypter(md5('operation_log'), 'AES-256-CBC'))->decrypt($data);
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