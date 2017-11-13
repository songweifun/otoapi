<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/19
 * Time: 上午12:00
 */

namespace app\api\validate;


use think\Validate;

class TestValidate extends Validate
{
    protected $rule=[
      'name'=>'require|max:10',
        'email'=>'email',
    ];

}