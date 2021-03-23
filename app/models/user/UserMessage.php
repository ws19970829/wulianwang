<?php

/**
 * UserMessage.php
 * desc:
 * created on  2020/9/19 4:02 PM
 * Created by caogu
 */

namespace app\models\user;


use crmeb\basic\BaseModel;

class UserMessage extends BaseModel
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
    protected $name = 'user_message';

    public function create_message($uid, $title, $message)
    {
        $data = [
            'uid' => $uid,
            'title' => $title,
            'content' => $message,
            'add_time' => time()
        ];
        return self::create($data);
    }

    public function getAddTimeTextAttr($val, $data)
    {
        return date('Y-m-d H:i', $data['add_time']);
    }

    /**
     * 获取用户消息列表
     * @param $where
     * @param $uid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getList($where, $uid, $page = 0, $limit = 20)
    {
        $model = self::where($where)->where('uid', '=', $uid);
        $count = $model->count();

        if ($count > 0) {
            $list = $model->page($page, $limit)->order('id', 'desc')->select();
            $list = $list->append(['add_time_text']);
            $list = $list->toArray();
        } else {
            $list = [];
        }
        $data = [
            'count' => $count,
            'page' => $page,
            'limit' => $limit,
            'list' => $list
        ];
        return $data;
    }
}
