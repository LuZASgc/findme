<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/9
 * Time: 21:16
 */


function pre_dump(&$mix){
    echo '<pre>';
    var_dump($mix);
    echo '</pre>';
}


/*
 * 系统配置的数据处理（dealCurrent）、读取（readConfig）和写入（writeConfig）
 */
function dealCurrent($val){
    if(is_array($val)){
        $rem = $val[1];
        @list($val['title'], $val['mem'], $val['format'], $val['authority']) = explode('|', $rem);
    }
    return $val;
}

function writeConfig($filePath, $cfgArr,$defaultCfg,$lockFile="")
{
    $originalData = readConfig($filePath);
    //local_specialConfig是否存在
    if(!$originalData || $originalData == ""){
        $originalData = $defaultCfg;
        $str = "<?php\n";
        file_put_contents($filePath, $str);
        foreach ($originalData as $k => $v){
            foreach ($cfgArr as $key => $value) {
                if($k == $key){
                    $value = str_replace('\'','',$value);
                    if(strpos($v[0],'\'')===false){
                        $originalData[$k][0] = $value;
                    } else{
                        $originalData[$k][0] = '\''.$value.'\'';
                    }
                }
            }

            if(is_numeric($k)){
                unset($originalData[$k]);
            } else{
                $str = "define('{$k}',{$originalData[$k][0]});\n";
                file_put_contents($filePath, $str, FILE_APPEND);
            }
        }
    } else{
        foreach ($defaultCfg as $k => $v){
            foreach ($originalData as $key => $val){
                if($k == $key){
                    $val[0] = str_replace('\'','',$val[0]);
                    if(strpos($v[0],'\'')===false){
                        $defaultCfg[$k][0] = $val[0];
                    }else{
                        $defaultCfg[$k][0] = '\''.$val[0].'\'';
                    }

                }
            }
        }
        $str = "<?php\n";
        file_put_contents($filePath, $str);
        foreach ($defaultCfg as $k=>$v){
            foreach ($cfgArr as $key=>$val){
                if($k == $key){
                    $val = str_replace('\'','',$val);
                    if(strpos($v[0],'\'')===false) {
                        $defaultCfg[$k][0] = $val;
                    }else{
                        $defaultCfg[$k][0] = '\''.$val.'\'';
                    }
                }
            }
            if(is_numeric($k)){
                unset($defaultCfg[$k]);
            } else{
                $str = "define('{$k}',{$defaultCfg[$k][0]});\n";
                file_put_contents($filePath, $str, FILE_APPEND);
            }
        }
    }
    if($lockFile){
        file_put_contents($lockFile,'使用说明');
    }
}

function readConfig($filePath)
{
    if(!file_exists($filePath)){
        return array();
    }
    $rawContent = file_get_contents($filePath);
    $re = preg_replace('/(\/\*.*\*\/)/s', '', $rawContent);//清除块注释
    $arr = explode("\n", $re);

    $matchArr = array();
    $cfgKv = array();
    foreach ($arr as $key => $one) {
        $r = preg_match('/\w*define\(\s*\'(.+)\'\s*,\s*(\'?[^\']*\'?)\s*\);\s*\/\/(.+)/i', $one, $matchArr);
        if ($r > 0) {
            $cfgKv[$matchArr[1]] = array($matchArr[2], $matchArr[3]);
        } elseif (preg_match('/^\/\/.*$/', $one)) {
            $cfgKv[] = $one;
        } elseif(preg_match('/\w*define\(\s*\'(.+)\'\s*,\s*(\'?[^\']*\'?)\s*\);/i', $one, $matchArr)){
            $cfgKv[$matchArr[1]] = array($matchArr[2]);
        }
    }

    return $cfgKv;
}

//分页模板配对
function fetchPage(&$smart,&$pagerObj){
    $smart->assign('prevPage',$pagerObj->nowPage >1?$pagerObj->nowPage-1:0);
    $smart->assign('nextPage',$pagerObj->nowPage <$pagerObj->totalPages?$pagerObj->nowPage+1:0);
    $smart->assign('nowPage',$pagerObj->nowPage);
    $smart->assign('totalPage',$pagerObj->totalPages);
}


/**
 * 生成缩略图
 * @param $src_img:原图数据
 * @param $from:图片位置
 * return:id>0 执行完成，返回，msg=显示路径；id=0 报错
 */
function image_resize($src_img,$from){
    $imgPosition = C('imgPosition');
    $WH = $imgPosition[$from];
    $width = $WH['width'];$height = $WH['height'];

    if(!$from || ($width <=0 && $height<=0)){
        return array('id'=>0,'msg'=>"参数错误");
    }

    list($src_w, $src_h, $type) = getimagesize($src_img);

    $file =  substr($src_img,strlen(dirname(dirname($src_img))));
    if($width<=0){
        $addDir = "000x";
    }else{
        $addDir = $width.'x';
    }

    if($height<=0){
        $addDir .= "000";
    } else{
        $addDir .= $height.'';
    }

    $uploadFile = FILE_UPLOAD_DIR.$addDir.$file; //上传文件
    $showPath = FILE_SHOW_PATH.$addDir.$file;
    $uploadPath = dirname($uploadFile);//上传目录

    //是否已有缩略图
    if(file_exists($uploadFile)){
        return array('id'=>2,'msg'=>$showPath); //已有缩略图，取缩略图
    }
    $temp = array(1=>'gif', 2=>'jpeg', 3=>'png');
    if(!$temp[$type]){

        return array('id'=>0,'msg'=>'上传图片格式不符合，<br/>请重新选择jpeg/png/gif格式图片后重试');
    }
    $tmp = $temp[$type];
    $inFunc = "imagecreatefrom$tmp";
    $outFunc = "image$tmp";

    //载入原图
    $img = $inFunc($src_img);
    imagesavealpha($img,true);  //保存sourcePic图像的透明色;

    //等比例缩略
    if($width <= 0){
        $width = ($src_w/$src_h)*$height;
    } else if($height <=0){
        $height = $width/($src_w/$src_h);
    }

    if($src_w/$width > $src_h/$height){
        $src_w = $width * ($src_h/$height);
    }else{
        $src_h = $height * ($src_w/$width);
    }

    if(!is_dir($uploadPath)){
        $dir = mkdir($uploadPath,0777,true);
        if(!$dir){
            return array('id'=>0,'msg'=>'缩略图生成路径不存在，路径生成失败');
        }
    }

    //创建缩略图
    $dst_img = imagecreatetruecolor($width, $height); //创建缩略图
    imagealphablending($dst_img,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
    imagesavealpha($dst_img,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
    //赋值图像并改变大小
    imagecopyresampled($dst_img,$img,0,0,0,0,$width,$height,$src_w,$src_h);

    //输出图像
    $res = $outFunc($dst_img,$uploadFile);
    imagedestroy($dst_img);
    imagedestroy($img);
    if($res){
        $return = array('id'=>1,'msg'=>$showPath);  //返回压缩图
    } else{
        $return = array('id'=>0,'msg'=>'缩略图生成失败');
    }
    return $return;
}