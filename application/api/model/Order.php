<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/26
 * Time: 下午10:32
 */

namespace app\api\model;
use app\api\model\OrderBookList as OrderBookListModel;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;


class Order extends BaseModel
{
    protected $hidden=['user_id','delete_time','update_time'];
    protected $autoWriteTimestamp=true; //自动写入时间戳
    //protected $createTime='';
    //protected $updateTime
    public function userDetail()
    {
        return $this->belongsTo('User','user_id');
    }
    public function libraryDetail()
    {
        return $this->belongsTo('Library','library_id');
    }
//    public function addressDetail()
//    {
//        return $this->hasOne('UserAddress');
//    }

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





    //通过子订单id获得父订单id
    public function getPidById($id){
        $orderInfo=self::where('id',$id)->find()->toArray();
        return $orderInfo['pid'];

    }


    //判断父订单下是否还有子订单
    public function isHasChild($pid){
        $count=self::where('pid',$pid)->count();
//        if($staus!=''){
//            $count=self::where('pid',$pid)->where('status',$staus)->count();
//        }
        if($count>0){
            return true;
        }
        return false;
    }

    public function isAllThisStatus($pid,$status){
        $flag=true;
        $orders=self::where('pid',$pid)->select()->toArray();
        foreach ($orders as $k=>$v){
            if($v['status']!=$status){
                $flag=false;
            }
        }

        return $flag;

    }



}