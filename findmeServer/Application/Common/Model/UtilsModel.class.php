<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


use Think\Exception;

class UtilsModel extends BaseModel{
    const ONE_DAY   = 86400;  //一天时间
    const LOSS_TIME = 604800;//流量时间，超过这个时间视为流失

    public static function checkLogin(){
        $uid=session('uid');
        if(!$uid) redirect(U('User/login')."&uri=".urlencode($_SERVER['REQUEST_URI']));
    }

    

    /**
     * 获取昨天的开始，结束时间戳
     * Author: shijy
     * @return array
     */
    public static function getYesterDay(){
        return self::getLastDay(-1);
    }
    /**
     * 获取之前月的开始，结束时间戳
     * Author: shijy
     * @param int $preMonth  提前月数，默认为1
     * @return array
     */
    public static function getLastDay($preDay=-1){
        list($year,$month,$day,$hour,$miniute,$second)=explode('-',date('Y-m-d-H-i-s',time()));

        $firstDayOfThisMonth = mktime(0, 0, 0, $month, $day, $year);
        $theBegin = strtotime("{$preDay} day",$firstDayOfThisMonth);

        $str='-1 second';
        if($preDay<-1){
            $preDay +=1;
            $str .= " {$preDay} day";
        }
        $theEnd   = strtotime( $str,$firstDayOfThisMonth);
        return array($theBegin,$theEnd);
    }

    //返回某个时间所在周的开始和结束时间戳
    public static function getTimeWeek($time){
        $theBegin=mktime(0,0,0,date('m',$time),date('d',$time)-date('w',$time)+1,date('Y',$time));
        $theEnd=mktime(23,59,59,date('m',$time),date('d',$time)-date('w',$time)+7,date('Y',$time));
        return array($theBegin,$theEnd);
    }

    /**
     * 获取之前月的开始，结束时间戳
     * Author: shijy
     * @param int $preMonth  提前月数，默认为1
     * @return array
     */
    public static function getLastMonth($preMonth=-1){
        list($year,$month,$day,$hour,$miniute,$second)=explode('-',date('Y-m-d-H-i-s',time()));

        $firstDayOfThisMonth = mktime(0, 0, 0, $month, 1, $year);
        $theBegin = strtotime("{$preMonth} month",$firstDayOfThisMonth);
        $str='-1 second';
        if($preMonth<-1){
            $preMonth +=1;
            $str .= " {$preMonth} month";
        }
        $theEnd   = strtotime( $str,$firstDayOfThisMonth);
        return array($theBegin,$theEnd);
    }

    /**
     * 获取上一周，开始结束时间戳
     *
     * User: shijy
     * @return array（开始，结束时间戳）
     */
    public static function getLastWeek(){
        //获取上周起始时间戳和结束时间戳
        $theBegin=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $theEnd=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        return array($theBegin,$theEnd);
    }


    /**
     * 获取当前月份第一天和最后一天对应星期及该月天数
     * Author:wwl
     * @return array
     */
    public static function getWeekAndDay($time){
        list($year,$month,$day,$hour,$minute,$second)=explode('-',date('Y-m-d-H-i-s',$time));
        $firstDayOfThisMonth = mktime(0,0,0,$month,1,$year);
        $week   = date('w',$firstDayOfThisMonth);
        $dayNum = date('t',$firstDayOfThisMonth);
        return array($week,$dayNum);
    }




    public static function getWeekName($timestamp){
        $week   = date('w',$timestamp);
        $dayStr=array('周日','周一','周二','周三','周四','周五','周六');
        return $dayStr[$week];
    }

    /**
     * 内容宽度限制
     * Author: shijy
     * @param $content
     * @return mixed
     */
    public static function fixHtmlWidth($content)
    {
        $regx = '/width:([ ]*\d+(\.\d*)?(px|%))/';
        if (!preg_match_all($regx, $content, $matches)){
            return $content;
        }

        $replace = array();
        foreach ($matches[1] as $k => $v) {
            if (strcmp($matches[2][$k], '%') == 0) {
                if ($v + 0 > 100) {
                    $replace[$v] = '100%';
                }
            } else {
                if ($v + 0 > 260) {
                    $replace[$v] = '100%';
                }
            }
        }
        foreach ($replace as $k => $v) {
            $content = str_replace($k, $v, $content);
        }
        return $content;
    }

