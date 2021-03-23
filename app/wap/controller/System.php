<?php
/**
 * 首页控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use think\Validate;

class System extends Common
{

    /**
     * 技术支持
     * @author Meng
     * @dateTime 2020-11-16
     * @return   [type]     [description]
     */
    public function support()
    {
        $params = input();
        $data = [
            'info' => '',
        ];
        $this->assign($data);
        return view();
    }
}
