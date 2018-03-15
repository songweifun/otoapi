<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2018/1/2
 * Time: 上午10:05
 */

namespace app\api\service;
use app\api\model\Order as OrderModel;
use app\api\model\OrderBookList as OrderBookListModel;
use app\lib\exception\OrderBookListException;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;


class Orders
{
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
        }else{
            $order->rollback();//回滚
            throw new OrderException([
                'msg'=>"更新订单状态失败",
                'errorCode'=>'100005',
                'code'=>400
            ]);
        }

    }


    public function setOrderStatusByOrderId($id,$orderStatus,$bookStatus){
        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $pid=$order->getPidById($id);
        $order->startTrans();//事物开始
        if($order->where('id','=',$id)->setField('status',$orderStatus)){
               if(!$orderBookList->where('order_id','=',$id)->setField('status',$bookStatus)){
//                   throw new OrderBookListException([
//                       'msg'=>'更新订单图书状态失败',
//                       'code'=>401,
//                       'errorCode'=>110003
//                   ]);
               }

            if($order->isAllThisStatus($pid,$orderStatus)){
                if(!$order->where('id',$pid)->setField('status',$orderStatus)){
                    throw new OrderException([
                        'msg'=>"更新订单状态失败",
                        'errorCode'=>'100005w',
                        'code'=>400
                    ]);
                }
            }
            $order->commit();//事物提交
        }else{

            $order->rollback();//回滚
            throw new OrderException([
                'msg'=>"更新订单状态失败",
                'errorCode'=>'100005',
                'code'=>400
            ]);
        }

        throw new SuccessMessage([
           'msg'=>"收货成功"
        ]);


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


    //根据订单id删除订单
    public function delteOrderById($id){
        //echo 11111;die;
        $order=new OrderModel();
        $orderBookList=new OrderBookListModel();
        $pid=$order->getPidById($id);
        if($order->where('id',$id)->delete()){
            if(!$orderBookList->delteOrderBookByOrderId($id)){
                throw new OrderException([
                    'msg'=>"删除订单图书失败",
                    'errorCode'=>'110002',
                    'code'=>400
                ]);
            }
            //如果所有子订单都取消，删除父订单
            if($order->isHasChild($pid)==false){
                if(!$order->where('id',$pid)->delete()){
                    throw new OrderException([
                        'msg'=>"删除订单失败",
                        'errorCode'=>'100004',
                        'code'=>400
                    ]);
                }
            }
        }else{
            throw new OrderException([
                'msg'=>"删除订单失败",
                'errorCode'=>'100004',
                'code'=>400
            ]);

        }

        throw new SuccessMessage(['取消订单成功']);
    }

}