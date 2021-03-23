<?php
/**
 * 首页控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use think\Validate;

class Index extends Common
{

    /**
     * [index description]
     * @author Meng
     * @dateTime 2020-09-23
     * @return   [type]     [description]
     */
    public function index()
    {
        $params = input();
        $data = [
            'info' => '',
        ];
        $this->assign($data);
        return view();
    }

    /**
     * [index description]
     * @author Meng
     * @dateTime 2020-09-23
     * @return   [type]     [description]
     */
    public function detail()
    {
        $params = input();

        $data = [
        ];

        if (isset($params['is_debug'])) {
            halt($data);
        }

        $this->assign($data);
        return view();
    }
}
