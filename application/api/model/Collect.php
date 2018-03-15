<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/13
 * Time: 下午2:36
 */

namespace app\api\model;


use app\api\service\Token;

class Collect extends BaseModel
{
    protected $autoWriteTimestamp=true;

    public static function getSummaryByUser($uid, $page=1, $size=20)
    {
        $total=self::where('user_id', '=', $uid)->count();
        $pagingData = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, $total,['var_page'=>'page']);
        //print_r($pagingData->toArray());die;
        return $pagingData ;
    }


    public static function isIntheCollect($id){
        $res=self::where('book_id',$id)->where('user_id',Token::getCurrentUid())->find();
        if($res){
            return true;

        }else{
            return false;
        }




    }


}