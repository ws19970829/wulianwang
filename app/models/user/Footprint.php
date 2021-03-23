<?php


namespace app\models\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;
use think\facade\Request;

class Footprint extends BaseModel
{

    protected $pk = 'id';

    protected $name = 'footprint';

    public static function footprint($uid, $goods_id)
    {
        db('footprint')->where([
            'uid' => $uid,
            'goods_id' => $goods_id
        ])->delete();
        db('footprint')->insert([
            'uid' => $uid,
            'goods_id' => $goods_id,
            'create_time' => time()
        ]);
    }

    public function getCollectAttr($v,$data)
    {
        if(!Request::uid()) return 0;

         if(db('user_collect')
        ->where('uid',Request::uid())
        ->where('type',1)
        ->where('collect_id',$data['goods_id'])
        ->count()){
            return 1;
        }else{
            return 0;
        }
    }

    public function getGoodsUrlAttr($v,$data)
    {
        return request()->domain().'/wap/goods/detail?id='.$data['goods_id'];
    }
}
