<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午1:50
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Library;
use app\api\service\Token;
use app\lib\exception\BookException;
use app\lib\exception\LibraryException;
use app\api\service\Jalis as JalisService;
use app\api\service\Sip2 as Sip2Service;

class Jalis extends BaseController
{

    //前置方法 继承think/controller类
//    protected $beforeActionList=[
//        'checkPrimaryScope'=>['only'=>'getLibraries']
//    ];
    public function getLibraries(){

        $jalis=new JalisService();
        $result=$jalis->getLibraries();

        return json($result);



    }

    public function readerCheck(){
        //return request()->get('libCode');
        $queryArr=[
            "libCode"=>request()->get('libCode'),
            "type"=>request()->get('type'),
            "value"=>request()->get('value'),
            "password"=>request()->get('password')
        ];
        $url=$this->getRequstUrl(config('jalis.jalis_reader_check_url'),$queryArr);
        $result=curl_post($url);
        return $result;
    }

    public function search(){

        $action=request()->get('action/a');
        $page=request()->get('page')?request()->get('page'):1;
        $pagesize=request()->get('pagesize')?request()->get('pagesize'):20;


        $query=[];

        if(array_key_exists('query',$action)){
            foreach ($action['query'] as $k=>$v ){
                $v['con']=array_key_exists('con',$v)?$v['con']:'';
                $query['queryFieldList'][]= [
                    "fieldCode"=> $k,
                    "fieldValue"=> $v['value'],
                    "opType"=>$v['con']?$v['con']: null
                ];

            }

        }


        if(array_key_exists('filter',$action)){
            foreach ($action['filter'] as $k2=>$v2 ){
                $query['queryFieldList'][]= [
                    "fieldCode"=> $k2,
                    "fieldValue"=> $v2['value'],
                ];

            }

        }
        $query['offset']=$page;
        $query['limit']=$pagesize;

        $queryArr=[
            'query'=>$query
        ];
        $jalis=new JalisService();
        $result=$jalis->search($queryArr);

        return json($result);
        //return $result;






    }


    public function libHolding($marcid){
        $jalis=new JalisService();
        $result=$jalis->libHolding($marcid);
        //print_r($result);die;
        $library=new Library();
        if($result){
            foreach ($result as $k=>$v){

               $result[$k]['lib_name']=$library->getSchoolNameByHwLibCode($v['libCode'])['lname'];

            }
        }
        return json($result);
    }

    public function libItems($marcid,$libCode){
        //echo $marcId;die;
        $jalis=new JalisService();
        $result=$jalis->libItems($libCode,$marcid);
        return json($result);

    }

    public function libItemsWithSip2Aaviable($marcid,$libCode=''){
        //echo $marcId;die;
        if(!$libCode){
            $libCode=Token::getCurrentTokenVar('hw_lib_code');
            $libCode=23010002;
        }
        $jalis=new JalisService();
        $sip2=new Sip2Service();
        $result=$jalis->libItems($libCode,$marcid);
        foreach ($result as $k=>$v){
            //$lib_items[$kitem]['is_available']=$sip2->getBookDetail($vitem['barCode']);
            $result[$k]['is_available']=$sip2->getBookDetail('lzas001')['fixed']['CirculationStatus'];

        }
        return json($result);

    }

    public function getBookDetailByMarc($marcid){

        $jalis=new JalisService();
        $result=$jalis->getBookDetailByMarc($marcid);
        if(!$result){
            throw new BookException();
        }
        return json($result);

    }


    public function getRequstUrl($uri,$args=[],$withSign=true){
        $jalis_base_url=config('jalis.jalis_base_url');
        $queryArr['appid']=$appid=config('jalis.jalis_appid');
        $appkey=config('jalis.jalis_appkey');
        $queryArr['noncestr']=$noncestr_value=str_replace('-',"",uuid());
        //$noncestr_value="4dbc1b61b79047d285169181aa9827c6";
        $queryArr['timestamp']= $timestamp_value=time();
        //$timestamp_value="1506413604";
        $queryArr['extrainfo']=$extrainfo_value="";

        if(count($args)>0){
            foreach ($args as $k=>$v){
                //echo 11111;die;
                $queryArr[$k]=$v;
            }

        }
        ksort($queryArr);
        //print_r($queryArr);die;
        if($withSign){
            //$stringA="appid=".$appid."&extrainfo=".$extrainfo_value."&noncestr=".$noncestr_value."&timestamp=".$timestamp_value;
           // $strSignTemp=$stringA."&key=".$appkey;
            //$stringA=http_build_query($queryArr);
            $stringA="";

            foreach ($queryArr as $kk=>$vv){
                if(is_array($vv)){
                    $stringA.=$kk."=".json_encode($vv).'&';
                }else{
                    $stringA.=$kk."=".$vv.'&';
                }
            }
            $stringA=trim($stringA,'&');
            //echo $stringA;die;
            $strSignTemp=$stringA."&key=".$appkey;
            //$sign=String2Hex(md5((getBytes($strSignTemp))));
            $sign=md5($strSignTemp);
            //echo  $sign;die;
            //$sign="16ae78db189d420924e558946764a22f";
             //     "1ccf5187c14ce17ba38359f0e77e9796"
            //return $sign;
            //$url=$stringA.'&sign='.$sign;
            $url=$jalis_base_url.$uri."?".$stringA.'&sign='.$sign;
        }else{
            $stringA=http_build_query($queryArr);
            $url=$stringA;

        }

        return $url;
    }

}