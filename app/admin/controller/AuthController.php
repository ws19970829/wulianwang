<?php

namespace app\admin\controller;

use app\admin\model\system\SystemAdmin;
use app\admin\model\system\SystemMenus;
use app\admin\model\system\SystemRole;
use think\facade\Route as Url;

/**
 * 基类 所有控制器继承的类
 * Class AuthController
 * @package app\admin\controller
 */
class AuthController extends SystemBasic
{
    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $adminInfo;

    /**
     * 当前登陆管理员ID
     * @var
     */
    protected $adminId;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];

    protected $skipLogController = ['index', 'common'];

    protected function initialize()
    {
        parent::initialize();
        if (!SystemAdmin::hasActiveAdmin()) return $this->redirect(Url::buildUrl('login/index')->suffix(false)->build());
        try {
            $adminInfo = SystemAdmin::activeAdminInfoOrFail();
        } catch (\Exception $e) {
            return $this->failed(SystemAdmin::getErrorInfo($e->getMessage()), Url::buildUrl('login/index')->suffix(false)->build());
        }
        $this->adminInfo = $adminInfo;
        $this->adminId = $adminInfo['id'];
        $this->getActiveAdminInfo();
        $this->auth = SystemAdmin::activeAdminAuthOrFail();
        $this->adminInfo->level === 0 || $this->checkAuth();
        $this->assign('_admin', $this->adminInfo);
        $type = 'system';
        event('AdminVisit', [$this->adminInfo, $type]);
    }


//    protected function checkAuth($action = null, $controller = null, $module = null, array $route = [])
//    {
//        static $allAuth = null;
//        if ($allAuth === null) $allAuth = SystemRole::getAllAuth();
//        if ($module === null) $module = app('http')->getName();
//        if ($controller === null) $controller = $this->request->controller();
//        if ($action === null) $action = $this->request->action();
//        if (!count($route)) $route = $this->request->param();
//        array_shift($route);
//        if (in_array(strtolower($controller), $this->skipLogController, true)) return true;
//        $nowAuthName = SystemMenus::getAuthName($action, $controller, $module, $route);
//        $baseNowAuthName = SystemMenus::getAuthName($action, $controller, $module, []);
//        //积分设置的父类 不是系统设置  但是 $baseNowAuthName   确实验证得 系统设置权限
//        if ((in_array($nowAuthName, $allAuth) && !in_array($nowAuthName, $this->auth)) || (in_array($baseNowAuthName, $allAuth) && ($nowAuthName != 'admin/setting.systemconfig/index/type/3/tab_id/11' && !in_array($baseNowAuthName, $this->auth))))
//            exit($this->failed('没有权限访问!'));
//        return true;
//    }

    protected function checkAuth($action = null, $controller = null, $module = null, array $route = [])
    {
        static $allAuth = null;
        if ($allAuth === null) $allAuth = SystemRole::getAllAuth();
        if ($module === null) $module = app('http')->getName();
        if ($controller === null) $controller = $this->request->controller();
        if ($action === null) $action = $this->request->action();
        if (!count($route)) $route = $this->request->route();
        if (in_array(strtolower($controller), $this->skipLogController, true)) return true;
        $nowAuthName = SystemMenus::getAuthName($action, $controller, $module, $route);
        $baseNowAuthName = SystemMenus::getAuthName($action, $controller, $module, []);
        if ((in_array($nowAuthName, $allAuth) && !in_array($nowAuthName, $this->auth)) || (in_array($baseNowAuthName, $allAuth) && !in_array($baseNowAuthName, $this->auth)))
            exit($this->failed('没有权限访问!'));
        return true;
    }


    /**
     * 获得当前用户最新信息
     * @return SystemAdmin
     */
    protected function getActiveAdminInfo()
    {
        $adminId = $this->adminId;
        $adminInfo = SystemAdmin::getValidAdminInfoOrFail($adminId);
        if (!$adminInfo) $this->failed(SystemAdmin::getErrorInfo('请登陆!'));
        $this->adminInfo = $adminInfo;
        SystemAdmin::setLoginInfo($adminInfo);
        return $adminInfo;
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
    public function scaleImg($pic_name, $save_path, $maxx = 800, $maxy = 450)
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
     * 获取链接二维码
     * @param $url
     * @param $id
     * @param string $field
     * @param int $size
     * @return array
     */
    public function get_url_qrcode($url,$id,$field='product_',$size=9)
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
}