<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 下午3:01
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\model\Car as CarModel;
use app\api\validate\IdMustBePositiveInt;
use app\api\validate\PagingParameter;
use app\lib\exception\CarException;
use app\lib\exception\CategoryException;
use app\lib\exception\SuccessMessage;
use app\api\service\Jalis as JalisService;


class Car extends BaseController
{
    public function addBookToCar()
    {
        (new IdMustBePositiveInt())->goCheck();
        $uid = TokenService::getCurrentUid();
        $book_id = request()->post('id');
        if(CarModel::where('user_id',$uid)->where('bookid',$book_id)->count()>0){
            throw new CarException([
                'msg'=>"购物车中已经存在此图书",
                'code'=>201,
                'errorCode'=>70004
            ]);
        }
        $res=CarModel::create([
            'bookid'=>$book_id,
            'user_id'=>$uid
        ]);

        if(!$res){
            throw new CategoryException([
                'msg'=>"加入购物车失败",
                'code'=>201,
                'errorCode'=>70001
            ]);
        }

        return json($res);


    }

    public function deleteBookFromCar(){
        //(new IdMustBePositiveInt())->goCheck();
        $uid=TokenService::getCurrentUid();
        $id=request()->delete('id');
        $ids=explode(',',$id);
        foreach ($ids as $id){
            $count=CarModel::where('bookid',$id)->where('user_id','=',$uid)->count();
            if(!$count){
                throw new CarException([
                    'msg'=>"借书中不存在此图书"
                ]);
            }

        }


        $res=CarModel::where('bookid','in',$ids)->where('user_id','=',$uid)->delete();
        if(!$res){
            throw new CarException([
               'msg'=>'借书车删除失败,删除别人的借书车休想!',
                'code'=>401,
                'errorCode'=>70002
            ]);
        }

        throw new SuccessMessage([
           'msg'=>"删除成功"
        ]);


    }

    public function deleteBookFormCarAll(){
        $uid=TokenService::getCurrentUid();
        $count=CarModel::where('user_id',$uid)->count();
        if(!$count){
            throw new CarException();
        }
        $res=CarModel::where('user_id','=',$uid)->delete();
        if(!$res){
            throw new CarException([
               'msg'=>'批量删除购物车失败',
                'code'=>401,
                'errorCode'=>70003
            ]);
        }

        throw new SuccessMessage([
           'msg'=>"清空购物车成功"
        ]);

    }

    public function getAllBookByCar($page=1,$size=20){
        (new PagingParameter())->goCheck();
        $uid=TokenService::getCurrentUid();
        $pagingOrders = CarModel::getSummaryByUser($uid, $page, $size);

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
            $arr['data'][$k]['book_detail']=$jalis->getBookDetailByMarc($v['bookid']);
        }

        return json([
            'total'=>$pagingOrders->total(),
            'current_page' => $page,
            'size'=>$size,
            'data' => $arr['data']
        ]);




    }


}
