<?php


namespace app\models\user;

use app\models\store\StoreOrder;
use app\models\store\StoreProduct;
use crmeb\services\SystemConfigService;
use think\facade\Session;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;

/**
 * TODO 用户Model
 * Class User
 * @package app\models\user
 */
class SystemAdmin extends BaseModel
{
    use JwtAuthModelTrait;
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'system_admin';
}