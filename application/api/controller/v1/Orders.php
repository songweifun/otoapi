<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/19
 * Time: 上午11:22
 */

namespace app\api\controller\v1;

use app\api\model\Library;
use app\api\service\Jalis;
use app\api\service\Order as OrderService;
use app\api\service\Sip2;
use app\api\service\Token as TokenService;
use app\api\model\Order as OrderModel;
use app\api\model\OrderBookList as OrderBookListModel;
use app\api\service\Address as AddressService;
use app\api\service\Orders as OrdersService;
use app\api\model\LibraryReturnAddress as libraryReturnAddressModel;
use app\api\model\ReturnOrder as returnOrderModel;


use app\api\controller\BaseController;
use app\lib\exception\OrderBookListException;
use app\lib\exception\OrderException;
use app\lib\exception\ReturnOrderException;
use app\lib\exception\SuccessMessage;
use think\Db;
use think\Exception;


class Orders extends BaseController
{
    public function place(){

//       print_r(request()->post());
//        die;

        $total_money=0;
        $desposit_money=0;//押金总费用
        $express_money=0;//快递总费用

        $uid=TokenService::getCurrentUid();
        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();




        $postData=request()->post();

        foreach ($postData['orderSchoolBookInfo'] as $k=>$v){
            $desposit_money+=$v['desposit']; //押金
            $express_money+=$v['carriage'];//快递费

        }


        //生成父订单
        $parentOrderData=[
            'pid'=>0,
            'sn'=>OrderService::generateOrderNo(),
            'total_money'=>$desposit_money+$express_money,
            'logistics_money'=>$express_money,
            'address_id'=>$postData['addressId'],
            'user_id'=>$uid,
            //'is_logistics'=>''
            'status'=>2 //跳过待支付的环节状态为1  直接为2为待发货
        ];

        if(!$order->save($parentOrderData)){
           throw new OrderException([
               'msg'=>'生成父订单失败',
               'code'=>201,
               'errorCode'=>100001
           ]);
        }

        $pid=$order->id;


        //生成子订单

        foreach ($postData['orderSchoolBookInfo'] as $kk=>$vv){
            $childOrderData=[
                'pid'=>$pid,
                'sn'=>OrderService::generateOrderNo(),
                'start_time'=>$vv['start_time'],
                'return_time'=>$vv['end_time'],
                'total_money'=>$vv['carriage']+$v['desposit'],//总金额
                'logistics_money'=>$vv['carriage'], //快递金额
                //rent_money借书费用这个字段没有用到
                'address_id'=>$postData['addressId'],
                'user_id'=>$uid,
                'library_id'=>$vv['school_id'],
                'is_logistics'=>$vv['send_type'],
                'status'=>2 //默认为1 跳过待支付的环节状态为1  直接为2为待发货
            ];
            $order=new OrderModel();

            if(!$order->save($childOrderData)){
                throw new OrderException([
                    'msg'=>"生成子订单失败",
                    'code'=>201,
                    'errorCode'=>100002
                ]);
            }

            $id=$order->id;
            //echo $id."<br>";
            //将图书插入子订单
            foreach ($vv['book_list'] as $kkk=>$vvv){
                $orderBookListData=[
                    'user_id'=>$uid,
                    'library_id'=>$vv['school_id'],
                    'order_id'=>$id,
                    'book_id'=>$vvv['id'],
                    'start_time'=>$vv['start_time'],
                    'end_time'=>$vv['end_time'],
                    'status'=>2, //默认为1 跳过待支付的环节状态为1  直接为2为待发货
                ];
                $orderBookList=new OrderBookListModel();
                if(!$orderBookList->save($orderBookListData)){
                    throw new OrderBookListException([
                        'msg'=>'订单图书插入失败',
                        'code'=>201,
                        'errorCode'=>110001
                    ]);
                }
            }
        }
        return $pid;


        //return json(request()->post());


    }

