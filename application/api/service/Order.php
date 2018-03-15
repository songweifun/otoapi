<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/26
 * Time: 下午8:04
 */

namespace app\api\service;


use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct;
use think\Db;


class Order
{
    protected $oProducts;
    protected $products;
    protected $uid;


    //下单的方法
    public function place($uid, $oProducts){
        $this->oProducts=$oProducts;
        $this->products = $this->getProductsByOrder();
        $this->uid = $uid;

        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;//?
            return $status;
        }
        $orderSnap = $this->snapOrder($status);
        $status = self::createOrderByTrans($orderSnap);
        $status['pass'] = true;
        return $status;

    }
    //根据现有代码封装的检测库存量的接口
    public function checkOrderStock($orderId){
        //此方法巧妙的运用了代码的复用 用了一个复杂方法其中的方法
        $this->oProducts=OrderProduct::where('order_id','=',$orderId)
            ->select();
        $this->products=$this->getProductsByOrder();
        $status=$this->getOrderStatus();
        return $status;

    }

    // 创建订单时没有预扣除库存量，简化处理
    // 如果预扣除了库存量需要队列支持，且需要使用锁机制
    public function createOrderByTrans($snap){
        //使用事物回滚 在异常中回滚
        Db::startTrans();
        try {
            $orderNo = $this->makeOrderNo();
            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus'],JSON_UNESCAPED_UNICODE);
            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;

            foreach ($this->oProducts as &$p) {
                //取地址直接赋值
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $ex) {
            Db::rollback();
            throw $ex;
        }
    }
    //生成订单号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    //生成订单快照
    private function snapOrder($status){
        //定义订单快照数组
        $snap=[
            'orderPrice'=>0,
            'totalCount'=>0,
            'pStatus'=>0,
            'snapAddress'=>[],
            'snapAddress'=>null,
            'snapName'=>'',
            'snapImg'=>'',

        ];

        $snap['orderPrice']=$status['orderPrice'];
        $snap['totalCount']=$status['totalCount'];
        $snap['pStatus']=$status['pStatusArray'];
        $snap['snapAddress']=json_encode($this->getUserAddress());
        $snap['snapName']=$this->products[0]['name'];//区商品中的第一个商品作为订单的封面
        $snap['snapImg']=$this->products[0]['main_img_url'];
        if(count($this->products)>1){
            $snap['snapName'].='等';
        }

        return $snap;

    }
    //获得用户的地址

    private function getUserAddress(){
        $user_address=UserAddress::where('user_id','=',$this->uid)
            ->find();
        if(!$user_address){
            throw new UserException([
                'msg'=>'用户收货地址不存在，下单失败',
                'errorCode'=>'60001',
            ]);
        }
        return $user_address->toArray();//数组方式返回
    }

    // 根据订单查找真实商品
    private function getProductsByOrder(){
        $oPIDs = [];
        foreach ($this->oProducts as $item){
            array_push($oPIDs, $item['product_id']);
        }

        //$this->products=Product::all($productIds);
        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();
        //var_dump($products);die;
        return $products;

    }
    //获取整个订单的状态
    private function getOrderStatus(){
        //定义要返回的数组的格式
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount'=>0,
            'pStatusArray' => []
        ];

        foreach ($this->oProducts as $oProduct){
            //整个订单的状态是由商品的状态组成的
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count']);
            //只要有一个商品库存检测不通过就设置整个订单的状态为未通过
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }

            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['counts']; //统计商品的总数
            array_push($status['pStatusArray'], $pStatus);

        }

        return $status;


    }


    //对比订单的数组和商品实际的数组得到一组状态和值
    private function getProductStatus($oPID, $oCount){
        $pIndex = -1;
        //定义返回的数据的格式
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'counts' => 0,
            'name' => '',
            'price' => null,
            'totalPrice' => 0,
            'main_img_url' => 0,
        ];
        for($i=0;$i<count($this->products);$i++){
            if($oPID==$this->products[$i]['id']){
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            throw new OrderException(
                [
                    'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
                ]);

        }else{
            $product = $this->products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['counts'] = $oCount; //下订单时的商品数量
            $pStatus['price'] = $product['price'];
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            $pStatus['main_img_url'] = $product['main_img_url'];

            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }

        }

        return $pStatus;
    }


    public function delivery($orderID, $jumpPage = '')
    {
        $order = OrderModel::where('id', '=', $orderID)
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
//            ->update(['status' => OrderStatusEnum::DELIVERED]);
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order, $jumpPage);
    }


    public static function generateOrderNo(){
        $sn = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return $sn;

    }

}