<?php
namespace Api\Controller;
use Common\Model\WeixinModel;
use Common\Model\WeixinStoreModel;
use Common\Model\WeixinTicketModel;
use Think\Controller;
class WeChatController extends Controller {
    const SUCCESS   =0;//成功
    const FAILURE   =1;//失败
    public function index(){
        $this->show('微信相关','utf-8');
    }



    /**
     * 获取微信通信用的token
     * Author: shijy
     * @return mixed
     */
    public function getAccessToken(){
        $this->ajaxReturn(WeixinModel::getAccessToken());
    }

    /**
     * 获取微信js api ticket
     * Author: shijy
     */
    public function getJsApiTicket(){
        $token=I('get.access_token',null);
        $this->ajaxReturn(WeixinModel::getJsApiTicket($token));
    }

    /**
     * 获取微信分享用的签名包
     * Author: shijy
     */
    public function getSignPackage(){
        $this->ajaxReturn(WeixinModel::getSignPackage());
    }

    public function getStoreList(){
       $return= WeixinStoreModel::getStoreUseFull();
       $this->ajaxReturn($return);
    }

}