<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/20
 * Time: 下午7:43
 */

namespace app\api\model;


use think\Db;
use think\Model;

class Banner extends BaseModel
{
    //隐藏字段
    protected $hidden=['update_time','delete_time'];

    //定义关联
    public function items(){
        return $this->hasMany('Banner_item','banner_id','id');
    }
    public static function getBannerById($id){
//        try{
//            1/0;
//
//        }catch (Exception $ex){
//            throw $ex;
//        }
        //$result=Db::query("select * from banner_item WHERE banner_id=?",[$id]);
        //result=Db::table('banner_item')->where('banner_id','=',$id)->select();
        //闭包写法
        $result=Db::table('banner_item')
            //->fetchSql()
            ->where(function ($query) use ($id){
           $query->where('banner_id','=',$id);
            })
            ->select();

        return $result;
    }

}