    public function getOrderDetailByPid($pid){

        //return $pid;

        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $jalis=new Jalis();
        $libray=new Library();
        $address=new AddressService();
        //$parentOrder=$order->where('id','=',$pid)->find();
        $orderList=$order->where('pid','=',$pid)->whereOr('id','=',$pid)->select();
        $orderList=make_tree($orderList->toArray())[0];
        //print_r($orderList);die;
        $orderList['address_detail']=$address->getAddressDetailById($orderList['address_id']);
        foreach ($orderList['_child'] as $k=>$v){

            $book_list=$orderBookList->where('order_id',$v['id'])->select()->toArray();
            foreach ($book_list as $kk=>$vv){
                $book_list[$kk]=$jalis->getBookDetailByMarc($vv['book_id']);
                //$book_list[$kk]['school_name']=$libray->getSchoolInfoById($vv['library_id'])['lname'];
            }

            $orderList['_child'][$k]['school_name']=$libray->getSchoolInfoById($v['library_id'])['lname'];
            $orderList['_child'][$k]['book_list']=$book_list;





        }
        return json($orderList);


    }

    /**
     * @param $status
     * @return \think\response\Json
     * @throws OrderException
     * 来自前端 适时改变
     * * 0 待发货
     * 1 待收货
     * 2 待归还
     * 3 已归还
     */
    public function getParentOrdersByStatus($status){

        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $jalis=new Jalis();
        $libray=new Library();
        $address=new AddressService();
        $uid=TokenService::getCurrentUid();
        //$uid=59;
        //$parentOrder=$order->where('id','=',$pid)->find();
        if($status==0){
            $orderList=$order->where('status','=',2)->where('pid','<>',0)->where('user_id',$uid)->select();
        }elseif ($status==1){
            $orderList=$order->where('status','>',2)->where('status','<',8)->where('pid','<>',0)->where('user_id',$uid)->select();
        }elseif ($status==2){
            $orderList=$order->where('status','>',7)->where('status','<',10)->where('pid','<>',0)->where('user_id',$uid)->select();

        }elseif ($status==3){
            $orderList=$order->where('status','>',9)->where('pid','<>',0)->where('user_id',$uid)->select();
        }else{
            $orderList=$order->where('user_id',$uid)->select();
        }
        //print_r($orderList);die;
        $orderList=$orderList->toArray();
        foreach ($orderList as $kt=>$vt){
            $porder=$order->where('id',$vt['pid'])->find()->toArray();
            $flag=false;
            foreach ($orderList as $kt2=>$vt2){
                if($vt2['id']==$porder['id']){
                    $flag=true;
                }
            }
            if($flag==false){
                array_push($orderList,$porder);
            }
        }
        $orderList=make_tree($orderList);
        //print_r($orderList);die;
        //$orderList['address_detail']=$address->getAddressDetailById($orderList['address_id']);
        if(!$orderList){
            throw new OrderException([
                //'code'=>201,
                'msg'=>"没有此状态下的订单",
                'errorCode'=>100003
            ]);
        }
        foreach ($orderList as $k=>$v){

            $orderList[$k]['address_detail']=$address->getAddressDetailById($v['address_id']);

            foreach($v['_child'] as $kk=>$vv){
                $book_list=$orderBookList->where('order_id',$vv['id'])->select()->toArray();
                foreach ($book_list as $kkk=>$vvv){
                    $book_list[$kkk]=$jalis->getBookDetailByMarc($vvv['book_id']);
                }
                $orderList[$k]['_child'][$kk]['book_list']=$book_list;
                $orderList[$k]['_child'][$kk]['school_name']=$libray->getSchoolInfoById($vv['library_id'])['lname'];
            }

//            $book_list=$orderBookList->where('order_id',$v['id'])->select()->toArray();
//            foreach ($book_list as $kk=>$vv){
//                $book_list[$kk]=$jalis->getBookDetailByMarc($vv['book_id']);
//                //$book_list[$kk]['school_name']=$libray->getSchoolInfoById($vv['library_id'])['lname'];
//            }
//
//            $orderList['_child'][$k]['school_name']=$libray->getSchoolInfoById($v['library_id'])['lname'];
//            $orderList['_child'][$k]['book_list']=$book_list;





        }
        return json($orderList);

    }


