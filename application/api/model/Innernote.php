<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午5:42
 */

namespace app\api\model;


class Innernote extends BaseModel
{
    protected $autoWriteTimestamp=true; //自动写入时间戳

    public static function getSummaryByUser($uid, $page=1, $size=15)
    {
        $total=self::where('msg_to', '=', $uid)->count();
        $pagingData = self::where('msg_to', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, $total,['var_page'=>'page']);
        //print_r($pagingData->toArray());die;
        return $pagingData ;
    }


}