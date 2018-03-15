<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午4:45
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Messagerule as MessageruleModel;
use app\api\service\Token;
use app\api\validate\IdMustBePositiveInt;
use app\api\validate\MessageNew;
use app\api\validate\PagingParameter;
use app\lib\exception\MessageException;
use app\lib\exception\MessageRuleException;
use app\api\model\Innernote as InnernoteModel;
use app\lib\exception\SuccessMessage;

class Message extends BaseController
{
    //前置方法 继承think/controller类
    protected $beforeActionList=[
        'checkSuperScope'=>['only'=>'getMessageRules']
    ];

    public function getMessageRules(){
        $result=MessageruleModel::all();
        if(!$result){
            throw new MessageRuleException();

        }
        return json($result);

    }


    public function getMessageRuleById($id){
        (new IdMustBePositiveInt())->goCheck();
        $result=MessageruleModel::where('id','=',$id)->find();
        if(!$result){
            throw new MessageRuleException();
        }
        return json($result);

    }

    public function sendMesageToUser(){
        (new MessageNew())->goCheck();
        $msg_from=Token::getCurrentUid();
        //return $msg_from;
        $postArr=request()->post();
        $postArr['msg_from']=$msg_from;
        $postArr['add_time']=time();

        $res=InnernoteModel::create($postArr);

        if(!$res){
            throw new MessageException([
                'code'=>401,
                'msg'=>"消息发送失败",
                'errorCode'=>50001
            ]);
        }
        return json($res);
    }

    //分页
    public function getUserMessageByUserId($page=1,$size=10){
        (new PagingParameter())->goCheck();
        $msg_to=Token::getCurrentUid();
        $pagingOrders = InnernoteModel::getSummaryByUser($msg_to, $page, $size);
        //return $pagingOrders;
        if ($pagingOrders->isEmpty())
        {
            return json([
                'total'=>$pagingOrders->total(),
                'current_page' => $page,
                'data' => []
            ]);
        }
//        $collection = collection($pagingOrders->items());
//        $data = $collection->hidden(['snap_items', 'snap_address'])
//            ->toArray();
        $arr = $pagingOrders->toArray();

        return json([
            'total'=>$pagingOrders->total(),
            'current_page' => $page,
            'data' => $arr['data']
        ]);
    }

    public function deleteUserMessage($id){
        (new IdMustBePositiveInt())->goCheck();
        $res=InnernoteModel::destroy($id);
        if(!$res){
            throw new MessageException(
                [
                    'msg'=>"消息删除失败",
                    'code'=>401,
                    'errorCode'=>50002,
                ]
            );
        }

        throw new SuccessMessage([
            'msg'=>"删除成功"
        ]);

    }
    public function updateMessgeIsRead($id){

        $res=InnernoteModel::where('id','=',$id)->setField('is_new',0);

        if(!$res){
            throw new MessageException([
               'msg'=>"更新消息状态失败!",
               'code'=>401,
               'errorCode'=>50003,
            ]);
        }

        throw new SuccessMessage([
            'msg'=>'更新消息状态成功'
        ]);

    }

}