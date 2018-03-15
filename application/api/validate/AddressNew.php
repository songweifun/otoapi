<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午8:14
 */

namespace app\api\validate;


class AddressNew extends BaseValidate
{
    // 为防止欺骗重写user_id外键
    // rule中严禁使用user_id
    // 获取post参数时过滤掉user_id
    // 所有数据库和user关联的外键统一使用user_id，而不要使用uid
    protected $rule = [
        //'address_id' => 'isPositiveInteger',
        'consignee' => 'require|isNotEmpty',
        'tel' => 'require|isMobile',
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'county' => 'require|isNotEmpty',
        'detail_address' => 'require|isNotEmpty',
        'flag' => 'isPositiveInteger|eq:1',
    ];

}