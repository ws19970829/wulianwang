<?php


namespace app\models\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;
use think\facade\Request;

class UserCollect extends BaseModel
{

    protected $pk = 'id';

    protected $name = 'user_collect';


    public function is_collect($type, $collect)
    {

        $res = self::where('uid', Request::uid())
            ->where('type', $type)
            ->where('collect_id', $collect)
            ->count();
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }
}
