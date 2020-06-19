<?php  
  /**
     * 微信用户授权
     * @param $authurl
     * @param $appid
     * @param $secret
     * @param $scope
     * @return mixed
     */
    function userAuth($url, $appid, $secret, $scope = 'snsapi_base'){
        $wxAuth = new WxAuth($url, $appid, $secret, $scope);
        if(isset($_GET['state']) && $_GET['state'] == $wxAuth->getState()){
            if(isset($_GET['code']) && $_GET['code'] != 'authdeny'){
                $accessToken = $wxAuth->getAccessToken($_GET['code']);
                if(isset($accessToken['errcode'])){
                    header('Location:'.$url);
                }else{
                    if($accessToken['scope'] == 'snsapi_base'){
            			$openid = $accessToken['openid'];
                        return $openid;
                    }
                    if($accessToken['scope'] == 'snsapi_userinfo'){
                        $userInfo = $wxAuth->getUserInfo($accessToken['openid']);
                        return $userInfo;
                    }
                }
            }else{
                echo '取消授权';
            }
        }else{
            $wxAuth->getCode();
        }
    }
	
	//获取用户信息
	function userInfo($openid){
		$accessToken = accessToken(APPID,SCRECT);
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$accessToken.'&openid='.$openid.'&lang=zh_CN';
		$res = httpGet($url);
		return json_decode($res);
	}

	
	
	//原getAccessToken 方法
	function accessToken($appid, $appsecret) {
		// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$access_path='../xuetang/Data/access_token.json';
		$contents = @file_get_contents($access_path);
		//print_R($contents);
		$data = json_decode($contents);
		//var_dump($data);exit;
		if ($data->expire_time < time()) {
		  $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		  $res = json_decode(httpGet($url));
		  $access_token = $res->access_token;
		  if ($access_token) {
			$data->expire_time = time() + 7000;
			$data->access_token = $access_token;
			$fp = fopen($access_path, "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		  }
		} else {
		  $access_token = $data->access_token;
		}
		return $access_token;
	}
	
	 /**
     * @return array
     * 企业微信授权
     *返回信息(openid,userid)
     */
    function qiYeScope() {
        $result = getQyUid();
        if (!empty($result['userId'])) {//企业成员
            if (!isset($_SESSION['qy_openid'])) {
                $userId = $result['userId'];
                $access_token = getQYAccessToken('../xt_data/access_token.json');
                $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=" . $access_token;
                $data = ['userid' => $userId];
                $res = json_decode(http_post($url, json_encode($data)), true);
                $_SESSION['qy_openid'] = $res['openid'];
            }
            $result['openid'] = $_SESSION['qy_openid'];

        }
        return $result;
    }
	
     /**
     * 企业微信
     * 获取token
     * @param string $path 不同路径的文件调用此方法默认路径../dataFile/access_token.json
     * @param string $corpid
     * @param string $secret
     * @return mixed
     */
    function getQYAccessTokenOld($path='../dataFile/access_token.json', $corpid='ww90611686506d528c', $secret='Y-HSw55-aWzEwvVcCD76t1F8_vmZQcMU-FmOMDA-UoY') {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        //$access_path='access_token.json';
        $access_path = $path;
        $data = @json_decode(file_get_contents($access_path),true);
        if ($data['expire_time'] < time()) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$corpid."&corpsecret=".$secret;

            $res = json_decode(curlGet($url),true);
            if ($res['errcode'] == 0) {
                $access_token = $res['access_token'];
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                $fp = fopen($access_path, "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }

	
	    /**
     * 获取token
     * @param string $path 不同路径的文件调用此方法默认路径../dataFile/access_token.json
     * @param string $corpid
     * @param string $secret
     * @return mixed
     */
    function getQYAccessToken($path='../dataFile/access_token.json',$corpid='ww094e9c4b139f3b6e',$secret='A0HYvgc36igTJIqZIFiHirOyUcBgP9M6N_W50cETPL8') {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        //$access_path='access_token.json';
        $access_path = $path;
        $data = @json_decode(file_get_contents($access_path),true);
        if ($data['expire_time'] < time()) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$corpid."&corpsecret=".$secret;

            $res = json_decode(httpGet($url),true);
            if ($res['errcode'] == 0) {
                $access_token = $res['access_token'];
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                file_put_contents($access_path,json_encode($data));
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }


    /**
     * 企业号发送消息
     * @param $touser 用户userid
     * @param string $type 消息类型
     * @param string $agentid  企业应用的id，整型。企业内部开发，可在应用的设置页面查看；
     * @param $content  消息内容
     * @return bool|mixed
     */
    function qiyeSendMsg($touser,$content,$type='text',$agentid='1000016'){
        $access_token = getQYAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$access_token;
        $param = array(
            'touser' => $touser,
            'msgtype' => $type,
            'agentid' => $agentid,
            'text'    => array('content'=>$content)
        );
        $param = json_encode($param);
        $result = curlPost($url, $param);
        return $result;
    }

    /*
     * 获取企业用户uid
     */
    function getQyUid($appid = 'ww094e9c4b139f3b6e'){
        $info = [];//用户信息
        $redirectUrl = WEBSITEURL.basename($_SERVER['REQUEST_URI']);//回调url
        if(!isset($_SESSION['qy_uid'])){

            if(!isset($_GET['code'])){
                $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirectUrl."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
                header('Location:'.$auth_url);exit;
            }else{
                $code = $_GET['code'];
                $access_token = getQYAccessToken('./xt_data/access_token.json');
                $user_url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$access_token."&code=".$code;
                $result = json_decode(httpGet($user_url),true);
                if(isset($result['errcode']) && $result['errcode'] == 0){

                    if(isset($result['UserId'])){//说明是企业用户
                        $info['userId'] = $result['UserId'];
                        $info['openid'] = '';
                        $_SESSION['qy_uid'] = $result['UserId'];

                    }else{//说明不是企业用户
                        $info['userId'] = '';
                        $info['openid'] = $result['OpenId'];
                    }

                }elseif ($result['errcode'] == '40029'){//code不合法
                    $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirectUrl."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
                    header('Location:'.$auth_url);exit;
                }

            }
        }

        if(!isset($_SESSION['qy_uid'])){
            return $info;
        }else{
            return [
                'userId' =>$_SESSION['qy_uid'],
                'openid'=>''
            ];
        }
    }

    /**
     * 读取成员详细信息
     * @param $user_id  string 成员userid
     * @return bool|mixed
     */
    function getQyInfo($user_id){
        $access_token = getQYAccessToken('./xt_data/access_token.json');
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&userid=".$user_id;
        $result = json_decode(httpGet($url),true);
        if(isset($result['errcode']) &&$result['errcode'] == 0){//获取用户详细信息
            return $result;
        }else{
            return false;
        }
    }


    /**
     * 获取微信扫码二维码
     * @param $openid
     * @param $wxUserName
     * @param $hhrId
     * @return bool|mixed
     */
    function getWxScanQrCode($openid,$wxUserName,$hhrId){
        $sign = md5($openid.'_'.$wxUserName.'_'.$hhrId.'_'.'SWws_aa4WWF32WR!');
        $param = [
            'openid'=>$openid,
            'wxusername'=>$wxUserName,
            'mid'=>$hhrId,
            'sign'=>$sign,
        ];

        $paramString  = http_build_query($param);
        $url = 'http://henanapi.ahzkcy.com/geterweimawxapiforapi.php?'.$paramString;
        $res = json_decode(curlGet($url),true);

        addApiRequestLog(__FUNCTION__,json_encode($res,JSON_UNESCAPED_UNICODE));
        if(!empty($res)){
            return $res;
        }

        return false;
    }

    /**
     * 获取好友上传进度
     * @param int $hhrId 用户主键
     * @return array
     */
    function getWxFriendUploadProcess($hhrId = 0){
        $url = 'http://henanapi.ahzkcy.com/checkerweimaloginForProcess.php?mid='.$hhrId;
        $res = json_decode(curlGet($url),true);

        addApiRequestLog(__FUNCTION__,json_encode($res,JSON_UNESCAPED_UNICODE));

        if(isset($res['code']) && $res['code'] == 1){
            return [
                'result'=>2,
                'message'=>2
            ];
        }else{
            return [
                'result'=>0,
                'message'=>''
            ];
        }
    }

    /**
     * 获取好友上传的地区类型
     * @param int $hhrId 用户主键
     * @return array
     */
    function getMemberAreaType($hhrId = 0){
        $getUrl = 'http://henanapi.ahzkcy.com/getmidareatype.php?mid='.$hhrId;
        $areaType = json_decode(curlGet($getUrl),true);
        addApiRequestLog(__FUNCTION__,json_encode($areaType,JSON_UNESCAPED_UNICODE));

        return $areaType;
    }

    /**
     * 上传通讯录
     * @param $file
     * @param $hhrId
     * @param array $params
     * @return array|bool
     */
    function uploadVcfFile($file,$hhrId,$params = []){
        $fileData = 'hhrId_'.$hhrId;
        $path = $params['path'].$fileData.'/';   //上传文件路径

        $fileType = $params['fileType'];
        $fileName = $file["tmp_name"];    //获取临时文件名
        if(!file_exists($path)) {
            mkDirs($path);
            return true;
        }
        if(empty($fileName)){
            runJson(2003,'vcf文件内容为空');
        }
        $res = file_get_contents($fileName);
        if($res == ''){
            runJson(2003,'vcf文件内容为空');
        }
        //是否存在文件
        if(!is_uploaded_file($file['tmp_name'])) {
            runJson(2004,'请上传v c f文件');
        }

        $suffix = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));   //获取文件类型

        //检查文件类型
        if(!in_array($suffix, $fileType)) {
            runJson(2005,'文件类型不符');
        }

        $pathInfo = pathinfo($file["name"]);   //获取文件名相关信息
        $fileExt = $pathInfo['extension'];   //获取文件扩展名(后缀)
        $newFileName = date('YmdHis').'_'.mt_rand(100000,999999).'.'.$fileExt;   //以时间戳命名新文件

        //检查是否有同名文件
        if(file_exists($newFileName)) {
            runJson(2006,'同名文件已存在');
        }
        file_put_contents($path.$newFileName,$res);

        $path = $path.$newFileName;   //上传到服务器的文件

        return [
            'errCode'=>0,
            'path'=>$path,
            'suffix'=>$suffix
        ];
    }
	
	
	    //获取openid
    function diffAuthOpenid() {
        //获取访问平台
        $wxPlatform = visitPlatform();
        $_SESSION['wxPlatform'] = $wxPlatform;//存储下访问平台

        if($wxPlatform == 'workWx') {   //企业微信

            //获取企业微信openid
            //unset($_SESSION['workOpenid']);//便于测试
            if(isset($_SESSION['workOpenid']) && !empty($_SESSION['workOpenid'])){
                $workOpenid = $_SESSION['workOpenid'];
            }else{

                require_once (ROOT.'library/Auth/qiYeWxAuth.class.php');  	//加载企业微信的授权相关文件
                require_once (ROOT.'include/tydic.class.php');			//授权页面做了安全验证，用的是迪科的加密方式
                $qiYeWxAuth = new qiYeWxAuth();
                $tyDic = new tydic();

                //获取code
                $redirectUrl = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                if(isset($_GET['code'])){
                    //获取企业openid
                    $getOpenidUrl = WEBSITEURL."/qiye/getAuthOpenidAndUid.php";
                    $params = [
                        'code'=>$_GET['code'],
                        'org'=>'xt_auth',
                        'timeStamp'=>date('YmdHis'),
                    ];
                    $params['sign'] = $tyDic->getSign($params);
                    $res = curlPost($getOpenidUrl, $params);
                    $data = json_decode($res, TRUE);
                    if(isset($data['errcode']) && $data['errcode'] == 0){
                        $workOpenid = $data['data']['openid'];
                    }else{
                        //如果出现code失效[失效有对应的code码]，则再次请求一次其他情况，直接返回错误码
                        if($data['errcode'] == '40029'){
                            $qiYeWxAuth->getAuthCode($redirectUrl);
                        }else{
                            echo "授权失败：错误码{$data['errcode']}--->{$data['errmsg']}";
                            exit();
                        }
                    }
                }else{
                    $qiYeWxAuth->getAuthCode($redirectUrl);
                }
            }

            $_SESSION['workOpenid'] = $workOpenid;
            return $workOpenid;

        } else {   //个人微信
            $baseSelfName = basename($_SERVER['REQUEST_URI']);
            $openid = wxauthinfo_snsapi_userinfo($baseSelfName);;
            return $openid;
        }
    }

    /**
     * @param $href
     * @param string $scope
     * @param int $type $type 参数  登录时，会和其他系统互动，type为1时，，需要单独处理一下
     * @return array|string
     */
    function wxauthinfo_snsapi_userinfo($href,$scope='snsapi_base',$type = 2){
        $visit_url = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        //获取openid
        if(isset($_POST)&&!empty($_POST)){
            if(isset($_POST['errcode']) && $_POST['errcode'] == 40001){
                echo '<script>window.location.href="'.$visit_url.'";</script>';exit;
            }else{
                $openid = isset($_POST['openid']) && !empty($_POST['openid']) ? addslash($_POST['openid']):'';
                $_SESSION['openid'] = $openid;
                if(empty($openid)){header('Location:'.$visit_url);}

                if($type == 1){
                    header('Location:'.$visit_url.'&openid='.$openid);exit;
                }else{
                    header('Location:'.$visit_url.'?openid='.$openid);exit;
                }
            }
        }else if(isset($_GET['openid'])){
            $openid = addslash($_GET['openid']);
            if($openid == ''){
                header('Location:http://xiaoyang.xuetang.cn/apps/index.php/AuthApi/huilong1?vendor_url='.urlencode($visit_url));exit;
            }

        }else if(isset($_SESSION['openid'])&&$_SESSION['openid']!=''){
            $openid = $_SESSION['openid'];
            if($openid == ''){
                header('Location:http://xiaoyang.xuetang.cn/apps/index.php/AuthApi/huilong1?vendor_url='.urlencode($visit_url));exit;
            }
        }else{
            header('Location:http://xiaoyang.xuetang.cn/apps/index.php/AuthApi/huilong1?vendor_url='.urlencode($visit_url));exit;
        }
        $_SESSION['openid'] = $openid;
        return $openid;
    }

    /*
     * 接口中如何获取不同载体的openid
     */
    function getAjaxDiffPlatformAndOpenid(){
        $wxPlatForm = $_SESSION['wxPlatform'];
        //企业
        if($wxPlatForm == 'workWx'){
            $openid = $_SESSION['workOpenid'];
            //个人
        }else{
            $openid = $_SESSION['openid'];
        }
        return [
            'openid'=>$openid,
            'wxPlatForm'=>$wxPlatForm
        ];
    }


    /*
     * 获取指定平台的openid
     */
    function getOpenidByPlatform($memberInfo){
        $wxPlatForm = visitPlatform();
        if($wxPlatForm == 'workWx'){
            $openid = $memberInfo['work_openid'];
            //个人
        }else{
            $openid = $memberInfo['openid'];
        }
        return $openid;
    }
	
	
	
	
	
	
	
	



?>