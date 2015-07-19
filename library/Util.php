<?php
/**
 * @brief 辅助字符串
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @link http://tlanyan.me
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

namespace tlanyan;

class Util
{
    public static function genRandStr($length)
    {
        $chars = '0123456789abcdefhijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $clen = strlen($chars) - 1;
        $str = '';
        for ($i=0; $i<$length; ++$i) {
            $str .= $chars[mt_rand(0, $clen)];
        }

        return $str;
    }
}