    /**
     * 代码清理
     * Author: shijy
     * @param $content
     * @return mixed
     */
    public static function tidyHtml($content) {
        //$content = strip_tags($content, '<img><p><br>');
        //$content = preg_replace('/<p[^>]*>/', '<p>', $content);
        $content = preg_replace('/<br[^>]*>/', '<br/>', $content);
        return preg_replace('/<img[^>]+src=[\'"]?(http[s]?:\/\/[^\'"]*)[^>]*>/', '<img src="$1" />', $content);
    }




    public static function NativeThumb($content) {
        $content=self::fixHtmlWidth($content);
        $content=self::tidyHtml($content);
        // 匹配正文中的 jpg 和 png 图片为照片
        if (!preg_match_all('/<img[^>]+src=.(http[s]?:\/\/[^\'"]*)[^>]*>/', $content, $matches)) {
            return array(array(), $content);
        }
       
        $thumb = array();
        // $matches[1] 中存的为图片地址数组
        foreach ($matches[1] as $rawUrl) {
            // 图片已经保存在我们的服务器上
            if (strpos($rawUrl, FILE_SHOW_PATH) !== false) {
                $thumb[] = $rawUrl;
                continue;
            }
            // 对别的服务器上的图片进行自动转存
            $tempUrl=str_replace('tp=webp','',$rawUrl);  //针对微信链接处理，webp格式，ie浏览器不能识别
            $localUrl = self::saveImageByURL($tempUrl);
            // 转存失败
            if (!$localUrl) {
                continue;
            }
            $content = str_replace($rawUrl, $localUrl, $content);
            $thumb[] = $localUrl;
        }

        return array($thumb, $content);
    }



    /**
     * 将图片转存到我们的服务器,主要针对微信图处，其它也基本适用
     * Author: shijy
     * @param $url
     * @return string
     */
    public static function saveImageByURL($url) {
        $blob = file_get_contents($url);
        // 获取不到图片
        if (!$blob) {
            return '';
        }
        $uri = 'data://application/octet-stream;base64,'  . base64_encode($blob);
        $info= getimagesize($uri);
        $ext = image_type_to_extension($info[2]);
        if(!$ext || $ext=='.' || $ext==''){
            $pattern = '/wx_fmt=([^&]*)/';
            preg_match($pattern, $url, $matches);
            $ext ='.'.$matches[1];
        }
        $url = self::UploadImage($blob,C('FILE_UPLOAD_DIR'),$ext);
        return $url;
    }

    public static function saveImageByBlob($blob){
        throw new Exception("未完成功能");

        // 获取不到图片
        if (!$blob) {
            return '';
        }
        $uri = 'data://application/octet-stream;base64,'  . base64_encode($blob);
        $info= getimagesize($uri);
        $ext = image_type_to_extension($info[2]);
        if(!$ext || $ext=='.' || $ext==''){
            $pattern = '/wx_fmt=([^&]*)/';
            preg_match($pattern, $url, $matches);
            $ext ='.'.$matches[1];
        }
        $url = self::UploadImage($blob,C('FILE_UPLOAD_DIR'),$ext);
        return $url;
    }
    /**
     * 保存用户头像
     * Author: shijy
     * @param $blob 图片地址
     * @param $uid
     * @return string
     */
    public static function saveUserHeadPic($blob,$uid){

        // 获取不到图片
        if (!$blob) {
            return '';
        }
        $subPath=UserModel::getHeadPicPath($uid);
        $savePath=USER_HEADPIC_SAVE_PATH.$subPath;
        $path=dirname($savePath);
        if(!self::mkdir($path)){
            return "";
        }

        if(false === file_put_contents($savePath, $blob)){
            return '';
        }
        $showPath=USER_HEADPIC_SHOW_PATH.$subPath;        
        return $showPath;
    }

