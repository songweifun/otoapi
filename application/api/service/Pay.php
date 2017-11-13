<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/27
 * Time: 下午10:41
 */

namespace app\api\service;


use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use think\Loader;
use think\Log;

Loader::import('WxPay.Wxpay',EXTEND_PATH,'.Api.php');
//s手动载入apiSDk

class Pay
{
    private $orderId;
    private $orderNo;
    public function __construct($orderId)
    {
        if(empty($orderId)){
            throw new Exception('订单号不允许为空');
        }

        $this->orderId=$orderId;
    }

    public function pay(){
        //检测订单存不存在
        //首先检测库存量
        //调用预订单接口

        $this->checkOrderValidate();
        $orderService=new OrderService();
        $status=$orderService->checkOrderStock($this->orderId);
        if(!$status['pass']){
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);


    }

    private function makeWxPreOrder($totalPrice){
        $openid=Token::getCurrentUid('openid');
        if(!$openid){
            throw new TokenException();
        }

        $wxOrderData=new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url')); //支付成功后的回调url
        return $this->getPaySignature($wxOrderData);

    }
    private function getPaySignature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        // 失败时不会返回result_code
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
           //throw new Exception('获取预支付订单失败');
//            throw new OrderException([
//               'msg'=> '获取预支付订单失败'
//            ]);
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    private function recordPreOrder($wxOrder){
        // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        OrderModel::where('id', '=', $this->orderId)->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    // 签名
    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }

    private function checkOrderValidate(){
        $order=OrderModel::where('id','=',$this->orderId)->find();
        if(!$order){
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id))
        {
            throw new TokenException(
                [
                    'msg' => '订单与用户不匹配',
                    'errorCode' => 10003
                ]);
        }
        //用户成功支付后改变的状态
        if($order->status != 1){
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }



}