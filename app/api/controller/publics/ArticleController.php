<?php

namespace app\api\controller\publics;

use app\api\controller\PublicController;
use app\models\article\Article;
use app\models\article\ArticleCategory;
use app\Request;
use crmeb\services\UtilService;

/**
 * 文章类
 * Class ArticleController
 * @package app\api\controller\publics
 */
class ArticleController
{
    /**
     * 文章列表
     * @param Request $request
     * @param $cid
     * @return mixed
     */
    public function lst(Request $request, $cid)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 1],
            ['limit', 10],
        ], $request, true);
        $list = Article::cidByArticleList($cid, $page, $limit, "id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url") ?? [];
        if (is_object($list)) $list = $list->toArray();
        return app('json')->successful($list);
    }
    /**
     * 文章详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details($id)
    {
        $content = Article::getArticleOne($id);
        if (!$content) return app('json')->fail('此文章已经不存在!');
        $content["visit"] = $content["visit"] + 1;
        $content["cart_name"] = ArticleCategory::getArticleCategoryField($content['cid']);
        $content['add_time'] = date('m月d日', $content['add_time']);
        Article::edit(['visit' => $content["visit"]], $id); //增加浏览次数
        return app('json')->successful($content);
    }

    /**
     * 商城介绍接口
     * @return mixed
     */
    public function about()
    {
        $tenant_id = input('param.tenant_id');
        if (!$tenant_id) {
            return app('json')->fail('参数不合法!');
        }

        $info = Article::where('tenant_id', '=', $tenant_id)
            ->where('cid', '=', 1)
            ->field('id,title,image_input as image,tenant_content')
            ->find();
        if (!$info) {
            $return = [
                'title' => '商城介绍',
                'image' => '',
                'content' => ''
            ];
        } else {
            $info = $info->toArray();
            $return = [
                'title' => $info['title'],
                'image' => $info['image'],
                'content' => $info['tenant_content'] ? $info['tenant_content'] : ''
            ];
        }

        return app('json')->successful($return);
    }

    /**
     * 文章 热门
     * @return mixed
     */
    public function hot()
    {
        $list = Article::getArticleListHot("id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url") ?? [];
        if (is_object($list)) $list = $list->toArray();
        return app('json')->successful($list);
    }

    /**
     * 文章 banner
     * @return mixed
     */
    public function banner()
    {
        $list = Article::getArticleListBanner("id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url") ?? [];
        if (is_object($list)) $list = $list->toArray();
        return app('json')->successful($list);
    }

    public function sys($id)
    {
        $content = db('system_article')->getFieldById($id, 'content');
        return app('json')->successful('', compact('content'));
    }
}
