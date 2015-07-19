<?php
/**
 * @brief 缓存接口
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @link http://tlanyan.me
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

namespace tlanyan;

interface CacheInterface
{
    /**
     * 获取实际的key名
     *
     * @param string $key key
     * @return string 存储到缓存中的key名
     */
    public function buildKey($key);

    /**
     * 从缓存中获取key对应的值
     *
     * @param string $key key
     * @return mixed 成功返回值，失败返回false
     */
    public function get($key);

    /**
     * 设置缓存
     *
     * @param string $key key
     * @param mixed $value key的值
     * @param integer $duration 过期时间，以秒为单位，0表示永不过期
     * @return boolean 是否设置成功
     */
    public function set($key, $value, $duration=0);
}
