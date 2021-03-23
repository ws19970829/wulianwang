<?php


namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


class SystemAdmin extends BaseModel
{

    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_admin';

    public static function getList($where)
    {
        $where_map = [
            ['level', '>', 0],
            ['status', '=', 1],
            ['is_del', '=', 0],
        ];

        $model = self::where($where_map);

        if (!empty($where['keyword'])) {
            $model = $model
                ->where('real_name', 'like', '%' . $where['keyword'] . '%');
        }
        $count = $model->count();
        $list = $model
            ->field('id,real_name,remark,mobile,addr,logo_img')
            ->order('add_time', 'desc')
            ->page($where['page'], $where['limit'])
            ->select();
        $list = $list->append(['logo_img_filter'])->toArray();
        return compact('count', 'list');
    }


    public function getLogoImgFilterAttr($v, $data)
    {
        $arr = json_decode($data['logo_img'], true);
        if (empty($arr)) return '';
        return $arr[0];
    }
}
