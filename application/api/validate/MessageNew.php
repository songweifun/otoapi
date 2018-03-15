<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午6:19
 */

namespace app\api\validate;


class MessageNew extends BaseValidate
{
    protected $rule = [
        //'address_id' => 'isPositiveInteger',
        'msg_to' => 'require|isPositiveInteger',
        'msg_title' => 'require|isNotEmpty',
        'msg_content' => 'require|isNotEmpty',
    ];


}