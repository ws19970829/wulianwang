<?php

/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2018/6/14 下午5:25
 */

namespace app\admin\controller\finance;

use app\admin\controller\AuthController;
use app\admin\model\user\{User, UserArrears as UserUserArrears, UserBill};
use app\admin\model\finance\FinanceModel;
use app\models\store\StoreOrder;

use crmeb\services\{
    JsonService,
    UtilService as Util,
    FormBuilder as Form
};

use think\facade\Route as Url;
use think\Request;

/**
 * 欠款管理
 */
class UserArrears extends AuthController
{
    /**
     * 会员分组页面
     * @return string
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 分组列表
     */
    public function groupList()
    {
        $where = Util::getMore([
            ['account', ''],
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['arrears', 1],
            ['status', ''],
            ['is_promoter', ''],
            ['order', ''],
            ['data', ''],
            ['user_type', ''],
            ['country', ''],
            ['province', ''],
            ['city', ''],
            ['user_time_type', ''],
            ['user_time', ''],
            ['change_user_time', ''],
            ['price_num_min', ''],
            ['price_num_max', ''],
            ['price_min', ''],
            ['price_max', ''],
            ['unit_price_min', ''],
            ['unit_price_max', ''],
            ['sex', ''],
            ['level', ''],
            ['group_id', ''],
            ['tags', ''],
        ]);
        $return = User::getUserList($where);

        $user_list = collect($return['data']);

        $return['data'] = $user_list;
        return JsonService::successlayui($return);
    }


    public function log(Request $request)
    {
        $this->assign('id', $request->param('id'));
        return view();
    }

    /**
     * 显示可提现订单列表
     */
    public function log_list()
    {
        $where = Util::getMore([
            ['id', 0],
            ['start_time', ''],
            ['end_time', ''],
            ['order_id', ''],
            ['limit', 20],
            ['page', 1],
        ]);

        $model = new StoreOrder;
        if (!empty($where['order_id'])) {
            $model = $model->where('order_id', $where['order_id']);
        }
        $model = $model->where('uid', $where['id'])
            ->where('is_del', 0)
            ->where('status', 'in', '1,2')
            ->whereIn('order_type','2,3' )
            ->where('tenant_id', session('tenant_id'))
            ->where('paid', 1)
            ->where('refund_status', '<>', 2);
        $count = $model->count();
        $data = $model
            ->order('add_time', 'desc')
            ->page($where['page'], $where['limit'])
            ->select();
        $data = $data->append(['status_text', 'arreas'])->toArray();
        foreach ($data as &$value) {
            $value['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
        }
        return JsonService::successlayui(compact('count', 'data'));
    }

    public function evidence(Request $request)
    {
        $this->assign('id', $request->param('id'));
        return view();
    }

    /**
     * 显示可提现订单列表
     */
    public function evidence_list()
    {
        $where = Util::getMore([
            ['id', 0],
            ['limit', 20],
            ['page', 1],
        ]);

        $model = new UserUserArrears();

        $model = $model->with(['userinfo'])
            ->where('uid', $where['id'])
            ->where('is_del', 0);

        $count = $model->count();
        $data = $model
            ->order('id', 'desc')
            ->page($where['page'], $where['limit'])
            ->select()
            ->toArray();
        return JsonService::successlayui(compact('count', 'data'));
    }

    /**
     * 添加核销
     * @param int $id
     * @return string
     */
    public function add_evidence(Request $request)
    {
        $f = array();
        $f[] = Form::input('money', '核销价格', 0);
        $f[] = Form::hidden('uid', $request->param('id'));
        $f[] = Form::hidden('add_time', time());
        $f[] = Form::textarea('remark', '备注');
        $f[] = Form::frameImageOne('image', '附件图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('image')->width('100%')->height('500px');
        $form = Form::make_post_form('添加核销记录', $f, Url::buildUrl('save_evidence'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 添加核销
     * @param int $id
     * @return string
     */
    public function edit_evidence($id)
    {
        $data = UserUserArrears::where('id', $id)->find();
        $f = array();
        $f[] = Form::input('money', '核销价格', $data['money']);
        $f[] = Form::hidden('uid', $data['uid']);
        $f[] = Form::hidden('add_time', time());
        $f[] = Form::textarea('remark', '备注', $data['remark']);
        $f[] = Form::frameImageOne('image', '附件图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $data['image'])->icon('image')->width('100%')->height('500px');
        $form = Form::make_post_form('添加核销记录', $f, Url::buildUrl('save_evidence', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加/修改
     * @param int $id
     */
    public function save_evidence(Request $request)
    {

        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'money|核销价格' => 'require|float',
            'uid' => 'require|integer|gt:0',
            'image' => 'require|url',
        ]);

        if (!$validate->check($param)) {
            return JsonService::fail($validate->getError(), []);
        }
        if (empty($param['id'])) {
            UserUserArrears::create($param);
        } else {
            UserUserArrears::where('id', $param['id'])->update($param);
        }
        return JsonService::successFul('保存成功');
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');

        db('user_evidence')->where('id', $id)->update(['is_del' => 1]);
        return JsonService::successFul('删除成功');
    }
}
