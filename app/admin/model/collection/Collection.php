<?php


namespace app\admin\model\collection;
use app\admin\model\store\StoreProduct;
use app\api\controller\PublicController;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use org\QRcode;


/**
 * 商家管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class Collection extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    protected $append=[
        'type_text'
    ];

    protected function getTypeTextAttr($val,$data){
        return $data['type']?'指定金额收款码:'.$data['money'].'元':'自助收款码';
    }



    /**
     * 模型名称
     * @var string
     */
    protected $name = 'collection';

    use ModelTrait;

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }

    public static function getCollectionList($where){
        $data = ($data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];

        foreach($data as $key=>$val){
            $data[$key]['qrcode']='/'.self::getQrcodeById($val);
            $data[$key]['link']=config('site.default_site_url').'/share/index/collection/id/'.$val['id'].'.html';
            //收款笔数
            $data[$key]['pay_num']=CollectionOrder::where('collection_id','=',$val['id'])->where('paid','=',1)->count();
            $data[$key]['pay_money']=CollectionOrder::where('collection_id','=',$val['id'])->where('paid','=',1)->sum('pay_price');
        }


        $count = self::systemPage($where,true)->count();
        return compact('count', 'data');
    }


    public static function getQrcodeById($collection_info){
        //前台页面的url
        $url=config('site.default_site_url').'/share/index/collection/id/'.$collection_info['id'].'.html';
        $qrcode=self::get_url_qrcode($url,$collection_info['id']);
        $path=$qrcode['path'];
        $_img=$qrcode['file_name'];
        $main = imagecreatefrompng ( $_img );
        $width = imagesx ( $main );//背景图宽度
        $height = imagesy ( $main );//背景图高

        //将二维码合成商城头像
        $bigImg = imagecreatefromstring(file_get_contents($qrcode['file_name']));

        # 商城标志
        //如果未上传logo，就使用默认logo；
        $tenant_id=$collection_info['tenant_id'];
        $publicController=new PublicController();
        $shop_logo=$publicController->getSysConfigValue('routine_index_logo',$tenant_id);
        $shop_logo=$shop_logo?$shop_logo:config('site.default_logo');
        $shop_logo=str_replace('\\/', '/', $shop_logo);
        $avatar = self::scaleImg($shop_logo, $path, 100, 100);
        $icon = imagecreatefromstring(file_get_contents($avatar));
        list($icon_width, $icon_hight) = getimagesize($avatar);

        imagecopymerge($bigImg, $icon,  ceil(($width - $icon_width) / 2), 120, 0, 0, $icon_width, $icon_hight, 200);
        imagepng($bigImg, $_img);
        // @imagedestroy($image);
        @imagedestroy($bigImg);

        return $_img;
    }


    function pngMerge($o_pic,$out_pic){
        $begin_r = 255;
        $begin_g = 250;
        $begin_b = 250;
        list($src_w, $src_h) = getimagesize($o_pic);// 获取原图像信息 宽高
        $src_im = imagecreatefrompng($o_pic); //读取png图片
        print_r($src_im);
        imagesavealpha($src_im,true);//这里很重要 意思是不要丢了$src_im图像的透明色
        $src_white = imagecolorallocatealpha($src_im, 255, 255, 255,127); // 创建一副白色透明的画布
        for ($x = 0; $x < $src_w; $x++) {
            for ($y = 0; $y < $src_h; $y++) {
                $rgb = imagecolorat($src_im, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if($r==255 && $g==255 && $b == 255){
                    imagefill($src_im,$x, $y, $src_white); //填充某个点的颜色
                    imagecolortransparent($src_im, $src_white); //将原图颜色替换为透明色
                }
                if (!($r <= $begin_r && $g <= $begin_g && $b <= $begin_b)) {
                    imagefill($src_im, $x, $y, $src_white);//替换成白色
                    imagecolortransparent($src_im, $src_white); //将原图颜色替换为透明色
                }
            }
        }


        $target_im = imagecreatetruecolor($src_w, $src_h);//新图

        imagealphablending($target_im,false);//这里很重要,意思是不合并颜色,直接用$target_im图像颜色替换,包括透明色;
        imagesavealpha($target_im,true);//这里很重要,意思是不要丢了$target_im图像的透明色;
        $tag_white = imagecolorallocatealpha($target_im, 255, 255, 255,127);//把生成新图的白色改为透明色 存为tag_white
        imagefill($target_im, 0, 0, $tag_white);//在目标新图填充空白色
        imagecolortransparent($target_im, $tag_white);//替换成透明色
        imagecopymerge($target_im, $src_im, 0, 0, 0, 0, $src_w, $src_h, 100);//合并原图和新生成的透明图
        imagepng($target_im,$out_pic);
        return $out_pic;

    }





    /**
     * 获取链接二维码
     * @param $url
     * @param $id
     * @param string $field
     * @param int $size
     * @return array
     */
    public static function get_url_qrcode($url,$id,$field='collction_',$size=9)
    {

        $path = 'uploads' . DIRECTORY_SEPARATOR . 'qrcode' . DIRECTORY_SEPARATOR;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $file = $field . $id . '.png';
        $fileName = $path . $file;

        \org\QRcode::png($url, "$fileName", QR_ECLEVEL_Q, $size, 0);
        return [
            'path' => $path,
            'file_name' => $fileName,
        ];

    }

    /**
     *等比例缩放函数（以保存新图片的方式实现）
     * @param string $pic_name 被缩放的处理图片源
     * @param string $save_path 保存路径
     * @param int $maxx 缩放后图片的最大宽度
     * @param int $maxy 缩放后图片的最大高度
     * @param string $pre 缩放后图片的前缀名
     * @return $string 返回后的图片名称（） 如a.jpg->s.jpg
     *
     **/
    public static function scaleImg($pic_name, $save_path, $maxx = 800, $maxy = 450)
    {
        //处理图片的
        if(!strpos($pic_name, '://')){
            $pic_name=config('site.default_site_url').$pic_name;
        }

        $info = getimageSize($pic_name); //获取图片的基本信息
        $w = $info[0];                   //获取宽度
        $h = $info[1];                   //获取高度

        if ($w <= $maxx && $h <= $maxy) {
            return $pic_name;
        }
        //获取图片的类型并为此创建对应图片资源
        switch ($info[2]) {
            case 1: //gif
                $im = imagecreatefromgif($pic_name);
                break;
            case 2: //jpg
                $im = imagecreatefromjpeg($pic_name);
                break;
            case 3: //png
                $im = imagecreatefrompng($pic_name);
                break;
            default:
                die("图像类型错误");
        }
        //计算缩放比例
        if (($maxx / $w) > ($maxy / $h)) {
            $b = $maxy / $h;
        } else {
            $b = $maxx / $w;
        }
        //计算出缩放后的尺寸
        $nw = floor($w * $b);
        $nh = floor($h * $b);
        //创建一个新的图像源（目标图像）
        $nim = imagecreatetruecolor($nw, $nh);

        //透明背景变黑处理
        //2.上色
        $color = imagecolorallocate($nim, 255, 255, 255);
        //3.设置透明
        imagecolortransparent($nim, $color);
        imagefill($nim, 0, 0, $color);

        //执行等比缩放
        imagecopyresampled($nim, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        //输出图像（根据源图像的类型，输出为对应的类型）
        //解析源图像的名字和路径信息
        $pic_info = pathinfo($pic_name);
        $save_path = $save_path . $pic_info["basename"];
        switch ($info[2]) {
            case 1:
                imagegif($nim, $save_path);
                break;
            case 2:
                imagejpeg($nim, $save_path);
                break;
            case 3:
                imagepng($nim, $save_path);
                break;

        }
        //释放图片资源
        imagedestroy($im);
        imagedestroy($nim);
        //返回结果
        return $save_path;
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));
        $model=$model->where('is_del','=',0);


        if($where['title']){
            $title=$where['title'];
            $model=$model->where('title','like',"%$title%");
        }

        if(isset($where['status'])){
            $model=$model->where('status','=',$where['status']);

        }

        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {

        }, $where);
    }


    /**
     *
     * @param $id
     * @param int $is_admin_view 是否是总后台预览访问 0否
     * @return array|null|\think\Model
     */
    public function getOne($id,$is_admin_view=0){
        $info=$this
            ->where('id','=',$id)
            ->find();
        if($info){
            $info=$info->toArray();
            $info['product_list']=[];
            $product_ids_list=(new CollectionProduct())->where('collection_id','=',$id)->select();
            if($product_ids_list){
                $product_ids_arr=$product_ids_list->column('product_id');
                $product_ids_str=implode($product_ids_arr,',');

                $product_list=StoreProduct::where('id','in',$product_ids_str)
                    ->where('tenant_id','=',$info['tenant_id'])
                    ->where('is_del','=',0)//未删除
                    ->where('is_show','=',1)//上架
                    ->select();
                if(count($product_list)){
                    //网站地址
                    $site_url=config('site.default_site_url');
                    foreach($product_list as $key=>$val){
                        $product_list[$key]['url']=$site_url.'/detail/'.$val['id'].'?collection_id='.$id;
                    }
                    $info['product_list']=$product_list->toArray();
                }
            }

            //如果是总后台的预览，则不做阅读量处理
            if(!$is_admin_view){
                //本篇合作商家的阅读量增1
                $this->where('id','=',$id)->inc('view_num')->update();
            }

        }
        return $info;
    }


    /**
     * 产品分类隐藏显示
     * @param $id
     * @param $show
     * @return bool
     */
    public static function setShow($id, $show)
    {
        $res = self::where('id', $id)->update(['status' => $show]);
        return $res;
    }



}