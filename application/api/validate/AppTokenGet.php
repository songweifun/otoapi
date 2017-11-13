<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/8/2
 * Time: 下午7:59
 */

namespace app\api\validate;


class AppTokenGet extends BaseValidate
{

    protected $rule = [
        'ac' => 'require|isNotEmpty',
        'se' => 'require|isNotEmpty'
    ];

}