    /**
     * @param $status
     * @return \think\response\Json
     * @throws OrderException
     * 来自前端 适时改变
     * * 0 待发货
     * 1 待收货
     * 2 待归还
     * 3 已归还
     */
    public function getParentOrdersByStatusAdmin($status,$pageSize=0,$pageNumber=20){

        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $jalis=new Jalis();
        //$sip2=new Sip2();
        $libray=new Library();
        $address=new AddressService();
        $library_id=TokenService::getCurrentTokenVar('sid');
        $hw_lib_code=TokenService::getCurrentTokenVar('hw_lib_code');
        $library_id=6;
        $lhw_lib_code=23010002;
        //$uid=59;
        //$parentOrder=$order->where('id','=',$pid)->find();
        if($status==0){
            $orderList=$order->with('userDetail,libraryDetail')->where('status','=',2)->where('pid','<>',0)->where('library_id',$library_id)->paginate();
        }elseif ($status==1){
            $orderList=$order->with('userDetail,libraryDetail')->where('status','>',2)->where('status','<',8)->where('pid','<>',0)->where('library_id',$library_id)->paginate();
        }elseif ($status==2){
            $orderList=$order->with('userDetail,libraryDetail')->where('status','>',7)->where('status','<',10)->where('pid','<>',0)->where('library_id',$library_id)->paginate();

        }elseif ($status==3){
            $orderList=$order->with('userDetail,libraryDetail')->where('status','>',9)->where('pid','<>',0)->where('user_id',$library_id)->paginate();
        }else{
            $orderList=$order->with('userDetail,libraryDetail')->where('library_id',$library_id)->paginate();
        }
        $pagingOrders=$orderList;
        $collection = collection($orderList->items());
        $orderList=$collection->hidden()->toArray();

        if(!$orderList){
            throw new OrderException([
                //'code'=>201,
                'msg'=>"没有此状态下的订单",
                'errorCode'=>100003
            ]);
        }
        foreach ($orderList as $k=>$v){

            $orderList[$k]['address_detail']=$address->getAddressDetailById($v['address_id']);
            $orderList[$k]['is_available']=0;

//            foreach($v['_child'] as $kk=>$vv){
                $book_list=$orderBookList->where('order_id',$v['id'])->select()->toArray();
                foreach ($book_list as $kkk=>$vvv){
                    $book_list[$kkk]['book_detail']=$jalis->getBookDetailByMarc($vvv['book_id']);
//                    $lib_items=$jalis->libItems($lhw_lib_code,$vvv['book_id']);
//                    foreach ($lib_items as $kitem=>$vitem){
//                        //$lib_items[$kitem]['is_available']=$sip2->getBookDetail($vitem['barCode']);
//                        //$lib_items[$kitem]['is_available']=$sip2->getBookDetail('lzas001');
//                    }
//                    $book_list[$kkk]['lib_items']=$lib_items;
                    if($book_list[$kkk]['is_available']){
                        $orderList[$k]['is_available']=1;
                    }

                }
                $orderList[$k]['book_list']=$book_list;
                $orderList[$k]['school_name']=$libray->getSchoolInfoById($v['library_id'])['lname'];
//            }

//            $book_list=$orderBookList->where('order_id',$v['id'])->select()->toArray();
//            foreach ($book_list as $kk=>$vv){
//                $book_list[$kk]=$jalis->getBookDetailByMarc($vv['book_id']);
//                //$book_list[$kk]['school_name']=$libray->getSchoolInfoById($vv['library_id'])['lname'];
//            }
//
//            $orderList['_child'][$k]['school_name']=$libray->getSchoolInfoById($v['library_id'])['lname'];
//            $orderList['_child'][$k]['book_list']=$book_list;





        }


        if (!$orderList)
        {
            return json([
                'current_page' => $pageNumber,
                'rows' => [],
                'total'=>0,
                'page_size'=>$pageSize

            ]);
        }
        return json([
            'current_page' => $pageNumber,
            'rows' => $orderList,
            'total'=>$pagingOrders->total(),
            'page_size'=>$pageSize
        ]);
        //return json($orderList);

    }



