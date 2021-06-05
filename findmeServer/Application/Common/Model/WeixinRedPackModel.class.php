<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


use Think\Exception;

class WeixinRedPackModel extends BaseModel{

    public static function sendPack($openId,$money,$title,$wish){

        $mchid  = PAY_MCHID;// '1485146632';          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
        $apiKey = PAY_APIKEY;// 'zaq1xsw2cde3vfr4bgt5nhy6mju7ki8l';   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥

        $appid  = XIAO_APPID;//'wx681916c9f34511b4';  //微信支付申请对应的公众号的APPID
        $appKey = XIAO_SECRET;// '98a6762492d80988e0e54feab038e9f9';   //微信支付申请对应的公众号的APP Key
//填写证书所在位置，证书在https://pay.weixin.qq.com 账户中心->账户设置->API安全->下载证书，下载后将apiclient_cert.pem和apiclient_key.pem上传到服务器。

        $path=WEB_ROOT;
        $apiclient_cert = $path.DIRECTORY_SEPARATOR.'Lib'.DIRECTORY_SEPARATOR.'Pay'.DIRECTORY_SEPARATOR.'apiclient_cert.pem';
        $apiclient_key  = $path.DIRECTORY_SEPARATOR.'Lib'.DIRECTORY_SEPARATOR.'Pay'.DIRECTORY_SEPARATOR.'apiclient_key.pem';
//①、获取当前访问页面的用户openid（如果给指定用户发送红包，则填写指定用户的openid)
        $wxPay = new WxpayService($mchid,$appid,$appKey,$apiKey,$apiclient_cert,$apiclient_key);
//②、发送红包
        $outTradeNo = uniqid();     //你自己的商品订单号
        $result = $wxPay->createJsBizPackage($openId,$money,$outTradeNo,SITE_NAME,$wish,$title);
        return $result;
    }
}

class WxpayService
{
    protected $mchid;
    protected $appid;
    protected $appKey;
    protected $apiKey;
    protected $apiclient_cert;
    protected $apiclient_key;
    public $data = null;
    public function __construct($mchid, $appid, $appKey,$key,$apiclient_cert,$apiclient_key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->appKey = $appKey;
        $this->apiKey = $key;
        $this->apiclient_cert = $apiclient_cert;
        $this->apiclient_key = $apiclient_key;
    }
    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $scheme = $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
            $baseUrl = urlencode($scheme.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }
    /**
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        $res = self::curlGet($url);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }
    /**
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["secret"] = $this->appKey;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
    /**
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign") $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 统一下单
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位分
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 支付时间
     * @return string
     */
    public function createJsBizPackage($openid, $totalFee, $outTradeNo, $sendName,$wishing,$actName)
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $unified = array(
            'wxappid' => $config['appid'],
            'send_name' => $sendName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            're_openid' => $openid,
            'mch_billno' => $outTradeNo,
            //'client_ip' => '127.0.0.1',
            'total_amount' => intval($totalFee),       //单位 转为分
            'total_num'=>1,     //红包发放总人数
            'wishing'=>$wishing,      //红包祝福语
            'act_name'=>$actName,           //活动名称
            'remark'=>'remark',               //备注信息，如为中文注意转为UTF8编码
            'scene_id'=>'PRODUCT_2',      //发放红包使用场景，红包金额大于200时必传。https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_4&index=3
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = $this->curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack', self::arrayToXml($unified));
        file_put_contents('1.txt',print_r($responseXml,true));
//      print_r($responseXml,true);die;
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            return ('PARSE_ERROR');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') { //通信状态
            return $unifiedOrder->return_msg;
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {//业务状态
            return($unifiedOrder->err_code_des);
        }
        return 'SUCCESS';
    }
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,$this->apiclient_cert);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,$this->apiclient_key);
        //第二种方式，两个文件合成一个.pem文件
//        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        file_put_contents('1.txt',$xml);
        return $xml;
    }
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}