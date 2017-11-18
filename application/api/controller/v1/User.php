<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/14
 * Time: 上午11:29
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\UserNew;

class User extends BaseController
{
    public function register(){
        (new UserNew())->goCheck();
        $postData=request()->post();
        return json($postData);
    }

}