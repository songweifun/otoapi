<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午6:07
 */

namespace app\api\model;


class ProductImage extends BaseModel
{
    protected $hidden=['img_id','delete_time','product_id'];
    //定义读取器完成拼接
    public function imgUrl(){
        return $this->belongsTo('Image','img_id','id');
    }

}