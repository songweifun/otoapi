<?php

namespace app\api\model;

use think\Model;

class Image extends BaseModel
{

    //protected $hidden=['id','from','update_time','delete_time'];
    protected $visible=['url'];
    //
    //用读取器完成字段的自动拼接 这是一个方法 方法名分为三段 ge Url Attr 前后固定 中间为字段
    //函数接收两个参数 第一个就是此属性的原来值 $data为每一条记录对应的所有字段组成的数组
    public function getUrlAttr($value,$data){
//        $finalVale=$value;
//        if($data['from']==1){
//            $finalVale=config('setting.img_prefix').$value;
//        }
//        return $finalVale;
        //基类方法
        return $this->prefixImgUrl($value,$data);
    }
}
