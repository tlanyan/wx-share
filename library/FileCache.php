<?php
/**
 * @brief 文件缓存
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @link http://tlanyan.me
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

namespace tlanyan;

require_once ("CacheInterface.php");

class FileCache implements CacheInterface
{
    /**
     * @var string key的前缀
     */
    public $keyPrefix = '_tlanyan';

    /**
     * @var string 缓存文件名后缀
     */
    public $cacheFileSuffix = '.bin';

    /**
     * @var string 缓存路径
     */
    public $cachePath = '/tmp';

    /**
     * @var integer 缓存文件权限
     */
    public $fileMode = 0644;

    /**
     * 根据key获取对应的缓存文件名
     *
     * @param string $key
     * @return string
     */
    protected function getCacheFile($key)
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
    }

    /**
     * 获取实际key
     *
     * @param string $key
     * @return string
     */
    public function buildKey($key)
    {
        return $this->keyPrefix . $key;
    }

    /**
     * 获取key的值
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->getCacheFile($key);

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return unserialize($cacheValue);
            }
        }
        return false;
    }

    /**
     * 设置key的值
     *
     * @param string $key
     * @param mixed $value key的值
     * @param integer $duration 缓存过期时间，以秒为单位，0表示为不过期
     * @return boolean
     */
    public function set($key, $value, $duration=0)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->getCacheFile($key);

        $value = serialize($value);

        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($cacheFile, $this->fileMode);
            }
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            return @touch($cacheFile, $duration + time());
        }
        return false;
    }
}
