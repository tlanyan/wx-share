<?php
/**
 * @brief 自动加载类
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @link http://tlanyan.me
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

namespace tlanyan;

class Autoloader
{
    protected static $_appPath = '';

    public static function load($name)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $name);

        if (strpos($name, 'tlanyan\\')===0) {
            $classPath = __DIR__ . substr($classPath, strlen('tlanyan')) . '.php';
        }
        elseif (self::$_appPath) {
                $classPath = self::$_appPath . DIRECTORY_SEPARATOR . $classPath . '.php';
        }

        if (is_file($classPath)) {
            require_once($classPath);
            if (class_exists($name, false)) {
                return true;
            }
        }
        return false;
    }

    public static function setAppPath($path)
    {
        self::$_appPath = $path;
    }
}

spl_autoload_register('\tlanyan\Autoloader::load');
