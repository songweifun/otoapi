<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    //封装的用于读取器的函数
    protected function prefixImgUrl($value,$data){
        $finalVale=$value;
        if($data['from']==1){
            $finalVale=config('setting.img_prefix').$value;
        }
        return $finalVale;
    }
}
