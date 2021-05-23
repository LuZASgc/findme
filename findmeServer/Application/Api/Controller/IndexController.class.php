<?php
namespace Api\Controller;
use Common\Model\EditorModel;
use Common\Model\GameModel;
use Common\Model\PayModel;
use Common\Model\RedpackModel;
use Common\Model\UserModel;
use Common\Model\UtilsModel;
use Common\Model\WeixinModel;
use Think\Controller;
class IndexController extends Controller {
    const SUCCESS   =0;//成功
    const FAILURE   =1;//失败

    //微信支付回调验证
    public function index(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        // 这句file_put_contents是用来查看服务器返回的XML数据 测试完可以删除了
        //file_put_contents(APP_ROOT.'/Statics/log2.txt',$res,FILE_APPEND);

        //将服务器返回的XML数据转化为数组
        $data = PayModel::xml2array($xml);
        // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        $sign = PayModel::makeSign($data);

        // 判断签名是否正确  判断支付状态
        if ( ($sign===$data_sign) && ($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS') ) {
            $result = $data;
            //获取服务器返回的数据
            $order_sn = $data['out_trade_no'];			//订单单号
            $openid = $data['openid'];					//付款人openID
            $total_fee = $data['total_fee'];			//付款金额
            $transaction_id = $data['transaction_id']; 	//微信支付流水号

            //更新数据库
            $this->updateOrderPay($order_sn,$openid,$total_fee,$transaction_id);

        }else{
            $result = false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }
    private function updateOrderPay($order_sn,$openid,$total_fee,$transaction_id){
        $game=GameModel::getGameInstance()->where('id='.$order_sn)->find();
        if($game['hasPay']==1){
            file_put_contents(APP_ROOT.'/Statics/log2.txt',"{$order_sn},{$openid},{$total_fee},{$transaction_id}\r\n",FILE_APPEND);
            return false;
        }
        list($type,$orderId,$time,$rnd)=explode('_',$order_sn);

        switch ($type){
            case 'w':
                GameModel::getGameInstance()->where('id='.$order_sn)->save(
                    array(
                        'hasPay'=>1,
                        'transaction_id'=>$transaction_id,
                        'total_fee'=>$total_fee
                    )
                );
                break;
            case 'g':
                GameModel::getGameInstance()->where('id='.$order_sn)->save(
                    array(
                        'hasPay'=>1,
                        'transaction_id'=>$transaction_id,
                        'total_fee'=>$total_fee
                    )
                );
                RedpackModel::createRecord($game['id'],$game['totalMoney'],$game['packNum'],$game['packMin']);
                break;
        }


    }



    public function checkPayStatus(){
        $id=I('post.id',0,'intval');
        $type=I('post.type');
        if(!$id){
            $this->ajaxUpload(array('status'=>1,'msg'=>'参数错误'));
        }
        switch ($type){
            case 'w':
                $hasPay=GameModel::getWagerInstance()->where('wid='.$id)->getField('hasPay');
                break;
            case 'g':
                $hasPay=GameModel::getGameDetail()->where('gid='.$id)->getField('hasPay');
                break;
            default:
                $this->ajaxUpload(array('status'=>1,'msg'=>'参数类型错误'));
                break;
        }
        $this->ajaxUpload(array('status'=>0,'msg'=>'success','payStatus'=>$hasPay));
    }




    public function ajaxUpload(){
        $from = I('get.from');
        $res = UtilsModel::imageUpload('file',FILE_UPLOAD_DIR);
        $this->ajaxReturn($res);

        if($from && $res['id'] != 0){
            $fromArr = explode(',',$from);
            foreach($fromArr as $key => $val){
                $result[$key] = image_resize($res['msg'],$val);
            }
            $this->ajaxReturn($result[0]);
        } else{
            $this->ajaxReturn($res);
        }

    }

    /**
     * EditorUploadImage | 编辑器图片上传
     * User: Rick.Sun.<sunwgjj@126.com>
     * Return: bool|int
     * Address: 浙江宣逸网络科技有限公司
     * @return bool|int
     */
    function editorUploadImage()
    {
        $tmpfile = $_FILES['upfile']['tmp_name'];
        $blob = file_get_contents($tmpfile);

        $temp = array(1=>'.gif', 2=>'.jpeg', 3=>'.png');
        $extName = $temp[getimagesizefromstring($blob)[2]];
        $url = UtilsModel::UploadImage($blob,C('FILE_UPLOAD_DIR'),$extName);

        if($extName == '.gif') {
            return print(json_encode(array(
                'state' => 'SUCCESS',
                'url' => $url,
                'title' => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
                'original' => htmlspecialchars($_FILES['upfile']['name'], ENT_QUOTES)
            )));
        } else {
            $res=image_resize($url,"IMG_UEDITOR_UPLOAD");

            if($res['id'] >0){
                return print(json_encode(array(
                    'state' => 'SUCCESS',
                    'url' => $res['msg'],
                    'title' => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
                    'original' => htmlspecialchars($_FILES['upfile']['name'], ENT_QUOTES)
                )));
            } else{
                return print(json_encode(array(
                    'state' => $res['msg'],
                    'url' => $url,
                    'title' => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
                    'original' => htmlspecialchars($_FILES['upfile']['name'], ENT_QUOTES)
                )));
            }
        }


    }

    // base64图片上传
    public function base64upload() {
        $dataURL = $_POST['dataURL'];
        list($mime, $data) = explode(',', $dataURL);
        $blob = base64_decode($data);
        $mime_array = explode(';', $mime);
        $mime_array = $mime_array[0];
        $extName=explode('/', $mime_array);
        $extName = $extName[1];

        $savePath=C('FILE_UPLOAD_DIR');
        $result = UtilsModel::UploadImage($blob,$savePath,$extName);
        $from = I('get.from');
        if($from && $result){
            //生成对应规格缩略图
            $res = image_resize($result,$from);
            if($res['id'] != 0){
                return print(json_encode(array('status' => 0, 'url' => $res['msg'])));
            } else{
                return print(json_encode(array('status' => 1, 'msg' =>$res['msg'])));
            }

        }
        else if ($result) {
            return print(json_encode(array('status' => 0, 'url' => $result)));
        } else{
            return print(json_encode(array('status' => 1, 'msg' => "图片上传失败，请重试…")));
        }

    }



}