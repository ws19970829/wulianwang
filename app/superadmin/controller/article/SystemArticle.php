<?php
/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/6/29
 * Time: 16:55
 * Desc: 系统文章
 */
namespace app\superadmin\controller\article;

use app\superadmin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use crmeb\services\{
    UtilService as Util, JsonService as Json
};
use app\admin\model\article\{
    SystemArticle as ArticleModel
};

/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class SystemArticle extends AuthController
{
    /**
     * TODO 显示后台管理员添加的图文
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $where = Util::getMore([
            ['title', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }

    /**
     * TODO 文件添加和修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create()
    {
        $id = $this->request->param('id');
        $news = [];
        $all = [];
        $news['id'] = '';
        $news['content'] = '';
        $news['image_input'] = '';
        $select = 0;
        if ($id) {
            $news = ArticleModel::where('n.id', $id)->alias('n')->find();
            if (!$news) return $this->failed('数据不存在!');
            $news['content'] = htmlspecialchars_decode($news['content']);
        }
        $this->assign('all', $all);
        $this->assign('news', $news);
        return $this->fetch();
    }

    /**
     * 添加和修改图文
     */
    public function add_new()
    {
        $data = Util::postMore([
            ['id', 0],
            'content',
            'image_input',
            ['status', 1],]);
        if ($data['id']) {
            $id = $data['id'];
            unset($data['id']);
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data, $id, 'id');

            ArticleModel::checkTrans($res1);
            if ($res1)
                return Json::successful('修改成功!', $id);
            else
                return Json::fail('修改失败，您并没有修改什么!', $id);
        } else {
            $data['create_time'] = time();
            $res = false;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::create($data);
            ArticleModel::checkTrans($res1);
            if ($res)
                return Json::successful('添加成功!', $res1->id);
            else
                return Json::successful('添加失败!', $res1->id);
        }
    }

    /**
     * 删除图文
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = ArticleModel::del($id);
        if (!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }

    public function merchantIndex()
    {
        $where = Util::getMore([
            ['title', '']
        ], $this->request);
        $this->assign('where', $where);
        $where['cid'] = input('cid');
        $where['merchant'] = 1;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }

    /**
     * 关联文章 id
     * @param int $id
     */
    public function relation($id = 0)
    {
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 保存选择的产品
     * @param int $id
     */
    public function edit_article($id = 0)
    {
        if (!$id) return Json::fail('缺少参数');
        list($product_id) = Util::postMore([
            ['product_id', 0]
        ], $this->request, true);
        if (ArticleModel::edit(['product_id' => $product_id], ['id' => $id]))
            return Json::successful('保存成功');
        else
            return Json::fail('保存失败');
    }

    /**
     * 取消绑定的产品id
     * @param int $id
     */
    public function unrelation($id = 0)
    {
        if (!$id) return Json::fail('缺少参数');
        if (ArticleModel::edit(['product_id' => 0], $id))
            return Json::successful('取消关联成功！');
        else
            return Json::fail('取消失败');
    }

    /**
     * 上传图文图片
     * @return \think\response\Json
     */
    public function upload_image()
    {
        $res = Upload::instance()->setUploadPath('wechat/image/' . date('Ymd'))->image($_POST['file']);
        if (!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 5, $res['image_type'], $res['time']);
        return Json::successful('上传成功!', ['url' => $res['dir']]);
    }

}