<?php

namespace app\admin\model\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


/**
 * 用户标签管理 model
 * Class User
 * @package app\admin\model\user
 */
class UserTag extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_tag';

    use ModelTrait;

    public function setPayStartTimeAttr($val){
        if(!is_numeric($val)){
            $val=strtotime($val);
        }
        return $val;
    }

    public function setPayEndTimeAttr($val){
        if(!is_numeric($val)){
            $val=strtotime($val);
        }
        return $val;
    }

    public static function getSytemList($where){
        $data = ($data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        $count = self::systemPage($where,true)->count();
        return compact('count', 'data');
    }


    public static function getSytemListToSelect($where){
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));
        $model=$model->where('is_del','=',0);
        $model=$model->field('id,title,is_auto');

        if(isset($where['title']) && $where['title']){
            $title=$where['title'];
            $model=$model->where('title','like',"%$title%");
        }

        if(isset($where['ids']) && $where['ids']){
            $model=$model->where('id','in',$where['ids']);
        }
        $data=$model->select();
        if(count($data)){
            $data=$data->toArray();
        }else{
            $data=[];
        }

        return $data;
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));
        $model=$model->where('is_del','=',0);

        if(isset($where['title']) && $where['title']){
            $title=$where['title'];
            $model=$model->where('title','like',"%$title%");
        }

        if(isset($where['ids']) && $where['ids']){
            $model=$model->where('id','in',$where['ids']);
        }

        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {

        }, $where);
    }



    public static function setWhere($where, $alias = '', $userAlias = 'u.', $model = null)
    {
        $model = is_null($model) ? new self() : $model;
        if ($alias) {
            $model = $model->alias($alias);
            $alias .= '.';
        }
        if (isset($where['nickname']) && $where['nickname'] != '') $model = $model->where("{$userAlias}nickanme", $where['nickname']);
        if (isset($where['level_id']) && $where['level_id'] != '') $model = $model->where("{$alias}level_id", $where['level_id']);
        return $model->where("{$alias}status", 1)->where("{$alias}is_del", 0);
    }

    /*
     * 查询用户vip列表
     * @param array $where
     * */
    public static function getUserVipList($where)
    {
        $data = self::setWhere($where, 'a')->group('a.uid')->order('grade desc')
            ->field('a.*,u.nickname,u.avatar')
            ->join('user u', 'a.uid=u.uid')->page((int)$where['page'], (int)$where['limit'])->select();
        $data = count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $info = SystemUserTag::where('id', $item['level_id'])->find();
            if ($info) {
                $item['name'] = $info['name'];
                $item['icon'] = $info['icon'];
            }
            $item['is_forever'] = $item['is_forever'] ? '永久会员' : '限时会员';
            $item['valid_time'] = $item['is_forever'] ? '永久' : date('Y-m-d H:i:s', $item['valid_time']);
        }
        $count = self::setWhere($where, 'a')->group('a.level_id')->order('grade desc')->join('user u', 'a.uid=u.uid')->count();
        return compact('data', 'count');
    }

    /*
     * 清除会员等级
     * @paran int $uid
     * @paran boolean
     * */
    public static function cleanUpTag($uid)
    {
        self::rollbackTrans();
        $res = false !== self::where('uid', $uid)->update(['is_del' => 1]);
        $res = $res && UserTaskFinish::where('uid', $uid)->delete();
        if ($res) {
            User::where('uid', $uid)->update(['level'=>0,'clean_time' => time()]);
            self::commitTrans();
            return true;
        } else {
            self::rollbackTrans();
            return self::setErrorInfo('清除失败');
        }
    }



}