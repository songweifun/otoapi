<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 下午3:06
 */

namespace app\api\model;


use app\api\service\Token;

class Car extends BaseModel
{
    protected $autoWriteTimestamp=true;

    public static function getSummaryByUser($uid, $page=1, $size=20)
    {
        $total=self::where('user_id', '=', $uid)->count();
        $pagingData = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, $total,['var_page'=>'page']);
        return $pagingData ;
    }


    public static function isIntheCar($id){
        $res=self::where('bookid',$id)->where('user_id',Token::getCurrentUid())->find();
        if($res){
            return true;

        }else{
            return false;
        }




    }

}