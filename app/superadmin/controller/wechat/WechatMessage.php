<?php

namespace app\superadmin\controller\wechat;

use app\superadmin\controller\AuthController;
use app\superadmin\model\wechat\WechatMessage as MessageModel;
use crmeb\services\UtilService as Util;

/**
 * 用户扫码点击事件
 * Class SystemMessage
 * @package app\admin\controller\system
 */
class WechatMessage extends AuthController
{
    /**
     * 显示操作记录
     */
    public function index()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['type', ''],
            ['data', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign('mold', MessageModel::$mold);
        $this->assign(MessageModel::systemPage($where));
        return $this->fetch();
    }


}

