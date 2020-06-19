<?php

    /**
     * 断点调试
     * @param $val
     */
    function dump($val){
        echo '<pre/>';var_dump($val); exit();
    }


    function curlGet($url) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
        }

        curl_setopt( $oCurl, CURLOPT_NOSIGNAL,1);    //注意，毫秒超时一定要设置这个
        curl_setopt( $oCurl, CURLOPT_TIMEOUT_MS,3000);  //超时毫秒，cURL 7.16.2中被加入

        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );

        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    function curlPost($url, $param) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
        }
        if (is_string ( $param )) {
            $strPOST = $param;
        } else {
            $aPOST = array ();
            foreach ( $param as $key => $val ) {
                $aPOST [] = $key . "=" . urlencode ( $val );
            }
            $strPOST = join ( "&", $aPOST );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );

        curl_setopt( $oCurl, CURLOPT_NOSIGNAL,1);    //注意，毫秒超时一定要设置这个
        curl_setopt( $oCurl, CURLOPT_TIMEOUT_MS,3000);  //超时毫秒，cURL 7.16.2中被加入

        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );

        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * json格式请求 post
     * @param $url
     * @param $param
     * @return bool|string
     */
    function curlPostJson($url, $param) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
        }

        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt(  $oCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt( $oCurl, CURLOPT_NOSIGNAL,1);    //注意，毫秒超时一定要设置这个
        curl_setopt( $oCurl, CURLOPT_TIMEOUT_MS,5000);  //超时毫秒，cURL 7.16.2中被加入

        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        //curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $param );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );

        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    /**
     * 写入session
     * @param $name
     * @return mixed|string
     */
    function setSession($name, $value) {
        $_SESSION[$name] = $value;
        return true;
    }


    /**
     * 获取session
     * @param $name
     * @return mixed|string
     */
    function getSession($name) {
        if(!isset($_SESSION[$name]) || empty($_SESSION[$name])) {
            return '';
        }
        return $_SESSION[$name];
    }



    /**
     * 处理数据  数据类型转换
     */
    function getParams($par, $key, $type = 'string') {
        $val = '';
        $res = (isset($par[$key]) && !empty($par[$key])) ? $par[$key] : '';
        switch ($type) {
            case'string':
                $val = $res ? replaceSpecialChar($par[$key]) : '';
                break;
            case 'int':
                $val = $res ? intval($par[$key]) : 0;
                break;
            case 'float':
                $val = $res ? number_format($par[$key], 2) : 0;
                break;
            case 'date':
                $val = $res ? date('Y-m-d', strtotime($par[$key])) : '';
                break;
            case 'dateTime':
                $val = $res ? date('Y-m-d H:i:s', strtotime($par[$key])) : '';
                break;
            default:
                $val = $res ? replaceSpecialChar($par[$key]) : '';
        }
        return $val;
    }


    /**
     * 替换特殊字符
     * @param $strParam
     * @return string|string[]|null
     */
	function replaceSpecialChar($strParam){
		$regex = "/\'|\ |\~|\!|\@|\#|\%|\^|\&|\*|\(|\)|\{|\}|\?|\[|\]|\，|\;|\"|\`|\|/";
		return preg_replace($regex,"",$strParam);
	}


    /**
     * 常见的正则验证
     * @param $value
     * @param $rule
     * @return bool
     */
    function regex($value,$rule) {
        $validate = [
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'qq'        =>  '/^\d*$/',
            'telephone' =>  '/^0([1-9]{3})([0-9]{7,8})$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
            'mobile'    =>  '/1[3-9]{1}[0-9]{1}[0-9]{8}/',
        ];
        // 检查是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule       =   $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
    }


    /**
     * 返回json
     * @param int $code  错误码 0 代表成功   其他代表错误
     * @param string $msg 错误信息
     * @param string $data 返回
     */
	function runJson($code = 0, $msg = '', $data = []){
		$json = [
		    'errCode' => $code,
            'errMsg' => $msg,
            'data' => $data,
        ];
		echo json_encode($json,JSON_UNESCAPED_UNICODE);exit;
	}




    function ASCIIArr($params = array()) {
        //echo '&amptimes';die;
        if (!empty($params)) {
            $p = ksort($params);
            if ($p) {
                $str1 = '';
                $str2 = '';
                foreach ($params as $k => $val) {
                    $str1 .= $k . '=' . $val . '&';
                }
                foreach ($params as $k => $val) {
                    $str2 .= $k . $val;
                }
                $str1 = rtrim($str1, '&');
                return [$str1, $str2];
            }
        } else {
            return ['', '__'];
        }
    }



    /**
     *  递归分类
     * @param $arr
     * @param int $id
     * @param int $level
     * @return array
     */
    function dgCat($arr,$id = 0 ,$level = 0) {
        $list = array();
        foreach ($arr as $k=>$v){
            if ($v['pid'] == $id){
                $v['level']=$level;
                $v['son'] = dgCat($arr,$v['id'],$level+1);
                $list[] = $v;
            }
        }
        return $list;
    }

    /**
     * 格式化时间戳
     * @param $pastDay
     * @return string
     */
    function formatDate($pastDay){
        $timeC = time() - strtotime($pastDay);
        $dateC = round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime($pastDay))))/60/60/24);
        if($timeC <= 3*60){
            $dayC = '刚刚';
        } else if($timeC > 3*60 && $timeC <= 5*60){
            $dayC = '3分钟前';
        } else if($timeC > 5*60 && $timeC <= 10*60){
            $dayC = '5分钟前';
        } else if($timeC > 10*60 && $timeC <= 30*60){
            $dayC = '10分钟前';
        } else if($timeC > 30*60 && $timeC <= 60*60){
            $dayC = '30分钟前';
        } else if($timeC > 60*60 && $timeC <= 120*60){
            $dayC = '1小时前';
        } else if($timeC > 120*60 && $dateC == 0){
            $dayC = '今天';
        } else if($dateC == 1){
            $dayC = '昨天';
        }else{
            $dayC = date('Y-m-d',strtotime($pastDay));
        }
        return $dayC;
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

    /**
     * 创建目录
     * @param $dir
     * @param int $mode
     * @return bool
     */
    function mkDirs($dir, $mode = 0744){
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (!mkDirs(dirname($dir), $mode)) return false;
        return @mkdir($dir, $mode);
    }

    /**
     * 处理vcf
     * @param string $info  vcf文件内容信息
     * @return array
     */
    function handleVCF($info) {
        $strArr = explode('BEGIN:VCARD',$info);
        $newStrArr = array();
        foreach($strArr as $key => $val){
            $lens = strlen($val);
            if($lens < 500){
                $newStrArr[] = $val;
            }
        }
        $newArr = array();
        if($newStrArr) {
            foreach($newStrArr as $key => $val) {
                if(empty($val)) {
                    continue;
                }

                //提取名称
                $preg = '/(FN;|FN:)([\s\S]*?)(ISFAVORITE:|TEL;)/i';
                preg_match($preg,$val,$res);
                $nameStr = isset($res[2]) ? $res[2] : '';

                if(strpos($nameStr,'QUOTED-PRINTABLE:') !== false) {
                    $name = str_replace('QUOTED-PRINTABLE:',"",strstr($nameStr,'QUOTED-PRINTABLE:'));
                    $name = rawurldecode(str_replace('=','%',$name));
                    $name = mb_substr($name,0,8);
                } else {
                    $preg = '/(N:)([\s\S]*?)\;;/i';
                    preg_match($preg,$nameStr,$res);
                    $name = isset($res[2]) ? mb_substr(str_replace(";", "", $res[2]),0,8) : $nameStr;
                }
                $name = HandleTrim($name);
                $name = htmlspecialchars(filterEmoji($name));

                $search = array("\\" ,"/");
                $replace = array("","");
                $name = str_replace($search, $replace, $name);
                //end

                $val = handleTrim($val);
                $phone = getPhone($val);
                if($phone) {
                    $name = $name ? $name : $phone;
                    $newArr[$key] = [
                        'name' => $name,
                        'phone' => $phone,
                    ];
                }
            }
        }
        return $newArr;
    }

    /**
     * 去除emoji
     * @param $str
     * @return string|string[]|null
     */
    function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match)
            {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }

    /**
     * 处理字符串 替换特殊字符
     * @param $str
     * @return mixed
     */
    function handleTrim($str) {
        $search = array("'", "\"", "-", " ", "　", " ", "\n", "\r", "\t", "&", ";", "@");
        $replace = array("", "", "", "", "", "", "", "", "", "", "", "");
        return str_replace($search, $replace, $str);
    }

    /**
     * 提取手机号
     * @param $string
     * @return string
     */
    function getPhone($string) {
        $patt = '/1[3-9]{1}[0-9]{1}[0-9]{8}/';
        preg_match_all($patt,$string,$res);
        preg_match($patt,$string,$res);
        if($res) {
            return $res[0];
        }
        return '';
    }





?>
