<?php

namespace app\superadmin\controller\store;

use app\superadmin\model\system\BondPrice;
use app\superadmin\model\system\MarginManagement;
use app\superadmin\model\store\StoreProduct;
use Yansongda\Pay\Pay as YanSongDaPay;
use app\superadmin\controller\AuthController;
use app\superadmin\model\store\{
    StoreDescription,
    StoreProductAttrValue,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProductCate,
    StoreProductRelation,
    StoreCategory as CategoryModel,
    StoreProduct as ProductModel
};
use think\facade\Session;
use app\superadmin\model\ump\StoreBargain;
use app\superadmin\model\ump\StoreCombination;
use app\superadmin\model\ump\StoreSeckill;
use crmeb\services\{
    JsonService, UtilService as Util, JsonService as Json, FormBuilder as Form
};
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\superadmin\model\system\{
    SystemAttachment, ShippingTemplates,RoutineCode as RoutineCodeModel
};
use Endroid\QrCode\QrCode;


/**
 * 产品管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class DifferentStoreProductExamine extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $adminInfo = $this->adminId;
        $type = $this->request->param('type', 1);
        //获取分类
        $this->assign('cate', CategoryModel::getTierList(null, 1));
        //已通过
        $onsale = ProductModel::where('is_del', 0)
            ->where('to_examine',1)
            ->where('is_different',1)
            ->count();
        //未审核
        $forsale = ProductModel::where('is_del', 0)
            ->where('to_examine',0)
            ->where('is_different',1)
            ->count();
        //未通过
        $unsale = ProductModel::where('is_del', 0)
            ->where('to_examine',-1)
            ->where('is_different',1)
            ->count();
        $this->assign(compact('type', 'onsale', 'forsale','unsale'));
        return $this->fetch();

    }

    /**
     * 异步查找产品
     *
     * @return json
     */
    public function product_ist()
    {
        $adminId = $this->adminId;
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['store_name', ''],
            ['cate_id', ''],
            ['excel', 0],
            ['order', ''],
            ['to_examine', ''],
            [['mer_id', ''], $adminId]
        ]);
        return Json::successlayui(ProductModel::ProductListToExamine($where));
    }

    public function see($id = 0)
    {
        $this->assign('id', (int)$id);
        return $this->fetch();
    }

    /**
     * 获取规则属性模板
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_rule()
    {
        return Json::successful(\app\models\store\StoreProductRule::field(['rule_name', 'rule_value'])->select()->each(function ($item) {
            $item['rule_value'] = json_decode($item['rule_value'], true);
        })->toArray());
    }

    /**
     * 获取产品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_product_info($id = 0)
    {
        $list = CategoryModel::getTierList(null, 1);
        $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1];//,'disabled'=>$menu['pid']== 0];
        }
        $data['tempList'] = ShippingTemplates::order('sort', 'desc')->field(['id', 'name'])->select()->toArray();
        $data['cateList'] = $menus;
        $data['productInfo'] = [];
        if ($id) {
            $productInfo = ProductModel::get($id);
            if (!$productInfo) {
                return Json::fail('修改的产品不存在');
            }
            $productInfo['cate_id'] = explode(',', $productInfo['cate_id']);
            $productInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id));
            $productInfo['slider_image'] = is_string($productInfo['slider_image']) ? json_decode($productInfo['slider_image'], true) : [];
            if ($productInfo['spec_type'] == 1) {
                $result = StoreProductAttrResult::getResult($id, 0);
                foreach ($result['value'] as $k => $v) {
                    $num = 1;
                    foreach ($v['detail'] as $dv) {
                        $result['value'][$k]['value' . $num] = $dv;
                        $num++;
                    }
                }
                $productInfo['items'] = $result['attr'];
                $productInfo['attrs'] = $result['value'];
                $productInfo['attr'] = ['pic' => '', 'price' => 0, 'cost' => 0, 'ot_price' => 0, 'stock' => 0, 'bar_code' => '', 'weight' => 0, 'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0];
            } else {
                $result = StoreProductAttrValue::where('product_id', $id)->where('type', 0)->find();
                if ($result) {
                    $single = $result->toArray();
                } else {
                    $single = [];
                }
                $productInfo['items'] = [];
                $productInfo['attrs'] = [];
                $productInfo['attr'] = [
                    'pic' => $single['image'] ?? '',
                    'price' => $single['price'] ?? 0,
                    'cost' => $single['cost'] ?? 0,
                    'ot_price' => $single['ot_price'] ?? 0,
                    'stock' => $single['stock'] ?? 0,
                    'bar_code' => $single['bar_code'] ?? '',
                    'weight' => $single['weight'] ?? 0,
                    'volume' => $single['volume'] ?? 0,
                    'brokerage' => $single['brokerage'] ?? 0,
                    'brokerage_two' => $single['brokerage_two'] ?? 0,
                ];
            }
            if ($productInfo['activity']) {
                $activity = explode(',', $productInfo['activity']);
                foreach ($activity as $k => $v) {
                    if ($v == 1) {
                        $activity[$k] = '秒杀';
                    } elseif ($v == 2) {
                        $activity[$k] = '砍价';
                    } elseif ($v == 3) {
                        $activity[$k] = '拼团';
                    }
                }
                $productInfo['activity'] = $activity;
            } else {
                $productInfo['activity'] = ['秒杀', '砍价', '拼团'];
            }
            $data['productInfo'] = $productInfo;
        }
        return JsonService::successful($data);
    }

    /**
     * 生成属性
     * @param int $id
     */
    public function is_format_attr($id = 0, $type = 0)
    {
        $data = Util::postMore([
            ['attrs', []],
            ['items', []]
        ]);
        $attr = $data['attrs'];
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $types = 1;
            if ($id) {
                $sukValue = StoreProductAttrValue::where('product_id', $id)->where('type', 0)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two', 'suk');
                if (!count($sukValue)) {
                    if ($type == 0) $types = 0; //编辑商品时，将没有规格的数据不生成默认值
                    $sukValue[$suk]['pic'] = '';
                    $sukValue[$suk]['price'] = 0;
                    $sukValue[$suk]['cost'] = 0;
                    $sukValue[$suk]['ot_price'] = 0;
                    $sukValue[$suk]['stock'] = 0;
                    $sukValue[$suk]['bar_code'] = '';
                    $sukValue[$suk]['weight'] = 0;
                    $sukValue[$suk]['volume'] = 0;
                    $sukValue[$suk]['brokerage'] = 0;
                    $sukValue[$suk]['brokerage_two'] = 0;
                }
            } else {
                $sukValue[$suk]['pic'] = '';
                $sukValue[$suk]['price'] = 0;
                $sukValue[$suk]['cost'] = 0;
                $sukValue[$suk]['ot_price'] = 0;
                $sukValue[$suk]['stock'] = 0;
                $sukValue[$suk]['bar_code'] = '';
                $sukValue[$suk]['weight'] = 0;
                $sukValue[$suk]['volume'] = 0;
                $sukValue[$suk]['brokerage'] = 0;
                $sukValue[$suk]['brokerage_two'] = 0;
            }
            if ($types) { //编辑商品时，将没有规格的数据不生成默认值
                foreach (array_keys($detail) as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 130;
                }
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ?? 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ?? 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ?? 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ?? 0;
                $count++;
            }
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '原价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '产品编号', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '重量(KG)', 'slot' => 'weight', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '体积(m³)', 'slot' => 'volume', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '操作', 'slot' => 'action', 'align' => 'center', 'minWidth' => 70];
        $info = ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
        return Json::successful($info);
    }

    /**
     * 检测商品是否开活动
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check_activity($id)
    {
        if ($id != 0) {
            $res1 = StoreSeckill::where('product_id', $id)->where('is_del', 0)->find();
            $res2 = StoreBargain::where('product_id', $id)->where('is_del', 0)->find();
            $res3 = StoreCombination::where('product_id', $id)->where('is_del', 0)->find();
            if ($res1 || $res2 || $res3) {
                return Json::successful('该商品有活动开启，无法删除属性');
            } else {
                return Json::fail('删除成功');
            }
        } else {
            return Json::fail('没有参数ID');
        }
    }

    public function is_starting($id = "")
    {
        if($id == ""){
            return Json::fail('没有参数ID');
        }else{
            $result = StoreProduct::where('id',$id)->update(['to_examine'=>1]);
            if($result){
                return Json::successful('审核成功');
            }else{
                return Json::fail('审核失败，请重试');
            }
        }
    }

    public function is_no_starting($id = "")
    {
        if(request()->isAjax()){
            if($id == ""){
                return Json::fail('没有参数ID');
            }else{
                $result = StoreProduct::where('id',$id)->update(['to_examine'=>-1,'examine_remark'=>request()->param()['examine_remark']]);
                if($result){
                    return json(['code'=>200,'msg'=>'驳回成功']);
                }else{
                    return json(['code'=>400,'msg'=>'提交失败，请重试']);
                }
            }
        }

        $this->assign('id',$id);
        return $this->fetch();
    }
}