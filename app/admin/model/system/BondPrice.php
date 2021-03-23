<?php

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\admin\model\system
 */
class BondPrice extends BaseModel
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
    protected $name = 'bond_price';

    use ModelTrait;

    public static function getBondPrice()
    {
        return self::where('id',1)->value('bond_price');
    }

}