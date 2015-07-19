<?php
/**
 * @brief 发送签名包
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @copyright http://www.liusha.info
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

require_once(__DIR__ . '/library/Autoloader.php');
require_once(__DIR__ . '/config.php');

tlanyan\Autoloader::setAppPath(__DIR__);

$weixin = new tlanyan\Weixin(APPID, SECRET);

$url = $_GET['url'];

header('Content-type: application/json');

echo json_encode($weixin->getSignPackage($url), true);
