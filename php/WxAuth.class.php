<?php
/**
 * 微信auth2.0认证封装
 * User: huanw2010@gmail.com
 * Date: 14-7-21 下午2:02
 */

class WxAuth{
    public $redirectUri;
    public $appId;
    public $appSecret;
    public $scope;
    public $state;
    public $accessToken;

    /**
     * @param string $redirectUri
     * @param $appId
     * @param $appSecret
     * @param string $scope snsapi_base | snsapi_userinfo
     * @param null $state
     */
    public function __construct($redirectUri,$appId,$appSecret,$scope='snsapi_base',$state=null){
        $this->redirectUri = strpos($redirectUri,'http') === 0 ? $redirectUri : 'http://'.$redirectUri;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->scope = $scope;
        $this->state = $state ? $state : $this->getState();
    }

    /**
     * get state
     * @return string
     */
    public function getState(){
        return $this->state ? $this->state : 'common_state';
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $decodeJson
     * @return mixed
     */
    protected function call($url, $params = array(),$decodeJson = true){
        $content = self::fetch($url,$params);
        if($decodeJson){
             return @json_decode($content,true);
        }
        return $content;
    }

    /**
     * get code
     */
    public function getCode(){
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $url .= http_build_query(array(
                'appid' => $this->appId,
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'scope' => $this->scope,
                'state' => $this->state,
            )).'#wechat_redirect';
        //echo $url;exit;
        self::redirect($url);
    }


    /**
     * get access token
     * @param $code
     * @return mixed
     */
    public function getAccessToken($code){
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';

        $data = $this->call($url,array(
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ));

        if($data && isset($data['access_token'])){
             $this->accessToken = $data['access_token'];
        }

        return $data;

    }

    /**
     * get user info
     * @param $openid
     * @param $accessToken
     * @return mixed
     */
    public function getUserInfo($openid,$accessToken = null){
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        return $this->call($url,array(
            'access_token' => $accessToken ? $accessToken : $this->accessToken,
            'openid' => $openid,
            'lang' => 'zh_CN',
        ));
    }

    /**
     * 刷新token
     * @param $freshToken
     * @return mixed
     */
    public function refreshAccessToken($freshToken){
        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

        $data = $this->call($url,array(
            'appid' => $this->appId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $freshToken,
        ));

        return $data;
    }

    /**
     * 检查access token 是否有效
     * @param $accessToken
     * @param $openid
     * @return bool
     */
    public function checkAccessToken($accessToken,$openid){
        $url = 'https://api.weixin.qq.com/sns/auth';
        $data = $this->call($url,array(
            'access_token' => $accessToken,
            'openid' => $openid,
        ));

        if(isset($data['errcode']) && $data['errcode'] == 0){
            return true;
        }

        return false;
    }

    /**
     * redirect the url
     * @param $url
     */
    public static function redirect($url){
        //header('Location:'.$url);
        //echo '<meta http-equiv="refresh" content="0;url={$url}" />';
        echo '<script> window.location.href = "'.$url.'";</script>';

        exit;
    }

    /**
     * fetch the http resource by curl
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $header
     * @return mixed
     */
    public static function fetch($url,$params=array(),$method="get",$header=array()){
        $ch = curl_init();
        if(is_array($params)){
            $query = http_build_query($params);
        }else{
            $query = $params;
        }
        if($method == 'get'){
            if(strpos($url,'?') !== false){
                $url .= "&".$query;
            }else{
                $url .= "?".$query;
            }
        }else{
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$query);
        }

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSLVERSION,1); // 在linux上可以不需要这行
        //curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).'/cacert.pem');

        $data = curl_exec($ch);
        /*$info = curl_getinfo($ch);
        print_r($info);
        $error = curl_error($ch);
        print_r($error);*/
        curl_close($ch);

        return $data;

    }

}