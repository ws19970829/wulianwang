<?php
/**
 * Created by PhpStorm.
 * User: wuhaotian
 * Date: 2020-02-24
 * Time: 17:57
 */

namespace app\superadmin\controller\widget;

use app\api\controller\PublicController;
use app\superadmin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\upload\Upload;
use think\facade\Config;

class Video extends AuthController
{


    /**
     * 上传类型
     * @var int
     */
    protected $uploadInfo;

    /**
     * 获取配置信息
     * Video constructor.
     */
    public function initialize()
    {
        parent::initialize();
        $this->uploadInfo['accessKey'] = (new PublicController())->getSysConfigValue('accessKey',1);
        $this->uploadInfo['secretKey'] = (new PublicController())->getSysConfigValue('secretKey',1);
        $this->uploadInfo['uploadUrl'] = (new PublicController())->getSysConfigValue('uploadUrl',1);
        $this->uploadInfo['storageName'] = (new PublicController())->getSysConfigValue('storage_name',1);
        $this->uploadInfo['storageRegion'] = (new PublicController())->getSysConfigValue('storage_region',1);
        $this->uploadInfo['uploadType'] = (new PublicController())->getSysConfigValue('uploadType',1);

    }

    /**
     * 获取密钥签名
     */
    public function get_signature()
    {
        if ($this->uploadInfo['uploadType'] == 1) {
            if (!$this->uploadInfo['accessKey'] || !$this->uploadInfo['secretKey']) {
                return JsonService::fail('视频上传需要上传到云端,默认使用阿里云OSS上传请配置!');
            } else {
                $this->uploadInfo['uploadType'] = 3;
            }
        }
        if ($this->uploadInfo['uploadType'] == 2) {
            $upload = new Upload('Qiniu', $this->uploadInfo);
            $res = $upload->getSystem();
            $this->uploadInfo['uploadToken'] = $res['token'];
            $this->uploadInfo['domain'] = $res['domain'];
            $this->uploadInfo['uploadType'] = 'QINIU';
        } elseif ($this->uploadInfo['uploadType'] == 3) {
            $this->uploadInfo['uploadType'] = 'OSS';
            if (($leng = strpos($this->uploadInfo['storageRegion'], 'aliyuncs.com')) !== false) {
                $this->uploadInfo['storageRegion'] = substr($this->uploadInfo['storageRegion'], 0, $leng - 1);
            }
        } elseif ($this->uploadInfo['uploadType'] == 4) {
            $this->uploadInfo['uploadType'] = 'COS';
        }
        return JsonService::successful($this->uploadInfo);
    }

    public function index($fodder = '')
    {
        $this->assign(compact('fodder'));
        return $this->fetch();
    }
}