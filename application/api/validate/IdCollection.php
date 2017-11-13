<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/22
 * Time: 下午6:03
 */

namespace app\api\validate;


class IdCollection extends BaseValidate
{
    protected $rule=[
      'ids'=>'require|checkIds'
    ];

    protected $message=[
        'ids'=>'参数必须为逗号分隔的正整数'
    ];

    public function checkIds($value){
        if(!$value){
            return false;
        }
        $ids=explode(',',$value);
        foreach ($ids as $k=>$v){

            if(!$this->isPositiveInteger($v)) return false;

        }

        return true;
    }

}