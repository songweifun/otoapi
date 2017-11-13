<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 上午11:27
 */

namespace app\api\model;


class User extends BaseModel
{
//    public function getOpenId($code){
//        //请求微信服务
//        //存入user表
//
//    }
//
//    public function creteToken(){
//        //生成token
//    }

    public function address(){
        return  $this->hasOne('UserAddress','user_id','id');
    }
    //根据openid查找用户的信息
    public static function getByOpenID($openid){
        return self::where('openid','=',$openid)->find();
    }



}