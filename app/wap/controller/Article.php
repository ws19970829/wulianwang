<?php
/**
 * 首页控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use app\admin\model\article\SystemArticle;
use app\admin\model\system\SystemConfig;
use think\Validate;

class Article extends Common
{

    /**
     * 系统文章
     * @author Meng
     * @dateTime 2020-11-16
     * @return   [type]     [description]
     */
    public function index()
    {
        $id = input('id');
        $article_info = SystemArticle::where('id',$id)->find() ?? '';
        $customer_mobile =  SystemConfig::getConfigValue('site_phone') ?? '';

        $this->assign('article_info',$article_info);
        $this->assign('customer_mobile',$customer_mobile);
        
        return view();
    }
}
