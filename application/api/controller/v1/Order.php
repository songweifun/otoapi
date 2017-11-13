<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/24
 * Time: 下午10:22
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\UserToken;
use app\api\validate\IdMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\validate\PagingParameter;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;

class Order extends BaseController
{

    //客户端调用接口提交订单的详细信息
    //检查库存量 如果有库存则则将订单信息写入表中
    // 如果有库存则告诉用户下单成功 可以支付
    //调用 api 支付接口进行支付
    //再次检查库存量
    //如果有库存 服务器就可以调用微信接口支付
    //支付成功
    //再次检查库存
    //成功 扣除库存


    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'], //用户专用
        'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser'], //管理员和用户都可以有
        'checkSuperScope' => ['only' => 'delivery,getSummary']  //只有管理员可以有
    ];


    /**
     * 下单
     * @url /order
     * @HTTP POST
     */
    public function placeOrder(){
          //print_r($products = input('post.products/a'));die;
        //echo $products=input('post');die;
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');
        $uid=UserToken::getCurrentUid();
        $orderSevice=new OrderService();
        $status = $orderSevice->place($uid, $products);
        return json($status);

    }

    /**
     * @param $id
     * @return $this
     * @throws OrderException
     */
    public function getDetail($id){
        (new IdMustBePositiveInt())->goCheck();
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail)
        {
            throw new OrderException();
        }
        $result=$orderDetail->hidden(['prepay_id'])->toArray();
        $result['snap_items']=json_decode($result['snap_items'],true);
        $result['snap_address']=json_decode($result['snap_address'],true);
        return json($result);

    }


    /**
     * 根据用户id分页获取订单列表（简要信息）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummaryByUser($page = 1, $size = 15)
    {
        (new PagingParameter())->goCheck();
        $uid = UserToken::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);
        if ($pagingOrders->isEmpty())
        {
            return json([
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ]);
        }
//        $collection = collection($pagingOrders->items());
//        $data = $collection->hidden(['snap_items', 'snap_address'])
//            ->toArray();
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return json([
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ]);

    }


    /**
     * 获取全部订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page=1, $size = 20){
        (new PagingParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByPage($page, $size);
        if ($pagingOrders->isEmpty())
        {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return json([
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ]);
    }


    public function delivery($id){
        (new IDMustBePositiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            throw new SuccessMessage();
        }
    }

}