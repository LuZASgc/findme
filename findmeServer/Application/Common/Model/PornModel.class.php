<?php
/**
 * 色情过滤
 */
namespace Common\Model;
include_once WEB_ROOT.'/aliyuncs/aliyun-php-sdk-core/Config.php';
use \Green\Request\V20170112 as Green;

class PornModel extends BaseModel{
    const URL='https://green.cn-shanghai.aliyuncs.com';


    public static function checkPic($pic){
        $photoM=M('photo_lib',null,DB_MAIN_CFG);
        $photo=$photoM->where("path like '{$pic}'")->field('id,audit')->find();
        if($photo['audit']!=PHOTO_AUDIT_UNCHECK){
            return $photo['audit'];
        }

        $accessId='LTAI4xxrUM7AP2kD';
        $accessKey='19SclbD2oeABJahPPpTYBEuZ7d4tau';
        date_default_timezone_set('PRC');

        $iClientProfile = \DefaultProfile::getProfile("cn-shanghai", $accessId, $accessKey);
        \DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new Green\ImageSyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        $task1 = array('dataId' =>  uniqid(),
            'url' => $pic,
            'time' => round(microtime(true)*1000)
        );


        /**
         * porn: 色情
         * terrorism: 暴恐
         * qrcode: 二维码
         * ad: 图片广告
         * ocr: 文字识别
         */
        $request->setContent(
            json_encode(array("tasks" => array($task1),"scenes" => array("porn")))
        );
        $audit=PHOTO_AUDIT_UNCHECK;
        try {
            $response = $client->getAcsResponse($request);
            if(200 == $response->code){
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if(200 == $taskResult->code){
                        $sceneResults = $taskResult->results;
                        foreach ($sceneResults as $sceneResult) {
                            //$scene = $sceneResult->scene;
                            $suggestion = $sceneResult->suggestion;
                            //$label=$sceneResult->label;
                            //根据scene和suggetion做相关的处理
                            //do something
                            switch (strtolower($suggestion)){
                                case 'pass':
                                    $audit=PHOTO_AUDIT_PASS;
                                    break;
                                case 'review':
                                    $audit=PHOTO_AUDIT_REVIEW;
                                    break;
                                case 'block':
                                    $audit=PHOTO_AUDIT_UNPASS;
                                    break;
                            }
                            $photoM->where('id='.$photo['id'])->setField('audit',$audit);

                        }
                    }else{

                    }
                }
            }else{

            }
        } catch (Exception $e) {
        }

        return $audit;
    }
    /**
     * 微信支付发起请求
     */
    public static function curl_post_ssl($url, $xmldata, $second=30,$aHeader=array()){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);


        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xmldata);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }


}