<?php
/**
 * 短信接口
 */
namespace Api\Controller;
use Api\BaseController;
use Common\Model\SmsModel;

class SmsController extends BaseController {

    public function index(){
        die;
    }


    /**
     * 发送短信
     * phone 手机号码 必填 多个用,分割
     * content 发送内容 必填
     * Author: RickSun
     *
     * return array('status'=>true|false,'msg'=>'发送成功')
     */
    public function sendSms(){
        $phone = trim($_REQUEST['phone']);
        $content = trim($_REQUEST['content']);

        M('t_sms_log', null, DB_MAIN_CFG)->add( array('addTime'=>time(),'phone'=>$phone,'content'=>$content,'type'=>2) );
        $phoneArr = explode(',',$phone);
        if(count($phoneArr)>50){
            $phonestr = '';
            $i = 1;
            foreach ( $phoneArr as $val){
                if($phonestr) $phonestr .= ','.$val;
                else $phonestr = $val;

                if($i == 50){
                    $i = 1;
                    SmsModel::sendSms($phonestr,$content);
                    $phonestr = '';
                }else{
                    ++$i;
                }
            }
        }else{
            SmsModel::sendSms($phone,$content);
        }
        $this->dataSuccess['msg'] = '发送成功';

        $this->ajax($this->dataSuccess);
    }

    /**
     * 发送验证短信
     * phone 手机号码 必填 一个
     * bind 1 绑定 | 0 找回密码
     * Author: RickSun
     *
     * return array('status'=>true|false,'msg'=>'发送成功','code'=>$code)
     */
    public function sendSmsCode(){
        $phone =I('get.phone','');// trim($this->parameter['phone']);
        $bindFlag =I('get.bind',0,'int');// intval($this->parameter['bind']);
        $uid =I('get.uid',0,'int');// intval($this->parameter['uid']);
        $this->ajaxReturn(SmsModel::sendSmsCode($phone, $bindFlag, $uid));
    }

    /**
     * 获取发送短信验证 code
     * phone 手机号码 必填
     * Author: RickSun
     *
     * return array('status'=>true|false,'code'=>$code)
     */
    public function getCode(){
        $phone = trim($_REQUEST['phone']);
        $row = M('t_sms_log', null, DB_MAIN_CFG)->where("phone = '{$phone}' AND type = 1 AND status = 1")->find();
        if(!$row){
            $this->ajax($this->dataFailure);
            return;
        }
        $this->dataSuccess['code'] = $row['code'];
        $this->ajax($this->dataSuccess);
    }



}