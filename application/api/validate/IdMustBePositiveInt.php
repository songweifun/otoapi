<?php
/**
 * id必须为正整数
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/19
 * Time: 下午10:07
 */

namespace app\api\validate;


use think\Validate;

class IdMustBePositiveInt extends BaseValidate
{
    protected $rule=[
      'id'=>'require|isPositiveInteger'
    ];

    protected $message=[
        'id'=>'id必须为正整数'
    ];



}