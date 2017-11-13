<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    //
    protected $hidden=['update_time','delete_time','pivot','from','category_id','create_time'];
    //读取器
    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }
    //定义关联
    public function imgs(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    public function properties(){
        return $this->hasMany('ProductProperty','product_id','id');
    }

    //获得最新商品
    public static function getMostRecent($count){
        $products=self::limit($count)->order('create_time','desc')->select();
        return $products;
    }
    //查询栏目下商品
    public static function getProductsByCategoryId($id){
        $products=self::where('category_id','=',$id)->select();
        return $products;

    }
    //商品详情
    public static function getProductDetail($id){
        $product = self::with(
            [
                'imgs' => function ($query)
                {
                    $query->with(['imgUrl'])
                         ->order('order','asc');
                }])
            ->with(['properties'])
            //->where('id','=',$id)
            ->find($id);
        return $product;

//        $product = self::with(
//            [
//                'imgs' => function ($query)
//                {
//                    $query->with(['imgUrl'])
//                        ->order('order', 'asc');
//                }])
//            ->with('properties')
//            ->find($id);
//        return $product;
    }
}
