<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------


if (!function_exists('get_month')) {
    /**
     * 格式化月份
     * @param string $time
     * @param int $ceil
     * @return array
     */
    function get_month($time = '', $ceil = 0)
    {
        if (empty($time)) {
            $firstday = date("Y-m-01", time());
            $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        } else if ($time == 'n') {
            if ($ceil != 0)
                $season = ceil(date('n') / 3) - $ceil;
            else
                $season = ceil(date('n') / 3);
            $firstday = date('Y-m-01', mktime(0, 0, 0, ($season - 1) * 3 + 1, 1, date('Y')));
            $lastday = date('Y-m-t', mktime(0, 0, 0, $season * 3, 1, date('Y')));
        } else if ($time == 'y') {
            $firstday = date('Y-01-01');
            $lastday = date('Y-12-31');
        } else if ($time == 'h') {
            $firstday = date('Y-m-d', strtotime('this week +' . $ceil . ' day')) . ' 00:00:00';
            $lastday = date('Y-m-d', strtotime('this week +' . ($ceil + 1) . ' day')) . ' 23:59:59';
        }
        return array($firstday, $lastday);
    }
}
if (!function_exists('clearfile')) {
    /**删除目录下所有文件
     * @param $path 目录或者文件路径
     * @param string $ext
     * @return bool
     */
    function clearfile($path, $ext = '*.log')
    {
        $files = (array) glob($path . DS . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . '/' . $ext);
                if (is_array($matches)) {
                    array_map('unlink', $matches);
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }
        return true;
    }
}
if (!function_exists('get_this_class_methods')) {
    /**获取当前类方法
     * @param $class
     * @return array
     */
    function get_this_class_methods($class, $unarray = [])
    {
        $arrayall = get_class_methods($class);
        if ($parent_class = get_parent_class($class)) {
            $arrayparent = get_class_methods($parent_class);
            $arraynow = array_diff($arrayall, $arrayparent); //去除父级的
        } else {
            $arraynow = $arrayall;
        }
        return array_diff($arraynow, $unarray); //去除无用的
    }
}

if (!function_exists('verify_domain')) {

    /**
     * 验证域名是否合法
     * @param string $domain
     * @return bool
     */
    function verify_domain(string $domain): bool
    {
        $res = "/^(?=^.{3,255}$)(http(s)?:\/\/)(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*$/";
        if (preg_match($res, $domain))
            return true;
        else
            return false;
    }
}

if (!function_exists('get_system_config')) {
    function get_service_config($config)
    {
        return trim(db('system_config')->where('menu_name', $config)->value('value'), '""');
    }
}
