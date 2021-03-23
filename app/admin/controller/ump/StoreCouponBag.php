<?php

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use app\admin\model\ump\StoreCouponBagIssue;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\api\controller\PublicController;
use crmeb\basic\BaseModel;
use think\facade\Route as Url;
use app\admin\model\wechat\WechatUser as UserModel;
use app\admin\model\ump\{StoreCouponIssue, StoreCoupon as CouponModel};
use crmeb\services\{FormBuilder as Form, UtilService as Util, JsonService as Json};

/**
 * 优惠券礼包控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCouponBag extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['type','']
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(\app\admin\model\ump\StoreCouponBag::systemPage($where));
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $id = $this->request->param('id');
        $news = [];
        $all = [];
        $news['id'] = '';
        $news['title'] = '';
        $news['type'] = 1;
        $news['is_long'] = 1;
        $news['start_time'] = '';
        $news['end_time'] = '';
        $news['is_coupon'] = 0;
        $news['note_id'] = 0;
        $news['note_info'] = [];
        $news['note_info_json'] = '';
        $news['coupon_id'] = 0;
        $news['coupon_info'] = [];
        $news['coupon_info_json'] = '';
        $news['user_tag_list'] = [];
        $news['user_tag_json'] = '';
        $news['is_public'] = 0;

        if ($id) {
            $news = FansPlanModel::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();
            $news['start_time']=$news['start_time']?date('Y-m-d H:i:s',$news['start_time']):'';
            $news['end_time']=$news['end_time']?date('Y-m-d H:i:s',$news['end_time']):'';
            //关联的笔记详情-使用二维数组
            $note_info=(new \app\admin\model\fans\FansNote())->where('id','=',$news['note_id'])->select();
            $news['note_info']=$note_info?$note_info->toArray():[];
            $news['note_info_json']=json_encode($news['note_info']);

            //关联的优惠券
            $coupon_issue_list=(new FansCouponIssue())->where('fans_plan_id','=',$id)->select();
            if(count($coupon_issue_list)){
                $coupon_issue_list=$coupon_issue_list->column('coupon_issue_id');
                $coupon_issue_ids=implode($coupon_issue_list,',');
                $where=['ids'=>$coupon_issue_ids];
                $model=StoreCouponIssue::getModelToSelect($where);
                $coupon_issue_list=$model->select();
                if(count($coupon_issue_list)){
                    $coupon_issue_list=$coupon_issue_list->toArray();
                    foreach($coupon_issue_list as $key=>$val){
                        switch ($val['type']){
                            case 0:
                                $coupon_issue_list[$key]['type_text']='平台券';
                                break;
                            case 1:
                                $coupon_issue_list[$key]['type_text']='品类券';
                                break;
                            default:
                                $coupon_issue_list[$key]['type_text']='商品券';
                        }

                        if($val['start_time']>0){
                            $coupon_issue_list[$key]['time_text']=date('Y-m-d',$val['start_time']).'至'.date('Y-m-d',$val['end_time']);
                        }else{
                            $coupon_issue_list[$key]['time_text']='不限时';
                        }

                        if($val['is_permanent']){
                            $coupon_issue_list[$key]['count_text']='不限量';
                        }else{
                            $coupon_issue_list[$key]['count_text']='<b style="color: #0a6aa1">发布:'.$val["total_count"].'</b><br/><b style="color:#ff0000;">剩余:'.$val["remain_count"].'</b>';
                        }
                    }
                }else{
                    $coupon_issue_list=[];
                }
            }else{
                $coupon_issue_list=[];
            }
            $news['coupon_info']=$coupon_issue_list;
            $news['coupon_info_json']=json_encode($coupon_issue_list);



            //处理关联的用户标签
            $user_tag_list=(new FansUserTag())->where('fans_plan_id','=',$id)->select();
            if(count($user_tag_list)){
                $user_tag_list=$user_tag_list->column('user_tag_id');
                $user_tag_ids=implode($user_tag_list,',');
                $where=['ids'=>$user_tag_ids];
                $user_tags_list=UserTag::getSytemListToSelect($where);
            }else{
                $user_tags_list=[];
            }

            $news['user_tag_list']=$user_tags_list;
            $news['user_tag_json']=json_encode($user_tags_list);

        }


        $this->assign('all', $all);
        $this->assign('news', $news);
        return $this->fetch();
    }


    /**
     * 选择笔记弹窗
     * @return string
     */
    public function select_coupon()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }


    public function save_cache_coupon(){
        $ids=input('param.ids');
        $checked_ids=input('param.checked_ids');

        $cache_key='select_coupon_'.session('tenant_id');
        if($ids){
            $ids=implode(',',$ids);
            if($checked_ids){
                $ids.=','.$checked_ids;
            }
            $where=[
                'ids'=>$ids,
                'is_bag'=>1//优惠券礼包选择
            ];
            $model=StoreCouponIssue::getModelToSelect($where);
            $product_list=$model->select();

            if(count($product_list)){
                $product_list=$product_list->toArray();
                foreach($product_list as $key=>$val){
                    switch ($val['type']){
                        case 0:
                            $product_list[$key]['type_text']='平台券';
                            break;
                        case 1:
                            $product_list[$key]['type_text']='品类券';
                            break;
                        default:
                            $product_list[$key]['type_text']='商品券';
                    }

                    if($val['start_time']>0){
                        $product_list[$key]['time_text']=date('Y-m-d',$val['start_time']).'至'.date('Y-m-d',$val['end_time']);
                    }else{
                        $product_list[$key]['time_text']='不限时';
                    }
                    if($val['is_permanent']){
                        $product_list[$key]['count_text']='不限量';
                    }else{
                        $product_list[$key]['count_text']='<b style="color: #0a6aa1">发布:'.$val["total_count"].'</b><br/><b style="color:#ff0000;">剩余:'.$val["remain_count"].'</b>';
                    }

                    if($val['is_public']){
                        $product_list[$key]['is_public']='是';
                    }else{
                        $product_list[$key]['is_public']='否';
                    }

                    if($val['is_bag']){
                        $product_list[$key]['is_bag']='是';
                    }else{
                        $product_list[$key]['is_bag']='否';
                    }
                }
            }else{
                $product_list=[];
            }
        }else{
            $product_list=[];
        }

        cache($cache_key,null);
        cache($cache_key,$product_list);

        return Json::successful('提交成功!');
    }

    public function get_cache_coupon(){
        $cache_key='select_coupon_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }


    /**
     * 选择商品
     * @param int $id
     */
    public function select()
    {
        return $this->fetch();
    }

    public function view_issue(){
        $where = Util::getMore([
            ['status', ''],
            ['coupon_title', ''],
            ['type','']
        ]);

        $coupon_bag_id=input('param.coupon_bag_id');
        $issue=StoreCouponBagIssue::where('coupon_bag_id','=',$coupon_bag_id)
            ->field('coupon_issue_id')
            ->select();
        $ids=[];
        if(count($issue)>0){
            $ids=$issue->column('coupon_issue_id');
        }
        $this->assign(StoreCouponIssue::stsypageToBagIssue($where,$ids));
        $this->assign('where', $where);
        return $this->fetch();
    }

    /**
     * 保存
     */
    public function save()
    {
        $data = Util::postMore([
            'title',
            'id',
            ['type', 0],
            ['total_count', 0],
            ['is_public', 0],
            ['coupon_ids',[]]
        ]);


        if (!in_array($data['type'],[1,2])) return Json::fail('优惠券礼包类型有误');
        if (!$data['title']) return Json::fail('请输入礼包名称');


        if(!$data['coupon_ids']){
            return Json::fail('选择优惠券');
        }

        if(!$data['total_count']){
            return Json::fail('请设置发放上限');
        }


        $couponIdsArr=$data['coupon_ids'];
        $data['tenant_id']=session('tenant_id');
        $data['add_time']=time();
        $data['remain_count']=$data['total_count'];
        $data['status']=1;

        BaseModel::beginTrans();
        if($data['id']){
            $id=$data['id'];
            unset($data['id']);
            \app\admin\model\ump\StoreCouponBag::edit($data,$id,'id');

            $coupon_data=[];
            //处理礼包和笔记的关联数据-先清空已有数据
            (new StoreCouponBagIssue())->where('coupon_bag_id','=',$id)->delete();
            foreach($couponIdsArr as $val){
                $tem['coupon_bag_id']=$id;
                $tem['coupon_issue_id']=$val;
                $tem['add_time']=time();
                $coupon_data[]=$tem;
            }
            $res=(new StoreCouponBagIssue())->saveAll($coupon_data);
            if(!$res){
                BaseModel::rollbackTrans();
                return Json::fail('保存失败');
            }


            BaseModel::commitTrans();
            return Json::successful('保存成功');

        }else{

            //新增新数据
            unset($data['id']);


            $res=\app\admin\model\ump\StoreCouponBag::create($data);
            if(!$res){
                return Json::fail('添加失败');
            }


            $fans_id=$res->getData('id');


            //处理商品和笔记的关联数据
            $coupon_data=[];
            foreach($couponIdsArr as $val){
                $tem['coupon_bag_id']=$fans_id;
                $tem['coupon_issue_id']=$val;
                $tem['add_time']=time();
                $coupon_data[]=$tem;
            }
            //存储优惠券
            $res=(new StoreCouponBagIssue())->saveAll($coupon_data);
            if(!$res){
                BaseModel::rollbackTrans();
                return Json::fail('添加失败');
            }


            BaseModel::commitTrans();
            return Json::successful('保存成功');
        }
    }

    /**
     * 显示编辑资源表单页.
     * @param $id
     * @return string|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit($id)
    {
        $coupon = \app\admin\model\ump\StoreCouponBag::get($id);
        if (!$coupon) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('title', '优惠券名称', $coupon->getData('title'));
        $f[] = Form::number('coupon_price', '优惠券面值', $coupon->getData('coupon_price'))->min(0);
        $f[] = Form::number('use_min_price', '优惠券最低消费', $coupon->getData('use_min_price'))->min(0);
        $f[] = Form::number('coupon_time', '优惠券有效期限', $coupon->getData('coupon_time'))->min(0);
        $f[] = Form::number('sort', '排序', $coupon->getData('sort'));
        $f[] = Form::radio('status', '状态', $coupon->getData('status'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);

        $form = Form::make_post_form('添加优惠券', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的资源
     *
     * @param $id
     */
    public function update($id)
    {
        $data = Util::postMore([
            'title',
            'coupon_price',
            'use_min_price',
            'coupon_time',
            'sort',
            ['status', 0]
        ]);
        if (!$data['title']) return Json::fail('请输入优惠券名称');
        if (!$data['coupon_price']) return Json::fail('请输入优惠券面值');
        if (!$data['coupon_time']) return Json::fail('请输入优惠券有效期限');
        \app\admin\model\ump\StoreCouponBag::edit($data, $id);
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
        if (!$id) return Json::fail('数据不存在!');
        $data['is_del'] = 1;
        if (!\app\admin\model\ump\StoreCouponBag::edit($data, $id))
            return Json::fail(\app\admin\model\ump\StoreCouponBag::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 修改优惠券状态
     * @param $id
     * @return \think\response\Json
     */
    public function status($id)
    {
        if (!$id) return Json::fail('数据不存在!');
        if (!\app\admin\model\ump\StoreCouponBag::editIsDel($id))
            return Json::fail(\app\admin\model\ump\StoreCouponBag::getErrorInfo('修改失败,请稍候再试!'));
        else
            return Json::successful('修改成功!');
    }

    /**
     * @return mixed
     */
    public function grant_subscribe()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(\app\admin\model\ump\StoreCouponBag::systemPageCoupon($where));
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function grant_all()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(\app\admin\model\ump\StoreCouponBag::systemPageCoupon($where));
        return $this->fetch();
    }

    /**
     * @param $id
     */
    public function grant($id)
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $nickname = UserModel::where('uid', 'IN', $id)->column('nickname', 'uid');
        $this->assign('where', $where);
        $this->assign('uid', $id);
        $this->assign('nickname', implode(',', $nickname));
        $this->assign(\app\admin\model\ump\StoreCouponBag::systemPageCoupon($where));
        return $this->fetch();
    }

    public function issue($id)
    {
        if (!CouponModel::be(['id' => $id, 'status' => 1, 'is_del' => 0]))
            return $this->failed('发布的优惠劵已失效或不存在!');
        $f = [];
        $f[] = Form::input('id', '优惠劵ID', $id)->disabled(1);
        $f[] = Form::dateTimeRange('range_date', '领取时间')->placeholder('不填为永久有效');
        $f[] = Form::radio('is_permanent', '是否不限量', 0)->options([['label' => '限量', 'value' => 0], ['label' => '不限量', 'value' => 1]]);
        $f[] = Form::number('count', '发布数量', 0)->min(0)->placeholder('不填或填0,为不限量');
        $f[] = Form::radio('is_full_give', '消费满赠', 0)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::number('full_reduction', '满赠金额')->min(0)->placeholder('赠送优惠券的最低消费金额');
        $f[] = Form::radio('is_give_subscribe', '首次关注赠送', 0)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);

        $form = Form::make_post_form('添加优惠券', $f, Url::buildUrl('update_issue', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

//        FormBuilder::text('id','优惠劵ID',$id)->disabled();
//        FormBuilder::dateTimeRange('range_date','领取时间')->placeholder('不填为永久有效');
//        FormBuilder::text('count','发布数量')->placeholder('不填或填0,为不限量');
//        FormBuilder::radio('status','是否开启',[
//            ['value'=>1,'label'=>'开启'],
//            ['value'=>0,'label'=>'关闭']
//        ],1);
//        $this->assign(['title'=>'发布优惠券','rules'=>FormBuilder::builder()->getContent(),'action'=>Url::buildUrl('update_issue',array('id'=>$id))]);
//        return $this->fetch('public/common_form');
    }

    public function update_issue($id)
    {
        list($_id, $rangeTime, $count, $status, $is_permanent, $full_reduction, $is_give_subscribe, $is_full_give) = Util::postMore([
            'id',
            ['range_date', ['', '']],
            ['count', 0],
            ['status', 0],
            ['is_permanent', 0],
            ['full_reduction', 0],
            ['is_give_subscribe', 0],
            ['is_full_give', 0]
        ], null, true);
        if ($_id != $id) return Json::fail('操作失败,信息不对称');
        if (!$count) $count = 0;
        if (!CouponModel::be(['id' => $id, 'status' => 1, 'is_del' => 0])) return Json::fail('发布的优惠劵已失效或不存在!');
        if (count($rangeTime) != 2) return Json::fail('请选择正确的时间区间');

        list($startTime, $endTime) = $rangeTime;
//        echo $startTime;echo $endTime;var_dump($rangeTime);die;
        if (!$startTime) $startTime = 0;
        if (!$endTime) $endTime = 0;
        if (!$startTime && $endTime) return Json::fail('请选择正确的开始时间');
        if ($startTime && !$endTime) return Json::fail('请选择正确的结束时间');
        if (StoreCouponIssue::setIssue($id, $count, strtotime($startTime), strtotime($endTime), $count, $status, $is_permanent,$full_reduction, $is_give_subscribe, $is_full_give))
            return Json::successful('发布优惠劵成功!');
        else
            return Json::fail('发布优惠劵失败!');
    }


    /**
     * 给分组用户发放优惠券
     */
    public function grant_group()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $group = UserModel::getUserGroup();
        $this->assign('where', $where);
        $this->assign('group', json_encode($group));
        $this->assign(CouponModel::systemPageCoupon($where));
        return $this->fetch();
    }

    /**
     * 给标签用户发放优惠券
     */
    public function grant_tag()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $tag = UserModel::getUserTag();;//获取所有标签
        $this->assign('where', $where);
        $this->assign('tag', json_encode($tag));
        $this->assign(CouponModel::systemPageCoupon($where));
        return $this->fetch();
    }



    /**
     * 获取商品的推广海报
     * @return string
     */
    public function extension(){
        $coupon_bag_id=input('param.id');
        $img_info=$this->get_coupon_bag_share_img($coupon_bag_id);
        $this->assign('id',$coupon_bag_id);
        $this->assign('img',$img_info['img']);
        $this->assign('name',$img_info['name']);
        $this->assign('url',$img_info['url']);
        return $this->fetch();
    }


    /**
     * 生成优惠券礼包的领取地址
     * @param $coupon_bag_id
     * @return mixed
     */
    function get_coupon_bag_share_img($coupon_bag_id)
    {
        //初始化设置
        $tenant_id=\app\admin\model\ump\StoreCouponBag::where('id','=',$coupon_bag_id)->value('tenant_id');
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
        $url = $site_url.'/user/CouponBagList?id='.$coupon_bag_id.'&tenant_id='.$tenant_id;

        $qCodePath = $this->get_url_qrcode($url,$coupon_bag_id,'coupon_bag_',8);
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
        imagecopymerge($bigImg, $icon, 100, 40, 0, 0, $icon_width, $icon_hight, 100);

        // 字体颜色
        $black = imagecolorallocate($bigImg, 0, 0, 0);
        $yellow = imagecolorallocate($bigImg, 255, 181, 15);
        $red = imagecolorallocate($bigImg, 255, 0, 0);
        $grey = imagecolorallocate($bigImg, 100, 100, 100);//越小灰度越深

        //商城名称
        $site_name=$publicController->getSysConfigValue('site_name',$tenant_id);
        $site_name=json_decode($site_name,true);
        $site_name=$site_name?$site_name:config('site.site_name');
        imagettftext ( $bigImg, 40, 0, 280, 120, $black, $font, $site_name );


        $product_info=\app\admin\model\ump\StoreCouponBag::where('id','=',$coupon_bag_id)->field('title')->find();
        $product_name=$product_info['store_name'];
        $product_image=$shop_logo;

        # 商品名称-居中
        $fontBox = imagettfbbox(25, 0, $font, $product_name);//文字水平居中实质
        //文字左右居中
        imagettftext ( $bigImg, 28, 0, ceil(($width - $fontBox[2]) / 2), 860, $black, $font, $product_name );


        # 商品图片
        $product_img = $this->scaleImg($product_image, $path, 830, 450);
        $icon = imagecreatefromstring(file_get_contents($product_img));
        list($icon_width, $icon_hight) = getimagesize($product_img);
        //图片居中
        imagecopymerge($bigImg, $icon, ceil(($width - $icon_width) / 2), 290, 0, 0, $icon_width, $icon_hight, 100);

        imagepng($bigImg, $_img);
        // @imagedestroy($image);
        @imagedestroy($bigImg);

        $return=[
            'img'=>$_img,
            'name'=>$product_name,
            'url'=>$url
        ];
        return $return;
    }

}
