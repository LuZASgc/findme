<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


class MerchPayModel extends BaseModel{

    //微信支付配置信息
    const SSLCERT_PATH = WEB_ROOT.'/Lib/Pay/apiclient_cert.pem';
    const SSLKEY_PATH  = WEB_ROOT.'/Lib/Pay/apiclient_key.pem';

    /**
     * 企业支付
     * @param string $openid 	用户openID
     * @param string $trade_no 	单号
     * @param string $money 	金额,单位分
     * @param string $desc 		描述
     * @return string 	XML 结构的字符串
     */
    public static function pay($openid,$trade_no,$money,$desc){
        $data = array(
            'mch_appid' => XIAO_APPID,
            'mchid'     => PAY_MCHID,
            'nonce_str' => PayModel::getNonceStr(),
            //'device_info' => '1000',
            'partner_trade_no' => $trade_no, //商户订单号，需要唯一
            'openid'    => $openid,
            'check_name'=> 'NO_CHECK', //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
            //'re_user_name' => 'jorsh', //收款人用户姓名
            'amount'    => $money, //付款金额单位为分
            'desc'      => $desc,
            'spbill_create_ip' => get_client_ip()
        );

        //生成签名
        $data['sign'] = PayModel::makeSign($data);
        //构造XML数据
        //pre_dump($data);
        $xmldata = PayModel::array2xml($data);
        echo $xmldata;
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        //发送post请求
        $res = self::curl_post_ssl($url, $xmldata);
        if(!$res){
            return array('status'=>1, 'msg'=>"Can't connect the server" );
        }
        // 这句file_put_contents是用来查看服务器返回的结果 测试完可以删除了
        file_put_contents(WEB_ROOT.'/Data/log2.txt',$res,FILE_APPEND);

        //付款结果分析
        $content = PayModel::xml2array($res);
        if(strval($content['return_code']) == 'FAIL'){
            return array('status'=>1, 'msg'=>strval($content['return_msg']));
        }
        if(strval($content['result_code']) == 'FAIL'){
            return array('status'=>1, 'msg'=>strval($content['err_code']),':'.strval($content['err_code_des']));
        }
        $resdata = array(
            'return_code'      => strval($content['return_code']),
            'result_code'      => strval($content['result_code']),
            'nonce_str'        => strval($content['nonce_str']),
            'partner_trade_no' => strval($content['partner_trade_no']),
            'payment_no'       => strval($content['payment_no']),
            'payment_time'     => strval($content['payment_time']),
        );
        return $resdata;
    }

    /**
     * 企业付款发起请求
     * 此函数来自:https://pay.weixin.qq.com/wiki/doc/api/download/cert.zip
     */
    public function curl_post_ssl($url, $xmldata, $second=30,$aHeader=array()){
        pre_dump($xmldata);
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

        //以下两种方式需选择一种

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,self::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,self::SSLKEY_PATH);

        //第二种方式，两个文件合成一个.pem文件
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

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