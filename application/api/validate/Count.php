<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/22
 * Time: 下午9:58
 */

namespace app\api\validate;


class Count extends BaseValidate
{
    protected $rule=[
        'count'=>'isPositiveInteger|between:1,15'
    ];

}