<?php

namespace app\admin\controller\activity;

use app\admin\controller\AuthController;
use app\admin\model\activity\Activity;
use app\admin\model\activity\ActivityProduct;
use app\admin\model\store\StoreDescription;
use app\admin\model\store\StoreProduct;
use app\admin\controller\store\StoreProduct as ProductController;
use app\admin\model\store\StoreProductAttrResult;
use app\admin\model\store\StoreProductAttrValue;
use app\api\controller\PublicController;
use crmeb\basic\BaseModel;
use think\Request;
use think\facade\Route as Url;
use app\admin\model\store\StoreCategory as CategoryModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};

/**
 * 限时折扣活动  控制器
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class Discount extends AuthController
{

    protected $type=4;//4限时折扣，5限时秒杀，6支付有礼

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
    }


    /*
     *  异步获取分类列表
     *  @return json
     */
    public function get_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', ''],
            ['type', $this->type]
        ]);
        return Json::successlayui(Activity::getList($where));
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
        $news['image_input'] = '';
        $news['image'] = '';
        $news['title'] = '';
        $news['sec_title'] = '';
        $news['start_time'] = '';
        $news['start_time_text'] = '';
        $news['end_time'] = '';
        $news['end_time_text'] = '';
        $news['before_hour'] = 0;
        $news['before_msg'] = '';
        $news['is_cut_zero'] = 0;
        $news['limit_buy_type'] = 0;
        $news['limit_goods_num'] = 0;
        $news['limit_before_goods_num'] = 0;


        $product_list = [];
        if ($id) {
            $news = Activity::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();
            $news['start_time_text']=date('Y-m-d H:i:s',$news['start_time']);
            $news['end_time_text']=date('Y-m-d H:i:s',$news['end_time']);
            $product_list=(new ActivityProduct())->getProductListByActivityId($id);
        }

