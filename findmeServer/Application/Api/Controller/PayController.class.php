<?php
namespace Api\Controller;
use Common\Model\GameModel;
use Common\Model\PayModel;
use Common\Model\UserModel;
use Common\Model\UtilsModel;
use Common\Model\WeixinModel;
use Think\Controller;
class PayController extends Controller
{

    protected function _initialize(){

        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            PayModel::return_err('error request method');
        }
	}

    /**
     * 预支付请求接口(POST)
     * @param string $openid 	openid
     * @param string $body 		商品简单描述
     * @param string $order_sn  订单编号
     * @param string $total_fee 金额
     * @return  json的数据
     */
    public function prepay(){
        $openid = I('post.openid');
        $body = I('post.body');
        $order_sn = I('post.order_sn');
        $total_fee = I('post.total_fee');

        //统一下单参数构造
        $unifiedorder = array(
            'appid'			=> XIAO_APPID,
            'mch_id'		=> PAY_MCHID,
            'nonce_str'		=> PayModel::getNonceStr(),
            'body'			=> $body,
            'out_trade_no'	=> $order_sn.'_'.time().'_'.rand(1,100),
            'total_fee'		=> $total_fee * 100,
            'spbill_create_ip'	=> get_client_ip(),
            'notify_url'	=> 'https://'.$_SERVER['HTTP_HOST'].'/api.php',
            'trade_type'	=> 'JSAPI',
            'openid'		=> $openid
        );

        $unifiedorder['sign'] = PayModel::makeSign($unifiedorder);

        //请求数据
        $xmldata = PayModel::array2xml($unifiedorder);

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = PayModel::curl_post_ssl($url, $xmldata);

        if(!$res){
            PayModel::return_err("Can't connect the server");
        }
        // 这句file_put_contents是用来查看服务器返回的结果 测试完可以删除了
        //file_put_contents(APP_ROOT.'/Statics/log1.txt',$res,FILE_APPEND);

        $content = PayModel::xml2array($res);

        if(strval($content['result_code']) == 'FAIL'){
            PayModel::return_err(strval($content['err_code_des']));
        }
        if(strval($content['return_code']) == 'FAIL'){
            PayModel::return_err(strval($content['return_msg']));
        }
        PayModel::return_data(array('data'=>$content));
    }


    /**
     * 进行支付接口(POST)
     * @param string $prepay_id 预支付ID(调用prepay()方法之后的返回数据中获取)
     * @return  json的数据
     */
    public function pay(){
        $config = $this->config;
        $prepay_id = I('post.prepay_id');

        $data = array(
            'appId'		=> XIAO_APPID,
            'timeStamp'	=> time(),
            'nonceStr'	=> PayModel::getNonceStr(),
            'package'	=> 'prepay_id='.$prepay_id,
            'signType'	=> 'MD5'
        );

        $data['paySign'] = PayModel::makeSign($data);

        $this->ajaxReturn($data);
    }
}
