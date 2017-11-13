<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 上午9:42
 */

namespace app\api\model;


class Category extends BaseModel
{
    protected $hidden=['update_time','delete_time','topic_img_id'];
    public function img(){
        return $this->belongsTo('Image','topic_img_id','id');
    }

}