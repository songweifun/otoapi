<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/14
 * Time: 下午2:34
 */

namespace app\api\service;


use app\api\model\Car;
use app\api\model\Collect;
use app\api\model\Library;
use app\lib\exception\BookException;
use app\api\model\Library as LibraryModel;
use app\lib\exception\LibraryException;

class Jalis
{

    public function getLibraries(){

        $url=$this->getRequstUrl(config('jalis.jalis_libs_url'));

        $result=curl_post($url);
        $result=json_decode($result,true);
        if($result['code']!=0){
            throw new LibraryException();
        }

        //print_r($result);die;
        //更新本地图书馆表的libCode 和汇文系统对接
        $lib=new LibraryModel();
        foreach ($result['data'] as $k=>$v){
            $res=$lib->where('lname',$v['libName'])->setField([
                'hw_lib_code'=>$v['libCode']
            ]);
        }

        return $result['data'];



    }


    public function search($queryArr){

//        $action=request()->get('action/a');
//        $page=request()->get('page')?request()->get('page'):1;
//        $pagesize=request()->get('pagesize')?request()->get('pagesize'):20;
//
//
//        $query=[];
//
//        if(array_key_exists('query',$action)){
//            foreach ($action['query'] as $k=>$v ){
//                $v['con']=array_key_exists('con',$v)?$v['con']:'';
//                $query['queryFieldList'][]= [
//                    "fieldCode"=> $k,
//                    "fieldValue"=> $v['value'],
//                    "opType"=>$v['con']?$v['con']: null
//                ];
//
//            }
//
//        }
//
//        if(array_key_exists('filter',$action)){
//            foreach ($action['filter'] as $k2=>$v2 ){
//                $query['queryFieldList'][]= [
//                    "fieldCode"=> $k2,
//                    "fieldValue"=> $v2['value'],
//                ];
//            }
//
//        }
//
//        $queryArr['offset']=$pagesize*($page-1);
//        $queryArr['limit']=$pagesize;
//        $queryArr=[
//            'query'=>$query
//        ];
        $url=$this->getRequstUrl(config('jalis.jalis_search_url'),$queryArr);
        $result=curl_post($url);

        $result=json_decode($result,true);
        if($result['code']==0){
            $res['total']=$result['data']['total'];
            $res['current_page']=$result['data']['offset'];
            $res['size']=$result['data']['limit'];
            $res['data']=$result['data']['marcList'];

        }else{
            throw new BookException([
                'msg'=>'没有此分类下的书籍',
                'errorCode'=>90003
            ]);
        }
        return $res;

    }


    public function getBookDetailByMarc($marcid){
        $uri=config("jalis.jalis_marcbook_detail_url");
        $queryArr['marcId']=$marcid;
        $url=$this->getRequstUrl($uri,$queryArr);
        //return $url;
        $result=curl_post($url);
        $result=json_decode($result,true);
        if($result['code']==0){
            $res=$result['data'];
            //print_r($res);die;
            $res['is_in_car']=Car::isIntheCar($res['id']);
            $res['is_in_collect']=Collect::isIntheCollect($res['id']);
            $res['cover']=config('setting.cover_url').$res['isbn'];
        }else{
            $res=[];
        }
        return $res;

    }


    public function getBookDetailByMarc2($marcid){
        $uri=config("jalis.jalis_marcbook_detail_url");
        $queryArr['marcId']=$marcid;
        $url=$this->getRequstUrl($uri,$queryArr);
        //return $url;
        $result=curl_post($url);
        $result=json_decode($result,true);
        if($result['code']==0){
            $res=$result['data'];
            //print_r($res);die;
            $res['is_in_car']=Car::isIntheCar($res['id']);
            $res['is_in_collect']=Collect::isIntheCollect($res['id']);
            $res['cover']=config('setting.cover_url').$res['isbn'];
        }else{
            $res=[];
        }
        return $res;

    }


    public function libHolding($marcid){
        $uri=config("jalis.jalis_lib_holding_url");


        $queryArr['marcId']=$marcid;

        $url=$this->getRequstUrl($uri,$queryArr);
        //return $url;
        $result=curl_post($url);
        //print_r($result);
        if($result){
            $result=json_decode($result,true);
            if($result['code']===0){
                $res=$result['data'];
                $lib=new Library();
                //print_r($res);die;
                if($res){
                    foreach ($res as $k=>$v){
                        $schoolInfo=$lib->getSchoolIdByHwLibCode($v['libCode']);
                        $res[$k]['lib_id']=$schoolInfo['id'];
                    }
                }else{
                    $res=[];
                }


            }
        }
        return $res;
    }

    public function libItems($libCode,$marcId){
        $uri=config("jalis.jalis_lib_items_url");

        $queryArr=[
            "libCode"=>$libCode,
            "marcId"=>$marcId,
        ];
        $url=$this->getRequstUrl($uri,$queryArr);
        $result=curl_post($url);
        $res=[];
        //print_r($result=json_decode($result,true));die;
        if($result){
            $result=json_decode($result,true);
            if($result['code']==0){
                $res=$result['data']?$result['data']:[];
            }
        }

        return $res;

    }


    public function getRequstUrl($uri,$args=[],$withSign=true){
        $jalis_base_url=config('jalis.jalis_base_url');
        $queryArr['appid']=$appid=config('jalis.jalis_appid');
        $appkey=config('jalis.jalis_appkey');
        $queryArr['noncestr']=$noncestr_value=str_replace('-',"",uuid());
        $queryArr['timestamp']= $timestamp_value=time();
        $queryArr['extrainfo']=$extrainfo_value="";

        if(count($args)>0){
            foreach ($args as $k=>$v){
                $queryArr[$k]=$v;
            }
        }
        ksort($queryArr);
        if($withSign){
            $stringA="";

            foreach ($queryArr as $kk=>$vv){
                if(is_array($vv)){
                    $stringA.=$kk."=".json_encode($vv).'&';
                }else{
                    $stringA.=$kk."=".$vv.'&';
                }
            }
            $stringA=trim($stringA,'&');
            $strSignTemp=$stringA."&key=".$appkey;
            $sign=md5($strSignTemp);
            $url=$jalis_base_url.$uri."?".$stringA.'&sign='.$sign;
        }else{
            $stringA=http_build_query($queryArr);
            $url=$stringA;

        }

        return $url;
    }

}