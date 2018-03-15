<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/13
 * Time: 下午6:18
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Car as CarModel;
use app\api\model\Collect as CollectModel;
use app\api\model\Library;
use app\api\validate\IdMustBePositiveInt;
use app\api\service\Token as TokenService;
use app\api\model\Browser as BrowserModel;
use app\api\model\BookClassification as BookClassificationModel;
use app\lib\exception\BookException;
use app\api\service\Jalis as JalisService;
use app\api\model\OrderBookList as OrderBookListModel;

class Book extends BaseController
{
    public function recordBookBrower(){
        (new IdMustBePositiveInt())->goCheck();
        $book_id=request()->post('id');
        $uid=TokenService::getCurrentUid();
        $res=BrowserModel::create([
            'user_id'=>$uid,
            'book_id'=>$book_id

        ]);

        if(!$res){
            throw new CategoryException([
                'msg'=>"加入购物车失败",
                'code'=>201,
                'errorCode'=>90005
            ]);
        }

        return json($res);

    }

    public function getBookClassification(){
        $res=BookClassificationModel::all()->toArray();
        if(!$res){
            throw new BookException([
                'msg'=>'分类信息不存在',
                'errorCode'=>90004
            ]);
        }
        $return =make_tree($res, 'id', "pid", "son", 0);
        return json($return);
        
    }

    public function getBooksByClassificationNum($num,$page=1,$size=20){

        $query=[
            'queryFieldList'=>[
                [
                    'fieldCode'=>'cls_no_lst',
                    'fieldValue'=>$num,
                    "opType"=> null
                ]
            ],

            "offset"=> $page,
            "limit"=>$size
        ];
        $queryArr['query']=$query;
        $jalis=new JalisService();
        $result=$jalis->search($queryArr);
        return json_encode($result);

    }


    public function getRecommendByUser(){
        //借书车中
        //收藏的
        //最大借阅量的
        $uid=TokenService::getCurrentUid();
        $jalis=new JalisService();
        $recommendIds=[];
        $cars=CarModel::where('user_id',$uid)->field('bookid as book_id')->select()->toArray();
        $collects=CollectModel::where('user_id',$uid)->field('book_id')->select()->toArray();
        $recommendIds=array_merge($cars,$collects);
        if(count($recommendIds)>0){
//            foreach ($recommendIds as $k=>$v){
//                $recommendIds[$k]['book_detail']=$jalis->getBookDetailByMarc($v['book_id']);
//            }
            //随机去除一个id来
            $recommendIndex=array_rand($recommendIds);
            $recommendId=$recommendIds[$recommendIndex]['book_id'];
            //根据图书id获得分类id

            //这里还缺一个根据id获得分类号的接口
            $res=[];
            $res=json_decode($this->getBooksByClassificationNum('A3',1,10),true)['data'];

        }else{
            $res=json_decode($this->getBooksByClassificationNum('A2',1,10),true)['data'];
        }

        return json($res);

    }

    public function getRecommendHot(){
        //最大借阅量的图书
        $res=OrderBookListModel::statisticsBookTop10();
        if(!$res ||count($res)<10){
            //没有订单记录的情况下推荐马克思
            $res=json_decode($this->getBooksByClassificationNum('A1',1,10),true)['data'];
            foreach ($res as $k=>$v){
                //print_r($v);die;
                $res[$k]['book_id']=$v['id'];
                if($v){
                    $res[$k]['book_detail']=$v;
                }else{
                    $res[$k]['book_detail']=(object)[];
                }

            }
        }
        //return json($res);
//        echo "<pre>";
//        print_r($res);die;
        return json($res);
    }


