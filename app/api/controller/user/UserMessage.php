<?php

/**
 * UserMessage.php
 * desc:
 * created on  2020/9/19 5:09 PM
 * Created by caogu
 */

namespace app\api\controller\user;


use app\Request;
use crmeb\services\UtilService;

class UserMessage
{

    /**
     * 用户消息列表
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = UtilService::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 20],
        ], $request);

        $where = [];
        $where[] = ['is_read', '=', 0];
        $uid = $request->uid();
       // db('user_message')->where('uid', $uid)->update(['is_read' => 1]);
        return app('json')->successful(\app\models\user\UserMessage::getList($where, $uid, $data['page'], $data['limit']));
    }

    public function num(Request $request)
    {
        $count = db('user_message')
            ->where('is_read', 0)
            ->where('uid', $request->uid())
            ->count();
        return app('json')->success('', ['num' => $count]);
    }
}
