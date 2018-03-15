<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/13
 * Time: 下午2:30
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IdMustBePositiveInt;
use app\api\model\Collect as CollectModel;
use app\api\service\Token as TokenService;
use app\api\validate\PagingParameter;
use app\lib\exception\CollectException;
use app\lib\exception\SuccessMessage;
use app\api\service\Jalis as JalisService;


class Collect extends BaseController
{
    public function addToCollect(){
        (new IdMustBePositiveInt())->goCheck();
        $uid = TokenService::getCurrentUid();
        $book_id = request()->post('id');
        if(CollectModel::where('user_id',$uid)->where('book_id',$book_id)->count()>0){
            throw new CollectException([
                'msg'=>"收藏夹中已经存在此图书",
                'code'=>201,
                'errorCode'=>80004
            ]);
        }

        $res=CollectModel::create([
            'book_id'=>$book_id,
            'user_id'=>$uid
        ]);

        if(!$res){
            throw new CollectException([
                'msg'=>"加入收藏夹失败",
                'code'=>201,
                'errorCode'=>80001
            ]);
        }

        return json($res);

    }

    public function deleteBookFromCollect(){
        //(new IdMustBePositiveInt())->goCheck();
        $uid=TokenService::getCurrentUid();
        $id=request()->delete('id');
        $ids=explode(',',$id);
       foreach ($ids as $id){
           $count=CollectModel::where('book_id',$id)->where('user_id','=',$uid)->count();
           if(!$count){
               throw new CollectException([
                   'msg'=>"收藏夹中不存在此图书"
               ]);
           }

       }


        $res=CollectModel::where('book_id','in',$ids)->where('user_id','=',$uid)->delete();

        if(!$res){
            throw new CollectException([
                'msg'=>'收藏夹删除失败,删除别人的收藏夹休想!',
                'code'=>401,
                'errorCode'=>80002
            ]);
        }

        throw new SuccessMessage([
            'msg'=>"删除成功"
        ]);


    }

    public function deleteBookFormCollectAll(){
        $uid=TokenService::getCurrentUid();
        $count=CollectModel::where('user_id',$uid)->count();
        if(!$count){
            throw new CollectException();
        }
        $res=CollectModel::where('user_id','=',$uid)->delete();
        if(!$res){
            throw new CarException([
                'msg'=>'清空收藏夹失败',
                'code'=>401,
                'errorCode'=>80003
            ]);
        }

        throw new SuccessMessage([
            'msg'=>"清空收藏夹成功"
        ]);

    }


    public function getAllBookByCollect($page=1,$size=20){
        (new PagingParameter())->goCheck();
        $uid=TokenService::getCurrentUid();
        $pagingOrders = CollectModel::getSummaryByUser($uid, $page, $size);

        if ($pagingOrders->isEmpty())
        {
            return json([
                'total'=>$pagingOrders->total(),
                'current_page' => $page,
                'size'=>$size,
                'data' => []
            ]);
        }

        $jalis=new JalisService();

        $arr = $pagingOrders->toArray();
        foreach ($arr['data'] as $k=>$v){
            $arr['data'][$k]['book_detail']=$jalis->getBookDetailByMarc($v['book_id']);
        }

        return json([
            'total'=>$pagingOrders->total(),
            'current_page' => $page,
            'size'=>$size,
            'data' => $arr['data']
        ]);




    }

}