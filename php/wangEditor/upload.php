<?php
	//图片文件的生成
	$savename = date('YmdHis',time()).mt_rand(0,9999).'.jpeg';//localResizeIMG压缩后的图片都是jpeg格式

	//生成文件夹
	$imgdirs = "upload/image/".date('Y-m-d',time()).'/';
	check($imgdirs);
	
	//获取图片文件的名字
	$fileName = $_FILES["file"]["name"];
	//图片保存的路径
	$savepath = 'upload/image/'.$savename;
	//生成一个URL获取图片的地址
//        $url = "http://localhost" . $savepath;
	$url = "http://127.0.0.11/" . $savepath;
	//返回数据。wangeditor3 需要用到的数据 json格式的
	$data["errno"] = 0;
	$data["data"] = $savepath;
	$data['url'] = "{$url}";
	//可有可无的一段，也就是图片文件移动。
	$res = move_uploaded_file($_FILES["file"]["tmp_name"],$savepath);
	//返回数据
	echo json_encode($data);

    /**
     * 检测文件夹是否存在，是否可写
     * @return bool
     */
	public function check($path){
		//文件夹不存在或者不是目录。创建文件夹
        if (!file_exists($path) || !is_dir($path)) {
            mkdir($path, 0777, true);
        }
        //判断文件是否可写
        if (!is_writeable($path)) {
            chmod($path, 0777);
        }
	}
?>