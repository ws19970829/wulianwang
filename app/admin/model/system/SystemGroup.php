<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\model\relation\BelongsToMany;
use app\admin\model\system\SystemGroupData;

/**
 * 数据组model
 * Class SystemGroup
 * @package app\admin\model\system
 */
class SystemGroup extends BaseModel
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
    protected $name = 'system_group';

    use ModelTrait;

    /**
     * 根据id获取当前记录中的fields值
     * @param $id
     * @return array
     */
    public static function getField($id){
        $fields = json_decode(self::where('id',$id)->value("fields"),true)?:[];
        return compact('fields');
    }

    public function withData()
    {
//        return $this->belongsToMany('SystemGroupData','SystemGroupData','gid','id');
        return $this->hasMany(SystemGroupData::class,'gid','id');
    }
}