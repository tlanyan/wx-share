<?php
/**
 * @brief 一些常用的微信接口
 *
 * @author tlanyan<tlanyan@hotmail.com>
 * @link http://tlanyan.me
 */
/* vim: set ts=4; set sw=4; set ss=4; set expandtab; */

namespace tlanyan;

class Weixin
{
    /**
     * @var string 微信的appid
     */
    public $appid = '';

    /**
     * @var string 微信的app secret
     */
    public $appSecret = '';

    /**
     * @var 网页授权回调url
     */
    public $codeCallbackUrl = '';

    /**
     * @var string 普通access token
     */
    private $_accessToken = null;

    /**
     * @var string jsapi ticket
     */
    private $_jsapi_ticket = null;

    /**
     * 缓存对象
     */
    private static $_cache = null;

    /**
     * @var string 微信普通token的Url，注意最终网址需要使用appid和app secret填充
     */
    const TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';

    /**
     * @const string 使用客服接口向用户发送消息的url，注意网址需要用access token 填充
     */
    const SEND_MESSAGE_URL = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';

    /**
     * @const string  获取关注用户列表的url，注意网址需要用access token和nextopenid填充
     */
    const USER_LIST_URL = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s';

    /**
     * @const string 网页授权获取code的url
     */
    const CODE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_%s&state=%s#wechat_redirect';

    /**
     * @const string 网页获取用户openid的url
     */
    const OPENID_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';

    /**
     * @const string jsapi_ticket的获取网址
     */
    const JSAPI_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';

    /**
     * 构造函数
     *
     * @param string $appid 微信的appid
     * @param string $secret 微信的secret
     */
    public function __construct($appid, $secret)
    {
        $this->appid = $appid;
        $this->appSecret = $secret;
    }

    /**
     * 获取缓存实例，线上环境建议采用redis或者memcache等高性能缓存
     *
     * @return FileCache
     */
    public static function getCache()
    {
        if (!self::$_cache) {
            self::$_cache = new FileCache();
        }

        return self::$_cache;
    }

    /**
     * 获取普通的access token
     *
     * @return mixed 成功返回access token，失败返回null
     */
    public function getAccessToken()
    {
        if ($this->_accessToken) {
            return $this->_accessToken;
        }

        $key = 'wx_token';
        $token = self::getCache()->get($key);
        if (!$token) {
            $url = sprintf(self::TOKEN_URL, $this->appid, $this->appSecret);

            $ch = curl_init();
             $options = [
                CURLOPT_URL => $url,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CONNECTTIMEOUT => 10,
            ];
            curl_setopt_array($ch, $options);
            $res = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($res,true);
            if (!$data || isset($data['errcode']) || !isset($data['access_token'])) {
                return null;
            }

            $token = $data['access_token'];
            self::getCache()->set($key, $token, $data['expires_in']);
        }

        $this->_accessToken = $token;
        return $token;
    }

    /**
     * 拼接得到获取code的url
     *
     * @param string $scope 授权作用域，默认为base，只能获取openid， 当作用域为userinfo时，能够获取用户信息（需用户同意）
     * @param string $state 跳转参数，微信将会带回回调地址
     *
     * @return string 拼接后的网址
     */
    public function getRedirectUrl($scope='base', $state='123')
    {
        $redirectUrl = urlencode($this->codeCallbackUrl);

        $url = sprintf(self::CODE_URL, $this->appid, $redirectUrl, $scope, $state);
        return $url;
    }

    /**
     * 获取网页访问用户的openid
     *
     * @param [in] $code string 从微信获取的授权码
     *
     * @return string|false 成功返回用户的openid,失败返回false
     */
    public function getOpenId($code)
    {
        $url = sprintf(self::OPENID_URL, $this->appid, $this->appSecret, $code);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (!$data || isset($data['errcode'])) {
            return false;
        }

        return $data['openid'];
    }

    /**
     * 获取jsapi的票据（用于分享、支付等）
     *
     * @return string|false 成功返回ticket，失败返回false
     */
    public function getJsapiTicket()
    {
        if ($this->_jsapi_ticket) {
            return $this->_jsapi_ticket;
        }

        $key = 'jsapi_ticket';
        $ticket = self::getCache()->get($key);
        if ($ticket)
            return $ticket;

        $token = $this->getAccessToken();
        $url = sprintf(self::JSAPI_TICKET_URL, $token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (!$data || $data['errcode']) {
            return false;
        }

        $this->_jsapi_ticket = $data['ticket'];
        self::getCache()->set($key, $this->_jsapi_ticket, $data['expires_in']);

        return $this->_jsapi_ticket;
    }

    /**
     * 生成jsapi的权限签名
     *
     * @param $url 调用jsapi的当前url
     *
     * @return string
     */
    public function getSignPackage($url)
    {
        $ticket = $this->getJsapiTicket();

        $timestamp = time();
        $nonceStr = Util::genRandStr(16);
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "access_token" => $this->getAccessToken(),
        );

        return $signPackage; 
    }
}
