<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 上午11:27
 */

namespace app\api\model;


use think\model\Merge;
use app\api\model\Images as ImagesModel;

class User extends Merge
{

    protected $autoWriteTimestamp=true; //自动写入时间戳
//    public function getOpenId($code){
//        //请求微信服务
//        //存入user表
//
//    }
//
//    public function creteToken(){
//        //生成token
//    }

    public function profile()
    {
        return $this->hasOne('UserDetail')->bind(['library_id']);
    }

    public function address(){
        return  $this->hasMany('UserAddress','user_id','id');
    }

    public function getHeadimgAttr($value)
    {
        $res=ImagesModel::where('id',$value)->find();
        if($res){
            return $res->url;
        }else{
            return '';
        }
    }
    //根据openid查找用户的信息
    public static function getByOpenID($openid){
        return self::where('openid','=',$openid)->find();
    }

    public static function check($uid)
    {
        $user = self::where('id','=',$uid)
            ->find();
        return $user;

    }

    //根据openid查找用户的信息
    public static function getById($id){
        return self::where('id','=',$id)->find();
    }



}