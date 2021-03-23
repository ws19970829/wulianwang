<?php

/**
 *
 * @author: jincheng
 * @day: 2020/09/18
 * 雪花算法生成唯一数字
 */

namespace crmeb\services;


class  SnowFlake
{
    const EPOCH = 1479533469598;
    const max12bit = 4095;
    const max41bit = 1099511627775;
    static $machineId = null;
    public static function machineId($mId)
    {
        self::$machineId = $mId;
    }

    /**雪花算法生成唯一id */
    public static function generateParticle()
    {

        $time = floor(microtime(true) * 1000);

        $time -= self::EPOCH;

        $base = decbin(self::max41bit + $time);

        $machineid = str_pad(decbin(self::$machineId), 10, "0", STR_PAD_LEFT);

        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);

        $base = $base . $machineid . $random;

        return bindec($base);
    }

    /**反向计算时间戳 */
    public static function timeFromParticle($particle)
    {

        return bindec(substr(decbin($particle), 0, 41)) - self::max41bit + self::EPOCH;
    }
}
