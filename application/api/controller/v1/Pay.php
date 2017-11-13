<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/27
 * Time: 下午10:31
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IdMustBePositiveInt;
use app\api\service\Pay as PayService;

class Pay extends BaseController
{
    protected $beforeActionList=[
      'checkExclusiveScope'=>['only','getPreOrder']
    ];

    //生成预订单接口
    public function getPreOrder($id=''){
        (new IdMustBePositiveInt())->goCheck();
        $pay=new PayService($id);
        return $pay->pay();

    }

}