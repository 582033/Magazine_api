<?php
/**
 * Tencent_OAuth授权类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class Tencent_OAuth
{
    public static $client_id = '';
    public static $client_secret = '';
    
    private static $accessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
    private static $authorizeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';

    /**
     * 初始化
     * @param $client_id 即 appid
     * @param $client_secret 即 appkey
     * @return
     */
    public static function init($client_id, $client_secret)
    {
        if (!$client_id || !$client_secret) exit('client_id or client_secret is null');
        self::$client_id = $client_id;
        self::$client_secret = $client_secret;
    }

    /**
     * 获取授权URL
     * @param $redirect_uri 授权成功后的回调地址，即第三方应用的url
     * @param $response_type 授权类型，为code
     * @param $wap 用于指定手机授权页的版本，默认PC，值为1时跳到wap1.0的授权页，为2时同理
     * @return string
     */
    public static function getAuthorizeURL($redirect_uri, $response_type = 'code', $wap = false)
    {
    	$type = null;
        $params = array(
            'client_id' => self::$client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => $response_type,
            'type' => $type
        );
        return self::$authorizeURL.'?'.http_build_query($params);
    }

    /**
     * 获取请求token的url
     * @param $code 调用authorize时返回的code
     * @param $redirect_uri 回调地址，必须和请求code时的redirect_uri一致
     * @return string
     */
    public static function getAccessToken($code, $redirect_uri)
    {
        $params = array(
            'client_id' => self::$client_id,
            'client_secret' => self::$client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri
        );
        return self::$accessTokenURL.'?'.http_build_query($params);
    }
    
    
    /**
     * 验证授权是否有效
     */
    public static function checkOAuthValid()
    {
        $r = json_decode(Tencent::api('user/info'), true);
        if ($r['data']['name']) {
            return true;
        }
        return false;
    }
}

/**
 * 腾讯微博API调用类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class Tencent
{
    //接口url
    public static $apiUrlHttp = 'http://open.t.qq.com/api/';
    public static $apiUrlHttps = 'https://open.t.qq.com/api/';
    
    //调试模式
    public static $debug = false;
    public static $accessToken = null;
    public static $openId = null;
    public static $openKey = null;
    
    static public function init($accessToken,$openId=null,$openKey=null) {
    	self::$accessToken = $accessToken;
    	self::$openId = $openId;
    	self::$openKey = $openKey;
    }
    
    /**
     * 发起一个腾讯API请求
     * @param $command 接口名称 如：t/add
     * @param $params 接口参数  array('content'=>'test');
     * @param $method 请求方式 POST|GET
     * @param $multi 图片信息
     * @return string
     */
    public static function api($command, $params = array(), $method = 'GET', $multi = false) {
        if (self::$accessToken) {//OAuth 2.0 方式
            //鉴权参数
            $params['access_token'] = self::$accessToken;
            $params['oauth_consumer_key'] = Tencent_OAuth::$client_id;
            $params['openid'] = self::$openId;
            $params['oauth_version'] = '2.a';
            $params['clientip'] = Tencent_Common::getClientIp();
            $params['scope'] = 'all';
            $params['appfrom'] = Tencent_Http::USERAGENT;
            $params['seqid'] = time();
            $params['serverip'] = $_SERVER['SERVER_ADDR'];
            
            $url = self::$apiUrlHttps.trim($command, '/');
        } 
        elseif (self::$openId && self::$openKey) {//openid & openkey方式
            $params['appid'] = Tencent_OAuth::$client_id;
            $params['openid'] = self::$openId;
            $params['openkey'] = self::$openKey;
            $params['clientip'] = Tencent_Common::getClientIp();
            $params['reqtime'] = time();
            $params['wbversion'] = '1';
            $params['pf'] = Tencent_Http::USERAGENT;
            
            $url = self::$apiUrlHttp.trim($command, '/');
            //生成签名
            $urls = @parse_url($url);
            $sig = Tencent_SnsSign::makeSig($method, $urls['path'], $params, Tencent_OAuth::$client_secret.'&');
            $params['sig'] = $sig;
        }
        //请求接口
        $r = Tencent_Http::request($url, $params, $method, $multi);
        $r = preg_replace('/[^\x20-\xff]*/', "", $r); //清除不可见字符
        $r = iconv("utf-8", "utf-8//ignore", $r); //UTF-8转码
        //调试信息
        if (self::$debug) {
            echo '<pre>';
            echo '接口：'.$url;
            echo '<br>请求参数：<br>';
            print_r($params);
            echo '返回结果：'.$r;
            echo '</pre>';
        }
        return json_decode($r,true);
    }
}

/**
 * Tencent_Http请求类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class Tencent_Http
{
	const USERAGENT = '1001 Magazine v1';
	const CONNECTTIMEOUT = 10;
	const TIMEOUT = 10;
    /**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL 
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @param $multi 图片信息
     * @param $extheaders 扩展的包头信息
     * @return string
     */
    public static function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array())
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
        curl_setopt($ci, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }
}

/**
 * 公共函数类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20 *
 */
class Tencent_Common
{
    //获取客户端IP
    public static function getClientIp()
    {
        if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
            $ip = getenv ( "HTTP_CLIENT_IP" );
        else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
            $ip = getenv ( "HTTP_X_FORWARDED_FOR" );
        else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
            $ip = getenv ( "REMOTE_ADDR" );
        else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
            $ip = $_SERVER ['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }
}

/**
 * Openid & Openkey签名类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class Tencent_SnsSign
{
    /**
     * 生成签名
     * @param string    $method 请求方法 "get" or "post"
     * @param string    $url_path 
     * @param array     $params 表单参数
     * @param string    $secret 密钥
     */
    public static function makeSig($method, $url_path, $params, $secret) 
    {
        $mk = self::makeSource ( $method, $url_path, $params );
        $my_sign = hash_hmac ( "sha1", $mk, strtr ( $secret, '-_', '+/' ), true );
        $my_sign = base64_encode ( $my_sign );
        return $my_sign;
    }
    
    private static function makeSource($method, $url_path, $params) 
    {
        ksort ( $params );
        $strs = strtoupper($method) . '&' . rawurlencode ( $url_path ) . '&';
        $str = ""; 
        foreach ( $params as $key => $val ) { 
            $str .= "$key=$val&";
        }   
        $strc = substr ( $str, 0, strlen ( $str ) - 1 );
        return $strs . rawurlencode ( $strc );
    }
}