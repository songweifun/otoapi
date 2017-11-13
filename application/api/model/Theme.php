<?php

namespace app\api\model;

use think\Model;

class Theme extends BaseModel
{
    protected $hidden=['update_time','delete_time'];
    //定义topic_imge_id的关联
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }
    //定义head_img_id的关联
    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }

    public function products(){
        return $this->belongsToMany('Product','theme_product','theme_id','product_id');
    }

    public static function getThemeWithProducts($id){
        $theme=self::with('products,topicImg,headImg')->find($id);
        return $theme;
    }
}