//        $product_list_json=json_encode($product_list);
        $product_list_json=json_encode($product_list);
        $this->assign('all', $all);
        $this->assign('news', $news);
        $this->assign('product_list', $product_list);
        $this->assign('product_list_json',$product_list_json);
        return $this->fetch();
    }


    /**
     * TODO 文件添加和修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function set_attr()
    {
        $id = $this->request->param('id');
        $news = [];
        $all = [];
        $news['id'] = '';

        $product_list = [];
        if ($id) {
            $news = Activity::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();
            $product_list=(new ActivityProduct())->getProductListAndAttrByActivityId($id, $this->type);//4限时折扣，5限时秒杀，6支付有礼
        }



//        $product_list_json=json_encode($product_list);
        $product_list_json=json_encode($product_list);
        $this->assign('activity_id',$id);
        $this->assign('all', $all);
        $this->assign('news', $news);
        $this->assign('product_list', $product_list);
        $this->assign('product_list_json',$product_list_json);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {

        $news['id'] = '';
        $news['image_input'] = '';
        $news['image'] = '';
        $news['title'] = '';
        $news['sec_title'] = '';
        $news['start_time'] = '';
        $news['end_time'] = '';
        $news['before_hour'] = 0;
        $news['before_msg'] = '';
        $news['is_cut_zero'] = 0;
        $news['limit_buy_type'] = 0;
        $news['limit_goods_num'] = 0;
        $news['limit_before_goods_num'] = 0;


        $data = Util::postMore([
            'id',
            'image_input',
            'title',
            ['sec_title', ''],
            'start_time',
            'end_time',
            ['before_hour', 0],
            'before_msg',
            ['is_cut_zero', 0],
            ['limit_buy_type', 0],
            ['limit_goods_num', 0],
            ['limit_before_goods_num', 0],
            ['goodsIdArr', []],
        ], $request);

//        dump($data);


        if (!$data['title']) return Json::fail('请输入标题');
        if (!$data['start_time'] || !$data['end_time']) return Json::fail('请设置起止时间');
        if (!$data['image_input']) return Json::fail('请上传图片');
        if (!$data['goodsIdArr']) return Json::fail('请添加商品');
        if ($data['limit_buy_type']==1 && !$data['limit_goods_num']) return Json::fail('请设置限购数量');
        if ($data['limit_buy_type']==2 && !$data['limit_before_goods_num']) return Json::fail('请设置前几次享受折扣数量');


        $data['image']=$data['image_input'];
        unset($data['image_input']);
        $goodsIdArr=$data['goodsIdArr'];

        $data['start_time']=$activity_start_time=strtotime($data['start_time']);
        $data['end_time']=$activity_end_time=strtotime($data['end_time']);
        $limit_buy_type=$data['limit_buy_type'];
        $data['add_time'] = time();
        $data['tenant_id'] = session('tenant_id');
        $data['type'] =  $this->type;//4限时折扣，5限时秒杀，6支付有礼s



        BaseModel::beginTrans();

        if($data['id']){
            //编辑数据
            $id=$data['id'];
            unset($data['id']);

            //先取出本活动之前的商品id
            $old_product_ids_arr=(new ActivityProduct())->where('activity_id','=',$id)->field('product_id')->select();
            if(count($old_product_ids_arr)>0){
                $old_product_ids_arr=$old_product_ids_arr->column('product_id');
            }else{
                $old_product_ids_arr='';
            }

            //旧商品如果已经不在新提交的商品中，那么将旧商品储存的规格进行清空
            foreach($old_product_ids_arr as $v){
                if(!in_array($v,$goodsIdArr)){
                    $where=[
                        'activity_id'=>$id,
                        'type'=> $this->type,//4限时折扣，5限时秒杀，6支付有礼
                        'product_id'=>$v
                    ];
                    StoreProductAttrValue::where($where)->delete();
                    StoreProductAttrResult::where($where)->delete();


                }
            }

            //如果新提交的商品，是旧商品中不存在的，那么将新商品的规格进行复制一份，存为活动规格
            foreach($goodsIdArr as $goods_id){
                if(!in_array($goods_id,$old_product_ids_arr)){
                    $where=[
                        'type'=>0,
                        'product_id'=>$goods_id
                    ];

                    //获取商品的原始规格属性-单属性规格
                    $attr_value=StoreProductAttrValue::where($where)->select();
                    if(count($attr_value)>0){
                        $attr_value=$attr_value->toArray();
                        //更改数组中的type类型和活动id，作为活动规格属性
                        foreach($attr_value as $k=>$v){
                            $attr_value[$k]['type']= $this->type;//4限时折扣，5限时秒杀，6支付有礼
                            $attr_value[$k]['activity_id']=$id;
                        }
                        $res=(new StoreProductAttrValue())->saveAll($attr_value);
                        if(!$res){
                            BaseModel::rollbackTrans();
                            return Json::fail('存储规格单属性时失败');
                        }
                    }

                    //获取商品的原始规格属性-多属性规格
                    $attr_value=StoreProductAttrResult::where($where)
                        ->select();
                    if(count($attr_value)>0){
                        $attr_value=$attr_value->toArray();
                        //更改数组中的type类型和活动id，作为活动规格属性
                        foreach($attr_value as $k=>$v){
                            $attr_value[$k]['type']= $this->type;//4限时折扣，5限时秒杀，6支付有礼
                            $attr_value[$k]['activity_id']=$id;
                        }
                        $res=(new StoreProductAttrResult())->saveAll($attr_value);
                        if(!$res){
                            BaseModel::rollbackTrans();
                            return Json::fail('存储规格多属性时失败');
                        }
                    }


                }
            }


            $res=Activity::edit($data,$id,'id');
            if(!$res){
                BaseModel::rollbackTrans();
                return Json::fail('数据更新时失败');
            }

            //更新商品和活动的关联数据-先清空已有数据
            (new ActivityProduct())->where('activity_id','=',$id)->delete();
            $data=[];
            //todo 这里要把商品的详细信息都存起来
            foreach($goodsIdArr as $val){
                $product_info=StoreProduct::where('id','=',$val)->find();
                $product_info=$product_info->toArray();
                $tem['activity_id']=$id;
                $tem['product_id']=$val;
                $tem['image']=$product_info['image'];
                $tem['images']=$product_info['slider_image'];
                $tem['title']=$product_info['store_name'];//商品名称
                $tem['info']=$product_info['store_info'];//商品名称
                $tem['price']=$product_info['price'];//价格
                $tem['cost']=$product_info['cost'];//成本价格
                $tem['ot_price']=$product_info['ot_price'];//原价价格
                $tem['give_integral']=$product_info['give_integral'];//赠送积分
                $tem['stock']=$product_info['stock'];//库存
                $tem['sales']=$product_info['sales'];//销量
                $tem['unit_name']=$product_info['unit_name'];//单位
                $tem['postage']=$product_info['postage'];//运费
                $tem['description']=htmlspecialchars_decode(StoreDescription::getDescription($val, 1));
                $tem['start_time']=$activity_start_time;//开始时间
                $tem['stop_time']=$activity_end_time;//结束时间
                $tem['status']=1;//状态
                $tem['is_show']=1;//显示状态
                $tem['is_del']=0;
                $tem['is_postage']=$product_info['is_postage'];//是否包邮
                $tem['num']=$limit_buy_type;//最多购买几个
                $tem['temp_id']=$product_info['temp_id'];//运费模板
                $tem['add_time']=time();
                $tem['type']= $this->type;
                $data[]=$tem;
            }
            $res=(new ActivityProduct())->saveAll($data);
            if(!$res){
                BaseModel::rollbackTrans();
                return Json::fail('保存商品表失败');
            }


        }else{
            //新增活动
            unset($data['id']);
            $res=Activity::create($data);
            if($res){
                $activity_id=$res->getData('id');
                //处理商品和笔记的关联数据
                $data=[];
                foreach($goodsIdArr as $val){
                    $product_info=StoreProduct::where('id','=',$val)->find();
                    $product_info=$product_info->toArray();
                    $tem['activity_id']=$activity_id;
                    $tem['product_id']=$val;
                    $tem['image']=$product_info['image'];
                    $tem['images']=$product_info['slider_image'];
                    $tem['title']=$product_info['store_name'];//商品名称
                    $tem['info']=$product_info['store_info'];//商品名称
                    $tem['price']=$product_info['price'];//价格
                    $tem['cost']=$product_info['cost'];//成本价格
                    $tem['ot_price']=$product_info['ot_price'];//原价价格
                    $tem['give_integral']=$product_info['give_integral'];//赠送积分
                    $tem['stock']=$product_info['stock'];//库存
                    $tem['sales']=$product_info['sales'];//销量
                    $tem['unit_name']=$product_info['unit_name'];//单位
                    $tem['postage']=$product_info['postage'];//运费
                    $tem['description']=htmlspecialchars_decode(StoreDescription::getDescription($val, 1));
                    $tem['start_time']=$activity_start_time;//开始时间
                    $tem['stop_time']=$activity_end_time;//结束时间
                    $tem['status']=1;//状态
                    $tem['is_show']=1;//显示状态
                    $tem['is_del']=0;
                    $tem['is_postage']=$product_info['is_postage'];//是否包邮
                    $tem['num']=$limit_buy_type;//最多购买几个
                    $tem['temp_id']=$product_info['temp_id'];//运费模板
                    $tem['add_time']=time();
                    $tem['type']= $this->type;
                    $data[]=$tem;
                }

                //todo 这里要把商品的详细信息都存起来
                $res=(new ActivityProduct())->saveAll($data);
                if(!$res){
                    BaseModel::rollbackTrans();
                    return Json::fail('保存失败');
                }


                //获取商品的原始规格属性-单属性规格
                $attr_value=StoreProductAttrValue::where('type','=',0)
                    ->where('product_id','in',$goodsIdArr)
                    ->select();
                if(count($attr_value)>0){
                    $attr_value=$attr_value->toArray();
                    //更改数组中的type类型和活动id，作为活动规格属性
                    foreach($attr_value as $k=>$v){
                        $attr_value[$k]['type']= $this->type;//4限时折扣，5限时秒杀，6支付有礼
                        $attr_value[$k]['activity_id']=$activity_id;
                    }
                    $res=(new StoreProductAttrValue())->saveAll($attr_value);
                    if(!$res){
                        BaseModel::rollbackTrans();
                        return Json::fail('存储规格单属性时失败');
                    }
                }

                //获取商品的原始规格属性-多属性规格
                $attr_value=StoreProductAttrResult::where('type','=',0)
                    ->where('product_id','in',$goodsIdArr)

                    ->select();
                if(count($attr_value)>0){
                    $attr_value=$attr_value->toArray();
                    //更改数组中的type类型和活动id，作为活动规格属性
                    foreach($attr_value as $k=>$v){
                        $attr_value[$k]['type']= $this->type;//4限时折扣，5限时秒杀，6支付有礼
                        $attr_value[$k]['activity_id']=$activity_id;
                    }
                    $res=(new StoreProductAttrResult())->saveAll($attr_value);
                    if(!$res){
                        BaseModel::rollbackTrans();
                        return Json::fail('存储规格多属性时失败');
                    }
                }

            }else{
                BaseModel::rollbackTrans();
                return Json::fail('保存失败');
            }
        }



        BaseModel::commitTrans();
        return Json::successful('保存成功!');
    }

    //todo:存储活动商品的属性
    public function save_attr(){

        if(!input('param.activity_id')){
            return Json::fail('活动ID格式不正确，请稍后再试');
        }
        $activity_id=input('param.activity_id');
        $attr_list=input('param.attr_list');
        $attr_list=json_decode($attr_list,true);
        $key=[];
        $temp=[];
        foreach($attr_list as  $val){
            if(!in_array($val['name'],$key)){
                //新键值
                $temp[$val['name']]=$val['value'];
                $key[]=$val['name'];
            }else{
                //已有的键值
                $temp[$val['name']].=','.$val['value'];
            }
        }

        $data=[];
        $activity_price_arr=explode(',',$temp['activity_price']);
        $activity_stock_arr=explode(',',$temp['activity_stock']);
        $product_arr=explode(',',$temp['product']);

        if(count($activity_stock_arr)!=count($activity_price_arr) && count($activity_price_arr)!=count($product_arr)){
            return Json::fail('参数数量不匹配，请刷新重试');
        }

        //将规格数组重新组合
        for($i=0;$i<count($activity_price_arr);$i++){
                $data[$i]['activity_price']=$activity_price_arr[$i];
                $data[$i]['activity_stock']=$activity_stock_arr[$i];
                $data[$i]['product']=$product_arr[$i];
        }
        $attr_data=[];
        foreach($data as $val){
            $attr_data[$val['product']][]=$val;
        }


        foreach($attr_data as $val){
            $result = StoreProductAttrResult::getResult($val[0]['product'],  $this->type,$activity_id);
            if(count($result)>0){
                for($i=0;$i<count($val);$i++){
                    //获取到当前产品下的原始结果
                    $result['value'][$i]['activity_price']=$val[$i]['activity_price'];
                    $result['value'][$i]['activity_stock']=$val[$i]['activity_stock'];

                    //更改活动产品表中的价格
                    $data=[
                        'price'=>$val[$i]['activity_price'],
                        'stock'=>$val[$i]['activity_stock'],
                    ];
                    ActivityProduct::where('product_id','=',$val[0]['product'])
                        ->where('activity_id','=',$activity_id)
                        ->update($data);

                    $suk = implode(',', $result['value'][$i]['detail']);
                    //测试中发现suk会因为detail里规格顺序的不同 而和value表中的suk字段逗号分隔后有顺序差异，所以把数组反向排序以后也拆分为字符串，用in来查找符合规格的value记录
                    $suk_reverse_sort = implode(',', array_reverse($result['value'][$i]['detail']));

                    $value_data=[
                        'activity_price'=>$val[$i]['activity_price'],
                        'activity_stock'=>$val[$i]['activity_stock']
                    ];

                    $where_arr=[$suk,$suk_reverse_sort];
                    //逐个更新value表的数据
                    StoreProductAttrValue::where('product_id','=',$val[0]['product'])
                        ->where('type','=',$this->type)
                        ->where('activity_id','=',$activity_id)
                        ->where('suk','in',$where_arr)
                        ->update($value_data);
                }
                StoreProductAttrResult::where('product_id','=',$val[0]['product'])
                    ->where('type','=', $this->type)
                    ->where('activity_id','=',$activity_id)
                    ->data(['result'=>json_encode($result)])
                    ->update();


            }else{
                return Json::fail('获取活动数据失败，请稍后再试');

            }

        }
        return Json::successful('保存成功!');

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $c = CategoryModel::get($id);
        if (!$c) return Json::fail('数据不存在!');
        $field = [
            Form::select('pid', '父级', (string)$c->getData('pid'))->setOptions(function () use ($id) {
                $list = CategoryModel::getTierList(CategoryModel::where('id', '<>', $id), 0);
//                $list = (sort_list_tier((CategoryModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','cate_name'));
                $menus = [['value' => 0, 'label' => '顶级菜单']];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name', '分类名称', $c->getData('cate_name')),
            Form::frameImageOne('pic', '分类图标', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')), $c->getData('pic'))->icon('image')->width('100%')->height('500px'),
            Form::number('sort', '排序', $c->getData('sort')),
            Form::radio('is_show', '状态', $c->getData('is_show'))->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])
        ];
        $form = Form::make_post_form('编辑分类', $field, Url::buildUrl('update', array('id' => $id)), 2);

        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function set_status($status= '' ,$id=''){
        ($status == '' || $id == '') && Json::fail('缺少参数');
        $res = Activity::where(['id' => $id])->update(['status' => (int)$status]);
        if ($res) {
            return Json::successful($status == 1 ? '开启成功' : '关闭成功');
        } else {
            return Json::fail($status == 1 ? '开启失败' : '关闭失败');
        }
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'pid',
            'cate_name',
            ['pic', []],
            'sort',
            ['is_show', 0]
        ], $request);
        if ($data['pid'] == '') return Json::fail('请选择父类');
        if (!$data['cate_name']) return Json::fail('请输入分类名称');
        if (count($data['pic']) < 1) return Json::fail('请上传分类图标');
        if ($data['sort'] < 0) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        CategoryModel::edit($data, $id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!Activity::where('id','=',$id)->update(['is_del'=>1]))
            return Json::fail(Activity::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 选择商品
     * @param int $id
     */
    public function select()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }


    /**
     * 选择商品提交并存入缓存
     */
    public function save_cache(){
        $ids=input('param.ids');
        $checked_ids=input('param.checked_ids');

        $cache_key='select_product_'.session('tenant_id');
        if($ids){
            $ids=implode(',',$ids);
            if($checked_ids){
                $ids.=','.$checked_ids;
            }
            $where=['ids'=>$ids,];
            $product_list=StoreProduct::ProductListBySelect($where);
        }else{
            $product_list=[];
        }
        cache($cache_key,null);
        cache($cache_key,$product_list);

        return Json::successful('提交成功!');
    }

    public function get_cache(){
        $cache_key='select_product_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }


    /**
     * 获取商品的推广海报
     * @return string
     */
    public function extension(){
        $activity_id=input('param.id');
        $img_info=$this->get_activity_share_img($activity_id);
        $this->assign('id',$activity_id);
        $this->assign('img',$img_info['img']);
        $this->assign('name',$img_info['name']);
        $this->assign('url',$img_info['url']);
        return $this->fetch();
    }

    /**
     * 生成活动的宣传海报
     * @param $activity_id
     * @return mixed
     */
    function get_activity_share_img($activity_id)
    {

        //初始化设置
        $activity_info=Activity::where('id','=',$activity_id)->find();
        $tenant_id=$activity_info['tenant_id'];
        $activity_type=$activity_info['type'];
        switch ($activity_type){
            case 4:
                $activity_type='discount';
                $title='限时折扣';
                break;
            case 5:
                $activity_type='seckill';
                $title='限时秒杀';
                break;
            case 6:
                $activity_type='gift';
                $title='支付有礼';
                break;
            default:
                $activity_type='';
                $title='活动';
        }


        $font='static/font/STHeiti.ttf';
        $bigImgPath=$_SERVER['DOCUMENT_ROOT'].'/system/images/background.jpg';
        $main = imagecreatefromjpeg ( $bigImgPath );
        $width = imagesx ( $main );//背景图宽度
        $height = imagesy ( $main );//背景图高

        $publicController=new PublicController();

        //获得发布时间
        // $date = date('Y/m/d', $member_info->join_timestamp);

        //获得二维码路径
        $site_url=$publicController->getSysConfigValue('site_url',$tenant_id);
        $site_url=str_replace('\\', '/', $site_url);
        $site_url=str_replace('////', '//', $site_url);
        $site_url = $site_url? $site_url : 'http://xiaohuixiang.3todo.com';
        $url = $site_url.'/'.'classifyGoods?type='.$activity_type.'&title='.$title.'&tenant_id='.$tenant_id;


        $qCodePath = $this->get_url_qrcode($url,$activity_id,'activity_',7);
        $_img=$qCodePath['file_name'];
        $path=$qCodePath['path'].'/head_logo';

        //将二维码合成背景图片
        //读取背景图片数据流
        $bigImg = imagecreatefromstring(file_get_contents($bigImgPath));
        //读取二维码数据流
        $qCodeImg = imagecreatefromstring(file_get_contents($qCodePath['file_name']));

        list($qCodeWidth, $qCodeHight) = getimagesize($qCodePath['file_name']);
        imagecopymerge($bigImg, $qCodeImg, 385, 1000, 0, 0, $qCodeWidth, $qCodeHight, 100);


        # 商城标志
        //如果未上传logo，就使用默认logo；
        $shop_logo=$publicController->getSysConfigValue('routine_index_logo',$tenant_id);
        $shop_logo=$shop_logo?$shop_logo:config('site.default_logo');
        $shop_logo=str_replace('\\', '/', $shop_logo);
        $shop_logo=str_replace('////', '//', $shop_logo);
        $avatar = $this->scaleImg($shop_logo, $path, 130, 130);
        $icon = imagecreatefromstring(file_get_contents($avatar));
        list($icon_width, $icon_hight) = getimagesize($avatar);
        imagecopymerge($bigImg, $icon, 100, 30, 0, 0, $icon_width, $icon_hight, 100);

        // 字体颜色
        $black = imagecolorallocate($bigImg, 0, 0, 0);
        $yellow = imagecolorallocate($bigImg, 255, 181, 15);
        $red = imagecolorallocate($bigImg, 255, 0, 0);
        $grey = imagecolorallocate($bigImg, 100, 100, 100);//越小灰度越深

        //商城名称
        $site_name=$publicController->getSysConfigValue('site_name',$tenant_id);
        $site_name=json_decode($site_name,true);
        $site_name=$site_name?$site_name:config('site.site_name');
        imagettftext ( $bigImg, 40, 0, 280, 90, $black, $font, $site_name );


        $activity_name=$activity_info['title'];
        $activity_image=$activity_info['image'];

        # 活动名称
        imagettftext ( $bigImg, 28, 0, 280,150, $black, $font, $activity_name );


        //活动时间，居中
        $activity_time='活动时间: '.date('Y/m/d H:i',$activity_info['start_time']).' ~ '.date('Y/m/d H:i',$activity_info['end_time']);
        $fontBox = imagettfbbox(25, 0, $font, $activity_time);//文字水平居中实质
        imagettftext ( $bigImg, 28, 0, ceil(($width - $fontBox[2]) / 2)-60, 860, $black, $font, $activity_time );


        # 活动图片
        $activity_image = $this->scaleImg($activity_image, $path, 830, 450);
        $icon = imagecreatefromstring(file_get_contents($activity_image));
        list($icon_width, $icon_hight) = getimagesize($activity_image);
        //图片居中
        imagecopymerge($bigImg, $icon, ceil(($width - $icon_width) / 2), 290, 0, 0, $icon_width, $icon_hight, 100);

        imagepng($bigImg, $_img);
        // @imagedestroy($image);
        @imagedestroy($bigImg);

        $return=[
            'img'=>$_img,
            'name'=>$activity_image,
            'url'=>$url
        ];
        return $return;
    }
}