    /**
     * 创建指定目录的目录
     * Author: shijy
     * @param $path
     * @return bool
     */
    public static function mkdir($path,$i=0){
        $i++;
        if($i>100)return false;
        if(is_dir($path)){
            if(!is_writable($path)){
                chmod($path,0600);
            }
            return true;
        }

        $fatherDir=dirname($path);
        if(strcasecmp($path,$fatherDir)==0){//根目录由返回
            return true;
        }

        if(is_dir($fatherDir)){
            if(!is_writable($fatherDir)){
                chmod($fatherDir,0600);
            }
            //echo $path,'is ok<hr />';
            return mkdir($path, 0777, true);
        }else{
            $result=self::mkdir($fatherDir,$i);
            if( $result==true){
                return mkdir($path, 0777, true);
            }else{
                return false;
            }
        }
    }



    //图片上传
    public static function UploadImage($blob,$savePath,$extName='.jpg') {
        if($extName[0]!='.')
        {
            $extName ='.'.$extName;
        }
        $name=date('His_').rand(1,1000).$extName;
        $subSavePath=date('Y-m-d/');
        $savePath.=$subSavePath;
        
        //创建目录
        if(!is_dir($savePath)){
            if(!mkdir($savePath, 0777, true)){
                return "";
            }
        }

        file_put_contents($savePath.$name, $blob);//返回的是字节数printr(a);
        $newMD5=md5_file($savePath.$name);
        $photoLib   = M('photo_lib',null,DB_MAIN_CFG);
        $existPhoto = $photoLib->where(array('md5'=>$newMD5))->find();

        if($existPhoto){
            @unlink ($savePath.$name);
            $existPhoto['ref'] += 1;
            $photoLib->save($existPhoto);
            $return= $existPhoto['path'];
        }else{
            $showPath=FILE_SHOW_PATH.$subSavePath.$name;
            $result=$photoLib->add(array('path'=>$showPath,'md5'=>$newMD5,'ref'=>1));
            if($result!==false){
                $return= $showPath;
            }
        }
        return $return;
    }


    /**
     * post 图片上传
     * @param string $photo_field_nam,form中存放图片的字段名
     * @param string $savePath 保存目录，以站点根目录开始，以/开始,以/结尾
     * @return array(
     *      id,0表示出错，其他值表示正确
     *      msg，出错时为提示信息，图片存放路径
     * )
     */
    public static function imageUpload($photo_field_name = 'photo',$savePath = FILE_UPLOAD_DIR){

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728 ;// 设置附件上传大小 3*1024*1024 4M
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = $savePath; // 设置附件上传根目录
        
        $return=array('id'=>0,'msg'=>'');
        // 上传单个文件
        $info = $upload->uploadOne($_FILES[$photo_field_name]);

        if(!$info) {// 上传错误提示错误信息
            $return['msg']=$upload->getError();
        }else{// 上传成功 获取上传文件信息
            $newMD5     = $info['md5'];
            $photoLib   = M('photo_lib',null,DB_MAIN_CFG);
            $existPhoto = $photoLib->where(array('md5'=>$newMD5))->find();
            $fullPath   = $savePath.$info['savepath'].$info['savename'];
            if($existPhoto){
                @unlink ($fullPath);
                $existPhoto['ref'] +=1;
                $photoLib->save($existPhoto);
                $return= array('id'=>$existPhoto['id'],'msg'=>$existPhoto['path']);
            }else{
                $showPath=FILE_SHOW_PATH.$info['savepath'].$info['savename'];
                $result=$photoLib->add(array('path'=>$showPath,'md5'=>$newMD5,'ref'=>1));
                if($result!==false){
                    $return= array('id'=>$result,'msg'=>$showPath);
                }
            }

        }
        return $return;
    }

    /**
     * 导入excel文件
     * @param string $excel_field_nam,form中存放excel的字段名
     * @param string $savePath 保存目录，以站点根目录开始，以/开始,以/结尾
     * Return: array(
     *     id,0表示出错，其他值表示正确
     *     msg，出错时为提示信息，图片存放路径
     * )
     */
    public function excelUpload($excel_field_name = 'excel',$savePath = '')
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->exts = array('xlsx', 'xls');// 设置附件上传类型
        $upload->rootPath = '.' . $savePath;  // 设置附件上传根目录

