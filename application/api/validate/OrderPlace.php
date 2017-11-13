<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/25
 * Time: 下午10:15
 */

namespace app\api\validate;


use app\lib\exception\ParmeterException;

class OrderPlace extends BaseValidate
{
    protected $rule=[
      'products'=>'checkProducts'
    ];

    protected $singleRule=[
      'product_id'=>'require|isPositiveInteger',
        'count'=>'require|isPositiveInteger'
    ];
    //验证整个二维数组
    protected function checkProducts($values){
        if(!is_array($values)){
            throw new ParmeterException([
               'msg'=>'商品列表参数错误'
            ]);
        }
        if(empty($values)){
            throw new ParmeterException([
                'msg'=>'商品列表不能为空'
            ]);
        }

        foreach ($values as $value){
            $this->checkProduct($value);
        }
        return true;
    }

    //验证子数组
    private function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            throw new ParmeterException([
                'msg' => '商品列表参数错误',
            ]);
        }
    }

}