    public function getOrderListWithLibItems($id){
        $ids=explode(',',$id);
        $jalis=new JalisService();
        $all_school=[];
        $book_list=[];
        $all_school_list=[];

        foreach ($ids as $id){
            $book_list[$id]=$jalis->getBookDetailByMarc($id);
        }

        //print_r($ids);die;

        foreach ($ids as $k=>$v){
            $temp=$jalis->libHolding($v);
            //print_r($temp);
            $book_list[$v]['libs']=[];
            if(count($temp)>0){
                foreach ($temp as $kk=>$vv){
                    //print_r($vv);die;
                    $book_list[$v]['libs'][]=$vv['libCode'];
                    $school = trim($vv['libCode']);
                    $all_school[$school] = isset($all_school[$school]) ? ++$all_school[$school] : 1 ;
                }
            }
        }
        //die;

        //按照学校出现的次数从高到低排列
        arsort($all_school);
        //print_r($all_school);die;
        $lib=new Library();
        foreach($all_school as $key=>$val)
        {
            $id=$lib->getSchoolIdByHwLibCode($key)['id'];
            $all_school_list[$id] = $key;
        }

        //print_r($all_school_list);die;
        foreach($book_list as $id=>$school_list)
        {
            $school_diff = array_diff($all_school_list,$school_list['libs']);
            $book_list[$id]['libs'] = array_diff($all_school_list,$school_diff);
        }

       //print_r($book_list);die;
        $return_arr=[];

        foreach ($book_list as $ks=>$vs){
            $sub_arr=[];
            foreach ($vs['libs'] as $kss=>$vss){
               $sub_arr[]=[
                   'id'=>$kss,
                   'name'=>$lib->getSchoolIdByHwLibCode($vss)['lname']
               ];
            }
            $book_list[$ks]['libs']=$sub_arr;
            $return_arr[]=$book_list[$ks];


        }

        return json($return_arr);


    }

    public function getOrderListWithSchool(){
        $lib=new Library();
//        $postData=[
//            [
//                'book_id'=>627434,
//                'lib_id'=>2
//            ],
//            [
//                'book_id'=>441741,
//                'lib_id'=>2
//            ],
//            [
//                'book_id'=>39,
//                'lib_id'=>4
//
//            ]
//        ];
        $postData=request()->post();
        //print_r($postData);die;

        $jalis=new JalisService();
        $result=[];
        //可以定义一些total什么的库存什么的预留
        if($postData){
            foreach ($postData as $k=>$v){
                $schoolInfo=$lib->getSchoolInfoById($v['lib_id']);
                $result[$v['lib_id']]['school_id']=$v['lib_id'];
                $result[$v['lib_id']]['school_name']=$schoolInfo['lname'];
                $result[$v['lib_id']]['carriage']=$schoolInfo['express_price'];//快递费用 图书馆配置
                $result[$v['lib_id']]['desposit']=$schoolInfo['desposit'];//押金
                $result[$v['lib_id']]['start_time']=time();//借书的开始时间
                $result[$v['lib_id']]['end_time']=time()+$schoolInfo['borrowtime']*24*3600;//借书的开始时间
                //以后还可扩展借书费用 目前LibrAry表中没有这个字段
                $result[$v['lib_id']]['book_list'][]=$jalis->getBookDetailByMarc($v['book_id']);
//                $returnArr[]=$result[$v['lib_id']];
            }
        }

        $returnArr=[];

        foreach ($result as $k=>$v){
            $returnArr[]=$result[$k];
        }
        return json($returnArr);
    }

    public function getWaitReturnBookListByUser(){
        $uid=TokenService::getCurrentUid();
        //$uid=59;
        $orderBookList=new OrderBookListModel();
        $jalis=new JalisService();
        $lib=new Library();
        $res=$orderBookList->where('user_id',$uid)->where('status',3)->select()->toArray();
        $result=[];
        foreach ($res as $k=>$v){
            $v['book_detail']=$jalis->getBookDetailByMarc($v['book_id']);
            $result[$v['library_id']][]=$v;
        }

        $return=[];

        foreach ($result as $kk=>$vv){
            $item=$lib->where('id',$kk)->find()->toArray();
            $item['book_list']=$vv;
            $return[]=$item;

        }
        return json($return);




    }

}