<?php
namespace Amcsi\UnziPhp;

class Utils
{
    public static function getRandomAlphanumString($length)
    {
        static $pool =
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        static $poolLength;
        if (!$poolLength) {
            $poolLength = strlen($pool);
        }
        $ret = '';
        for ($i = 0; $i < $length; $i++) {
            $randIndex = mt_rand(0, $poolLength - 1);
            $ret .= $pool[$randIndex];
        }
        return $ret;
    }
}

