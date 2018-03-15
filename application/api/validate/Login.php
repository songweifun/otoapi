<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/22
 * Time: 下午4:27
 */

namespace app\api\validate;


class Login extends BaseValidate
{
    protected $rule=[
        'phone'=>'require|isMobile',
        'password'=>'require|min:6'
    ];

}