    public function deleteOrderByParentId($pid){
        //print_r($this->getIdBypid($pid));die;
        Db::startTrans();
        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $orderIds=$this->getIdBypid($pid);

        try{
            if(!$order->where('id','=',$pid)->whereOr('pid','=',$pid)->delete()){
                throw new OrderException([
                    'msg'=>'订单删除失败',
                    'code'=>401,
                    'errorCode'=>100004

                ]);
            }
//            $map['order_id']=array('in',$this->getIdBypid($pid));


//            $str="";
//            foreach ($this->getIdBypid($pid) as $k=>$v){
//                $str.=$v;
//                $str.=',';
//            }
//
//            $str=trim($str,',');

            if($orderIds){
                foreach ($orderIds as $k=>$v){
                    if(!$orderBookList->where('order_id','=',$v)->delete()){
                        throw new OrderBookListException([
                            'msg'=>'删除订单图书失败',
                            'code'=>401,
                            'errorCode'=>110002
                        ]);
                    }
                }
            }

//            if(!$orderBookList->where('order_id','in',$str)->delete()){
//                throw new OrderBookListException([
//                    'msg'=>'删除订单图书失败',
//                    'code'=>401,
//                    'errorCode'=>110002
//                ]);
//            }

            Db::commit();

            throw new SuccessMessage(['订单取消成功']);


        }catch (Exception $ex) {
            Db::rollback();
            throw $ex;
        }


    }




    public function deleteOrderById($id){
        $ordersService=new OrdersService();
        $ordersService->delteOrderById($id);


    }



    public function setOrderStatusByParentId($pid,$orderStatus,$bookStatus){
        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $orderIds=$this->getIdBypid($pid);
        $order->startTrans();//事物开始
        if($order->where('id','=',$pid)->whereOr('pid','=',$pid)->setField('status',$orderStatus)){
            if($orderIds){
                foreach ($orderIds as $k=>$v){
                    if(!$orderBookList->where('order_id','=',$v)->setField('status',$bookStatus)){
                        throw new OrderBookListException([
                            'msg'=>'更新订单图书状态失败',
                            'code'=>401,
                            'errorCode'=>110003
                        ]);
                    }
                }
            }
            $order->commit();//事物提交
            throw new SuccessMessage([
                'msg'=>"订单图书状态更新成功"
            ]);
        }
        $order->rollback();//回滚
    }



    public function setOrderAndBookStatusByPid($pid,$orderStatus,$bookStatus){
        $orders=new OrdersService();
        $orders->setOrderStatusByParentId($pid,$orderStatus,$bookStatus);

    }


    public function setOrderAndBookStatusById($id,$orderStatus,$bookStatus){
        $orders=new OrdersService();
        $orders->setOrderStatusByOrderId($id,$orderStatus,$bookStatus);

    }



    public function getReturnOrderListDetail(){
        //echo 111111;die;
        //print_r(request()->post());
        $postData=request()->post();
        $data=[];
        $dataFinal=[];
        $jalis=new Jalis();
        $orderBookList=new OrderBookListModel();
        $librayReturnAddress=new libraryReturnAddressModel();
        $libray=new Library();
        foreach ($postData as $k=>$v){
            $data[$v['school_id']]['libray'] = $libray->getSchoolInfoById($v['school_id'])->toArray();
            $bookInfo=$jalis->getBookDetailByMarc($orderBookList->getMacIdByOrderBookListId($v['id']));
            $bookInfo['self_id']=$v['id'];//selfid 用于标记还书的return_order_id
            $data[$v['school_id']]['book_list'][] = $bookInfo;

//            $data[]=[
//                'libray'=>$libray->getSchoolInfoById($v['school_id'])->toArray(),
//                'book_list'=>[$libray->getSchoolInfoById($v['school_id'])->toArray()],
//            ];

        }
        foreach ($data as $kk=>$vv){
            $vv['libray']['book_list']=$vv['book_list'];
            $vv['libray']['return_address_detail']=$librayReturnAddress->getAddressDetailById(13);
            $dataFinal[]=$vv['libray'];
//            $dataFinal['book_list']=$vv['book_list'];
        }
       return json($dataFinal);
    }


