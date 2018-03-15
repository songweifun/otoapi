<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/1
 * Time: 上午10:55
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Sip2Wrapper as Sip2WrapperService;
use app\api\service\Token as TokenService;
use app\api\model\Sip2Config as Sip2ConfigModel;
use app\lib\exception\Sip2ConfigException;
use think\Request;

class Sip2 extends BaseController
{
    protected $sip2;
    protected $sip2Config;
    protected $loginInfo;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $sid=TokenService::getCurrentTokenVar('sid');
        //echo $sid;die;
        $this->sip2Config=Sip2ConfigModel::getSip2ConfigBySchoolId($sid);
        //print_r($sip2Config);die;
        $this->sip2 = new Sip2WrapperService(
            array(
                'hostname' => $this->sip2Config['host'],
                'port' => $this->sip2Config['port'],
                'withCrc' => false,
                'location' => $this->sip2Config['location'],
                'institutionId' => ''
            )
        );

    }

    public function login(){
        $this->loginInfo=$result=$this->sip2->login($this->sip2Config['bind_name'], $this->sip2Config['bind_pass']);
        //print_r($result);
        return json(array_convert($result));
        //print_r($result['variable']['AO'][0]);die;
    }


    public function getBookDetail($barCode){
        $this->login();
        $result=$this->sip2->tushchaxun($this->loginInfo['variable']['AO'][0],$barCode,$this->sip2Config['bind_name']);
        return json(array_convert($result));

    }

    public function getReaderDetail($barCode){
        $this->login();
        $result=$this->sip2->duzhechaxun($this->loginInfo['variable']['AO'][0],$barCode,$this->sip2Config['bind_name'],'','','');
        return json(array_convert($result));
    }

    public function borrowBook(){
        $this->login();
        //echo $this->loginInfo['variable']['AO'][0];die;
        $barCode=input('post.barCode');
        //echo $barCode;die;
        //echo $this->sip2Config['user_code'];die;
        $result=$this->sip2->jieshu($this->loginInfo['variable']['AO'][0],$this->sip2Config['user_code'],$barCode,'','');
        return json(array_convert($result));

    }

    public function returnBook(){
        $this->login();
        $barCode=input('post.barCode');
        $result=$this->sip2->huanshu('',$this->loginInfo['variable']['AO'][0],$barCode,'','');
        return json(array_convert($result));



    }







}