<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/26
 * Time: 下午10:32
 */

namespace app\api\model;


class Order extends BaseModel
{
    protected $hidden=['user_id','delete_time','update_time'];
    protected $autoWriteTimestamp=true; //自动写入时间戳
    //protected $createTime='';
    //protected $updateTime

    public static function getSummaryByUser($uid, $page=1, $size=15)
    {
        $pagingData = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData ;
    }


    public static function getSummaryByPage($page=1, $size=20){
        $pagingData = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData ;
    }


}