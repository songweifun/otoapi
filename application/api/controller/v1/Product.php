<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/22
 * Time: 下午9:52
 */

namespace app\api\controller\v1;



use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\api\validate\IdMustBePositiveInt;
use app\lib\exception\ProductException;

class Product
{
    /**
     * @url /recent
     * @param $count
     */
    public function getRecent($count=15){
        (new Count())->goCheck($count);
        $result=ProductModel::getMostRecent($count);
        if($result->isEmpty()){
            throw new ProductException();
        }
        $result=$result->hidden(['summary']);
       // return json($result);
        return $result;

    }


    public function getAllInCategory($id){
        (new IdMustBePositiveInt())->goCheck();
        $products=ProductModel::getProductsByCategoryId($id);
        if($products->isEmpty()){
            throw new ProductException();
        }

        $products=$products->hidden(['summary']);

        return $products;

    }

    //获得上商品的详情
    public function getOne($id){

        (new IdMustBePositiveInt())->goCheck();
        $product=ProductModel::getProductDetail($id);
        if(!$product){

        }
        return $product;

    }

}