<?php

namespace app\admin\model\system;

use crmeb\services\MiniProgramService;
use crmeb\basic\BaseModel;
/**
 * TODO 小程序二维码Model
 * Class RoutineCode
 * @package app\models\routine
 */
class RoutineCode extends BaseModel
{

    /**
     * TODO 获取小程序二维码
     * @param $thirdId
     * @param $thirdType
     * @param $page
     * @param $imgUrl
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getShareCode($thirdId, $thirdType, $page, $imgUrl)
    {
        $res = RoutineQrcode::routineQrCodeForever($thirdId, $thirdType, $page, $imgUrl);
        $resCode = MiniProgramService::qrcodeService()->appCodeUnlimit($res->id, $page, 280);
        dump($resCode);exit;
        if ($resCode) {
            if ($res) return ['res' => $resCode, 'id' => $res->id];
            else return false;
        } else return false;
    }
}