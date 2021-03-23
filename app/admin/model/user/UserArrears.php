<?php

namespace app\admin\model\user;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\UserExtract;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;



class UserArrears extends BaseModel
{
    protected $name = 'user_evidence';

    public function getAddTimeAttr($v)
    {
        return date('Y-m-d H:i:s', $v);
    }

    public function userinfo()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }
}
