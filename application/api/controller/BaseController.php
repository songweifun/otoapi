<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/24
 * Time: 下午11:18
 */

namespace app\api\controller;


use app\api\service\Token;
use think\Controller;

class BaseController extends Controller
{
    //app 和cms
    protected function checkPrimaryScope()
    {
        Token::needPrimaryScope();
    }

    //只有app
    protected function checkExclusiveScope()
    {
        Token::needExclusiveScope();
    }

    protected function checkSuperScope()
    {
        Token::needSuperScope();
    }

}