        $return = array('id' => 0, 'msg' => '');
        // 上传单个文件
        $info = $upload->uploadOne($_FILES[$excel_field_name]);

        if (!$info) {// 上传错误提示错误信息
            $return['msg'] = $upload->getError();
        } else {// 上传成功
            $path = $upload->rootPath.$info['savepath'].$info['savename'];
            $return = array('id' => 1, 'msg' => $path);
        }
        return $return;
    }

    /**
     * 测试文字长度
     * Author: shijy
     * @param $str
     * @return int
     */
    public static function strlen(&$str){
        //return mb_strlen($str,'utf-8');
        return strlen($str);
    }


    /**
     * 获取完整的domain值 
     * Author: shijy
     * @return string 如 http://new.kannb.com/
     */
    public static function getFullDomain(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol.$_SERVER['HTTP_HOST'];

    }


    /**
     * 对外部系统进行通知,除非指定，否则默认使用轿辰会
     * Author: shijy
     * @param $actionName
     * @param $paramObj
     * @return mixed
     */
    public static function broadcast($actionName,$paramObj,$companySwitch=null){
        if($companySwitch){
            $company=$companySwitch;
        }else{
            $company=COMPANY_JCH;
        }

        if ($company==COMPANY_NBQC){
            $company=COMPANY_JCH;
        }
        $className="\\Common\\Model\\OtherSystem\\".$company;
        
        if(!class_exists($className)){
            return false;
        };
         $class = new $className();
         return $class->notice($actionName,$paramObj);
    }




    ///地址相关处理函数
    private static $cfgAddress=null;
    public static function getCfgAddress(){
        if (!is_null(self::$cfgAddress)){
            return true;
        }
        $cfgAddress= M('cfg_region',NULL,DB_MAIN_CFG)->field('ID,ParentId,cleanMerger,Name,LevelType,ShortName')->order('length(cleanMerger) desc')->select();
        $cfgAddressKV=array();
        foreach ($cfgAddress as $one){
            $cfgAddressKV[$one['ID']]=array('ParentId'=>$one['ParentId'],'cleanMerger'=>$one['cleanMerger'],'LevelType'=>$one['LevelType'],'Name'=>$one['Name']);
        }

        self::$cfgAddress=array();
        self::$cfgAddress['full']=$cfgAddress;
        self::$cfgAddress['kv']=$cfgAddressKV;
    }
    public static function doAddress($address){
        $returnArray=array(
            'way'=>'',
            'district'=>0,
            'city'=>0,
            'province'=>0,
            'oldAddress'=>$address,
            'newAddress'=>$address,
            'match'=>true
        );
        //省市区全匹配
        foreach (self::$cfgAddress['full'] as $one){
            $searchStr=$one['cleanMerger'];
            if(strpos($address,$searchStr)!==false && $searchStr!=''){
                //找到地址
                switch($one['LevelType']){
                    case 3:
                        $returnArray['district']    =$one['ID'];
                        $returnArray['city']        =$one['ParentId'];
                        $returnArray['province']    =self::$cfgAddress['kv'][$one['ParentId']]['ParentId'];
                        break;
                    case 2:
                        $returnArray['district']    =0;
                        $returnArray['city']        =$one['ID'];
                        $returnArray['province']    =$one['ParentId'];
                        break;
                    case 1:
                        $returnArray['district']    =0;
                        $returnArray['city']        =0;
                        $returnArray['province']    =$one['ID'];
                        break;
                }
                $returnArray['newAddress']=str_replace( $searchStr,'',$address);
                $returnArray['district'].='-'.self::$cfgAddress['kv'][$returnArray['district']]['Name'];
                $returnArray['city'].='-'.self::$cfgAddress['kv'][$returnArray['city']]['Name'];
                $returnArray['province'].='-'.self::$cfgAddress['kv'][$returnArray['province']]['Name'];
                $returnArray['way'] ='cleanMerger';
                return $returnArray;
            }
        }
        ///////////////////////////////////////////
        //以上方式未对应到地址-
        foreach (self::$cfgAddress['full'] as $one){
            $searchStr=$one['Name'];
            if(strpos($address,$searchStr)===0){
                //找到地址
                switch($one['LevelType']){
                    case 3:
                        $returnArray['district']    =$one['ID'];
                        $returnArray['city']        =$one['ParentId'];
                        $returnArray['province']    =self::$cfgAddress['kv'][$one['ParentId']]['ParentId'];
                        break;
                    case 2:
                        $returnArray['district']    =0;
                        $returnArray['city']        =$one['ID'];
                        $returnArray['province']    =$one['ParentId'];
                        break;
                    case 1:
                        $returnArray['district']    =0;
                        $returnArray['city']        =0;
                        $returnArray['province']    =$one['ID'];
                        break;
                }
                $returnArray['district'].='-'.self::$cfgAddress['kv'][$returnArray['district']]['Name'];
                $returnArray['city'].='-'.self::$cfgAddress['kv'][$returnArray['city']]['Name'];
                $returnArray['province'].='-'.self::$cfgAddress['kv'][$returnArray['province']]['Name'];
                $returnArray['way'] ='Name';

                $returnArray['newAddress']=str_replace( $searchStr,'',$address);
                return $returnArray;
            }
        }

        ///////////////////////////////////////////
        //以上方式未对应到地址-
        foreach (self::$cfgAddress['full'] as $one){
            $searchStr=$one['ShortName'];
            if(strpos($address,$searchStr)===0){
                //找到地址
                switch($one['LevelType']){
                    case 3:
                        $returnArray['district']    =$one['ID'];
                        $returnArray['city']        =$one['ParentId'];
                        $returnArray['province']    =self::$cfgAddress['kv'][$one['ParentId']]['ParentId'];
                        break;
                    case 2:
                        $returnArray['district']    =0;
                        $returnArray['city']        =$one['ID'];
                        $returnArray['province']    =$one['ParentId'];
                        break;
                    case 1:
                        $returnArray['district']    =0;
                        $returnArray['city']        =0;
                        $returnArray['province']    =$one['ID'];
                        break;
                }
                $returnArray['district'].='-'.self::$cfgAddress['kv'][$returnArray['district']]['Name'];
                $returnArray['city'].='-'.self::$cfgAddress['kv'][$returnArray['city']]['Name'];
                $returnArray['province'].='-'.self::$cfgAddress['kv'][$returnArray['province']]['Name'];
                $returnArray['way'] ='ShortName';

                $returnArray['newAddress']=str_replace( $searchStr,'',$address);
                return $returnArray;
            }
        }

        $returnArray['match']=false;
        return $returnArray;
    }

    public static function formatAddress($address){
        self::getCfgAddress();
        $result=self::doAddress($address);
        if($result['district']==0 || $result['city']==0 || $result['province']==0){
            if(strcmp($result['oldAddress'],$result['newAddress'])!=0){//不等，说明有变化
                $result2=self::doAddress($result['newAddress']);
                if($result2['match']==false){
                    return $result;
                }
                if($result['district']==0){
                    $result['district']=$result2['district'];
                }
                if($result['city']==0){
                    $result['city']=$result2['city'];
                }
                if($result['province']==0){
                    $result['province']=$result2['province'];
                }
                $result['newAddress']=$result2['newAddress'];
                $result['way'] .='-'.$result2['way'];
            }
        }
        return $result;
    }

    public static function getRegionById($regionId){
        self::getCfgAddress();
        return self::$cfgAddress['kv'][$regionId];

    }




    /**
     * 根据省级编号获取地址名
     * @param $uinfo
     * @param string $connector
     * @return string
     */
    public static function getRegionNameByProvince($uinfo,$connector='/'){
        if($uinfo['province']) {
            $province = include_once(WEB_ROOT . '/data/area_info/' . $uinfo['province'] . '.php');
            $uinfo['provinceName'] = $province[$uinfo['province']]['name'];
            $addStr = $uinfo['provinceName'];
            if ($uinfo['city']) {
                $uinfo['cityName'] = $province[$uinfo['city']]['name'];
                $addStr .= $connector . $uinfo['cityName'];
            }
            if ($uinfo['district']) {
                $uinfo['districtName'] = $province[$uinfo['district']]['name'];
                $addStr .= $connector . $uinfo['districtName'];
            }
            if ($uinfo['street']) {
                $uinfo['streetName'] = $province[$uinfo['street']]['name'];
                $addStr .= $connector . $uinfo['streetName'];
            }
            return $addStr;
        }
    }


    /**
     * 根据给定地区编号，获取下级地区键值对
     * @param $pid
     * @return mixed
     */
    public static function getReginKVByParent($pid){
        return M('cfg_region',null,DB_MAIN_CFG)->where("p_id={$pid}")->getField('area_id,short_name',true);
    }

    /**
     * 根据操作编号返回操作名称
     * Author: shijy
     * @param $actID
     * @return string
     */
    public static function getActionNameByActId($actID){
        $actionArr=C('OPT_TYPE');
        if(array_key_exists($actID,$actionArr)){
            return $actionArr[$actID];
        }
        return '未定义操作'.$actID;
    }


    /**
     * 返回当前开放的访问通道
     * Author: shijy
     * @return string
     */
    public static function getOpenClient(){
        $OPEN=array();
        if(ENABLE_WAP){
            $OPEN[]='WAP';
        }
        if(ENABLE_PC){
            $OPEN[]='PC';
        }
        if(ENABLE_WEIXIN){
            $OPEN[]='微信';
        }
        if(ENABLE_APP){
            $OPEN[]='App';
        }
        if (count($OPEN)>0){
            return implode(',',$OPEN);
        }
        return null;

    }

    public static function disableClientAndTip($enable){
        if($enable){
            return true;
        }

        $open=self::getOpenClient();
        if($open){
            die("请从{$open}访问本站点");
        }else{
            die("禁止访问本站点");
        }
    }

    /**
     * 跳转链接构建
     * Author: shijy
     * @param $objType
     * @param $objId
     * @param $jumpURL
     * @return string
     */
    public static function buildJump($objType,$objId,$jumpURL){
        $tmpstr="type=".$objType.'&id='.$objId.'&url='.urlencode($jumpURL);
        return $tmpstr;
    }

    /**
     * 检查是否核销
     * Author: shijy
     */
    public static function checkMaintenance(){
        if(!SYSTEM_MAINTENANCE){
            return false;
        }
        //ip检查
        $ip=get_client_ip(0,true);
        
        $excludeIP=C('MAINTENANCE_EXCLUDE_IP');
        foreach($excludeIP as $one){
            if(strcmp($ip,$one)==0){
                return false;
            }
        }
        $excludeFunc=C('MAINTENANCE_EXCLUDE_FUNCTION');
        $upper_control=strtoupper(CONTROLLER_NAME);
        $upper_action=strtoupper(ACTION_NAME);
        $act=$upper_control.'_'.$upper_action;
        foreach($excludeFunc as $one){
            $one=strtoupper($one);
            if(strpos($act,$one)!==false){
                return false;
            }
        }
        die('<center><img src="./Public/Wap/images/update.png"></center>');
    }

    /**
     * 加用户喜爱类型指数操作
     * Author:wwl
     * @param $uid 用户编号
     * @param $contentType 指数类型
     * @param $val 指数应增值
     */
    public static function addContentTypeIndex($uid,$contentType,$val){
        $likeM =  M('statistic_user_like',null,DB_MAIN_CFG);
        if(!$likeM->where("uid={$uid}")->find()){
            self::statisticIndex($uid);
        }
        $res =$likeM->where("uid={$uid}")->setInc("index{$contentType}",$val);
        return true;
    }

    /**
     * 减用户喜爱类型指数操作
     * Author:wwl
     * @param $uid 用户编号
     * @param $contentType 指数类型
     * @param $val 指数应增值
     */
    public static function minusContentTypeIndex($uid,$contentType,$val){
        $likeM =  M('statistic_user_like',null,DB_MAIN_CFG);
        if(!$likeM->where("uid={$uid}")->find()){
           self::statisticIndex($uid);
        }
        $res = $likeM->where("uid={$uid}")->setDec("index{$contentType}",$val);
        return true;
    }

    private static function statisticIndex($uid){
        $likeM =  M('statistic_user_like',null,DB_MAIN_CFG);
        $userInfo = M('u_user_base',null,DB_MAIN_CFG)->where("uid={$uid}")->field("nickname,realName")->find();
        $data = array(
            'uid'       => $uid,
            'nickname'  => $userInfo['nickname'],
            'realName'  => $userInfo['realName'],
        );
        $likeM->add($data);
        //统计该用户的操作数据
        $obj = $likeM->where("uid={$uid}")->find();
        $praise = M('praise_log',null,DB_MAIN_CFG)->where("uid={$uid}")->getField("objContentType",true);
        foreach ($praise as $val){
            $obj['index'.$val] += INDEX_PRAISE;
        }

        $comment1 = M('comment_1',null,DB_MAIN_CFG)->where("uid={$uid} and parentID = 0")->getField("objContentType",true);
        foreach ($comment1 as $val){
            $obj['index'.$val] += INDEX_COMMENT;
        }

        $comment2 = M('comment_2',null,DB_MAIN_CFG)->where("uid={$uid} and parentID = 0")->getField("objContentType",true);
        foreach($comment2 as $val){
            $obj['index'.$val] += INDEX_COMMENT;
        }

        $event = M('e_events_join',null,DB_MAIN_CFG)->where("uid={$uid}")->getField("objContentType,isPay",true);
        foreach ($event as $key=> $val){
            if($val[$key] == 2){
                $obj['index'.$key] += INDEX_PAY;
            } else if($val[$key] == 0){
                $obj['index'.$key] += INDEX_APPLY;
            }
        }

        $point = M('exchange_log',null,DB_MAIN_CFG)->where("uid={$uid} and status=".PointMallModel::EXCHANGE_STATUS_SUCCESS)->getField("objContentType",true);
        foreach ($point as $val){
            $obj['index'.$val] += INDEX_POINT;
        }
        
        $likeM->where("uid={$uid}")->save($obj);
        return true;
    }


    public static function maskNickname(&$nickname){
        $len=mb_strlen($nickname,'utf-8');

        if($len==1){
            return $nickname;
        }
        if($len==2){
            return mb_substr($nickname,0,1).'*';
        }else{
            return mb_substr($nickname,0,1).str_repeat('*',$len-2).mb_substr($nickname,$len-1,1);
        }
    }

    /**
     * 根据区域获取对应商圈列表
     * @param $areaId
     * @return array
     */
    public static function getTradeAreaKV($areaId=0){
        if ($areaId==0){
            $list=M('cfg_trade_area',null,DB_MAIN_CFG)->where('tradeAreaName<>\'其他\'')->getField('tradeAreaId,tradeAreaName',true);
        }else{
            $list=M('cfg_trade_area',null,DB_MAIN_CFG)->where("areaId={$areaId}")->getField('tradeAreaId,tradeAreaName',true);
        }
        return $list;
    }


    /**
     * 根据经纬度计算距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return int
     */
    public static function getEarthDistance($lat1, $lng1, $lat2, $lng2){
        //将角度转为狐度
        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
        return $s;
    }



    /**
     * 发起请求
     */
    public static function curl_request($url,$method, $data=array(), $second=30,$aHeader=array()){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        if (strcasecmp($method,'get')==0){
            $param=http_build_query($data);
            if(substr($url, -1)=='?'){
                $url.=$param;
            }
        }else{
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return array('status'=>0,'msg'=>'success','data'=>$data);
        }
        else {
            $error = curl_errno($ch);
            curl_close($ch);
            return array('status'=>1,'msg'=>"call faild, errorCode:{$error}");
        }
    }

    /**
     * 获取IP地址
     * @return [String] [ip地址]
     */
    public static function getip() {
        static $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
}