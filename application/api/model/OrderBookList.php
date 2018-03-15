<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/14
 * Time: 下午5:07
 */

namespace app\api\model;
use app\api\service\Jalis as JalisService;


class OrderBookList extends BaseModel
{
    protected $autoWriteTimestamp=true;

    public static function statisticsBookTop10(){
        $res=self::field('book_id,count(id) as count')
            ->group('book_id')
            ->order('count desc')
            ->limit(10)
            ->select()->toArray();
        $result=[];
        $jalis=new JalisService();
        foreach ( $res as $k=>$v){
            $tmp=$jalis->getBookDetailByMarc($v['book_id']);
            //print_r($tmp);die;
            if($tmp){
                $res[$k]['book_detail']=$jalis->getBookDetailByMarc($v['book_id']);
            }else{
                $res[$k]['book_detail']=(object)[];
            }

            //$result[]=$res;
        }


        return $res;
    }

    public function getMacIdByOrderBookListId($id){
        $res=self::where('id',$id)->find()->toArray();
        $result='';
        if($res){
            $result=$res['book_id'];
        }

        return $result;

    }

    public function delteOrderBookByOrderId($id){
        return self::where('order_id',$id)->delete();
    }

    public function updateFieldByListId($id,$filed,$value){
        $result=self::where('id',$id)->update([
            $filed=>$value
        ]);

        return $result;
    }

}