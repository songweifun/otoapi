<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/21
 * Time: 下午1:39
 */

namespace app\api\validate;


class CodeGet extends BaseValidate
{
    protected $rule=[
        'phone'=>'require|isMobile'

    ];

}