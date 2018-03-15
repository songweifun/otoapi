<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2018/1/9
 * Time: 下午5:35
 */

namespace app\api\model;


class ReturnOrder extends BaseModel
{
    protected $autoWriteTimestamp=true; //自动写入时间戳

    public function getReturnOrderByUser($uid){
        $res=[];
        $res=self::where('user_id',$uid)->select()->toArray();
        return $res;

    }

}