    public function returnOrder(){
        $postData=request()->post();
        $uid=TokenService::getCurrentUid();
        $orderBookList=new OrderBookListModel();
        $order=new OrderModel();
        $returnOrder=new returnOrderModel();

       // print_r(request()->post());

        //echo 11111;die;

        foreach ($postData as $k=>$v){


            $insertData=[
                'library_id'=>$v['id'],
                'status'=>1,
                'user_id'=>$uid,
                'express_remark'=>$v['post_code'],
                'is_logistics'=>$v['sendType'],
                'logistics_money'=>0.00,
                'carrier_name'=>'sf',
                'carrier_code'=>$v['post_code'],
            ];


            //生成还书订单
            if($return_order_id=$returnOrder->create($insertData)->id){
                //$return_order_id=$returnOrder->id;
               //echo $return_order_id;
                //更新订单图书列表的图书还书订单
                foreach ($v['book_list'] as $kk=>$vv){
                    if($orderBookList->where('id',$vv['self_id'])->setField('return_order_id',$return_order_id) &&$orderBookList->where('id',$vv['self_id'])->setField('status',4)){
                        //这里换了每一本书后要判断所属订单是否所有的图书已经都全部归还
                        $order_id=$orderBookList->where('id',$vv['self_id'])->find()->toArray()['order_id'];
                        $pid=$order->getPidById($order_id);
                        //echo $order_id;die;
                        //统计所有订单此订单Id下的图书是否已经全部归还
                        $orderBooks=$orderBookList->where('order_id',$order_id)->where('status','=',3)->select()->toArray();
                        //这个地方有待测试
                        if(count($orderBooks)==0){
                            if(!$order->where('id',$order_id)->setField('status',10)){
                                throw new OrderException([
                                    'msg'=>'更新订单状态失败',
                                    'code'=>400,
                                    'errorCode'=>100003
                                ]);
                            }
                            //判断所有子订单的父订单是否为已还完状态
                            if($order->isAllThisStatus($pid,10)){
                                if(!$order->where('id',$pid)->setField('status',10)){
                                    throw new OrderException([
                                        'msg'=>'更新订单状态失败',
                                        'code'=>400,
                                        'errorCode'=>100003
                                    ]);
                                }
                            }


                        }
                    }else{
                        throw new OrderBookListException([
                            'msg'=>'更新订单图书状态失败',
                            'code'=>400,
                            'errorCode'=>110003
                        ]);
                    }


                }
            }else{
                throw new ReturnOrderException([
                    'msg'=>"还书订单生产失败",
                    'errorCode'=>130001,
                    'code'=>400
                ]);
            }


        }

        throw new SuccessMessage([
            'msg'=>"还书成功"
        ]);
        //print_r(request()->post());
    }


    public function getMyReturnOrdersList(){
        $uid=TokenService::getCurrentUid();
        $returnOrder=new returnOrderModel();
        $result=$returnOrder->getReturnOrderByUser($uid);
        return json($result);

    }


    public function getIdBypid($pid){
        $order=new OrderModel();
        $result=[];
        $res=$order->where('pid',$pid)->field('id')->select()->toArray();
        if($res){
            foreach ($res as $k=>$v){
                $result[]=$v['id'];
            }
        }
        return $result;

    }

    public function updateFieldByListId($id,$field,$value){
        $orderBookList=new OrderBookListModel();
        $res=$orderBookList->updateFieldByListId($id,$field,$value);

        if(!$res){
            throw new OrderBookListException(['msg'=>'更新字段失败']);
        }

        throw new SuccessMessage(['msg'=>'更新成功']);
    }


    public function nannongtest(){

        function curl_get_headers($url,$headers)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //不做证书校验,部署在linux环境下请改为true
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, $headers
            );
            $file_contents = curl_exec($ch);
            //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $file_contents;
        }

        function curl_post_headers($url, array $params = array(),array $headers=array())
        {
            $data_string = json_encode($params);
            //$headers_string = json_encode($headers);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, $headers
            );
            $data = curl_exec($ch);
            curl_close($ch);
            return ($data);
        }


        $headers=[
            "Cookie: pgv_pvi=5552538624; _qddaz=QD.1foxf7.4zwt78.jeqyk1et; pgv_si=s2956346368; JSESSIONID=9A8742A7F9DD6E55418EB6D32B8AF61F; userName=%u6797%u9752; UID=9000035102; enc_su=5701C80BBEF350FFB34D7B4925142DE5"
        ];

        $str = curl_get_headers("http://services.e-library.com.cn/admin/consult_toConsultArea.action",$headers);
       //$str = '<a href="javascript:receiveConsult(555555)">';


        preg_match_all('/javascript:receiveConsult\((\d+)\)/',$str,$arr);
        //print_r($arr);die;
        //echo $consultId;
        if(array_key_exists(0,$arr[1])){

            $consultId=$arr[1][0];



            echo curl_post_headers("http://services.e-library.com.cn/admin/consult_receiveConsult.action",['consultId'=>$consultId],$headers);
            echo $consultId;

        }else{
            //echo $str;
            //echo "no task";
        }






    }

}