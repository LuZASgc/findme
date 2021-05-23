<?php
namespace Api\Controller;
use Common\Model\GameModel;
use Common\Model\RedpackModel;
use Common\Model\UserModel;
use Common\Model\UtilsModel;
use Common\Model\WeixinModel;
use Think\Controller;
class GameController extends Controller {
    const SUCCESS   =0;//成功
    const FAILURE   =1;//失败
    public function index(){
        $this->show('公共支持','utf-8');
    }




    /**
     * 提交结果，
     */
    public function submitResult(){
        $gid=I('post.gid',0,'intval');//游戏编号
        $oid=I('post.oid',0,'intval');//对家uid
        $uid=session('uid');//自己uid
        $sid=I('post.sid',0,'intval');//选中uid
        $userInfo=UserModel::getUserInstance()->find($uid);


        if($oid == $sid){//正确
            $result=GameModel::BET_RESULT_WIN;
        }else{//错误
            $result=GameModel::BET_RESULT_LOSE;
        }

        $return=array('status'=>0,
            'msg'=>'success',
            'result'=>$result,//对错，1对
            'prize'=>0,//获得奖励
            'return'=>0,//对赌退回
            'next'=>'' //下一组
        );

        if(UserModel::isStar($oid)){//明星赛
            $update=array();
            if($result==GameModel::BET_RESULT_WIN){
                $userInfo['nowScore']+=1;
                $update['nowScore']=$userInfo['nowScore'];

                if($userInfo['historyScore']<$userInfo['nowScore']){
                    $userInfo['historyScore']=$userInfo['nowScore'];
                    $userInfo['historyTime']=time();

                    $update['historyScore']=$userInfo['historyScore'];
                    $update['historyTime']=$userInfo['historyTime'];
                }
                $return['next'] = $this->getNextTeam( $userInfo['nowScore']);
            }else{
                $userInfo['nowScore']=0;
                $update['nowScore']=$userInfo['nowScore'];

                $this->initGame();//重置明星序列
            }

            UserModel::getUserInstance()->where("uid={$uid}")->save($update);
        }
        else{//素人
            $gameInfo=GameModel::getGameInstance()->find($gid);
            $joinInfo=GameModel::getJoinInstance()->where("gid={$gid} and uid={$uid}")->find();

            //join记录更新
            GameModel::getJoinInstance()->where("jid={$joinInfo['jid']}")->setField('result',$result);
            
            if($result == GameModel::BET_RESULT_WIN){//成功
                if ($gameInfo['isBet']){//游戏是对赌的
                    if ($joinInfo['isBet']){//参与对赌
                        GameModel::getPrizeInstance()->startTrans();
                        $prizeRecord=GameModel::getPrizeRecordByGid($gid);
                        if($prizeRecord) {
                            UserModel::addMoney($prizeRecord['money'], $uid, GameModel::OPT_BET_WIN, $oid);
                            $return['prize']=$prizeRecord['money'];//奖励
                        }
                        UserModel::addMoney($joinInfo['money'],$uid,GameModel::OPT_BET_RETURN);
                        $return['return']=$joinInfo['money'];//退回
                        GameModel::getPrizeInstance()->commit();
                    }
                }
                else{//游戏不是对赌的
                    //判断奖池，随机给奖励
                    $prizeRecord=GameModel::getPrizeRecordByGid($gid);
                    if ($prizeRecord) {//有奖
                        UserModel::addMoney($prizeRecord['money'], $uid, GameModel::OPT_PRIZE, $oid);
                        $return['prize']=$prizeRecord['money'];//奖励
                    }
                }

                if($prizeRecord){//奖励记录更新
                    GameModel::setPrizeRecordStatus($prizeRecord['id'],GameModel::PRIZE_RECORD_STATUS_SEND);
                }

            }else{//失败
                if($joinInfo['isBet'] && $gameInfo['isBet']){//赔钱
                    UserModel::addMoney($joinInfo['money'],$oid,GameModel::OPT_BET_LOSE,$uid);
                }
            }
        }

        $this->ajaxReturn($return);
    }


    /**
     * 初始化明星列表
     */
    private function initGame(){
        $maxStarId=UserModel::getMaxStarId();
        $array=range(1,$maxStarId);
        shuffle($array);
        session('starId',implode(',',$array));
    }

    /**
     * 明星游戏
     */
    public function startStarGame(){
        $this->initGame();
        $this->ajaxReturn($this->getNextTeam(0));
    }

    /**
     * 素人
     */
    public function startSurenGame(){
        $gid=I('post.gid',0,'intval');
        $mainUid=GameModel::getGameInstance()->where('gid='.$gid)->getField('uid');
        $num=10;
        $uidArr=UserModel::getUserInstance()->where("uid<>{$mainUid} and length(album)>10")->limit($num)->getField('uid',true);
        shuffle($uidArr);
        for($i=$num-1;$i>4;$i--){
            unset($uidArr[$i]);
        }
        array_unshift($uidArr,$mainUid);
        $this->ajaxReturn( $this->getPhotos($uidArr));
    }

    /**
     * 取下一组明星
     * @param $nowScore， 当前答对的明星数量
     * @return bool|mixed
     */
    public function getNextTeam($nowScore=0){
        $idStr=session('starId');
        $idArray=explode(',',$idStr);
        if(!array_key_exists($nowScore,$idArray)){
            return false;
        }

        $nextStarId=$idArray[$nowScore];//下一场要答的主咖编号

        $uidArr=array();
        $uidArr[]=$nextStarId;

        $others=array_rand($idArray,6);
        foreach ($others as $id){
            if($idArray[$id] == $nextStarId){
                continue;
            }
            $uidArr[]=$idArray[$id];
        }

        if(count($uidArr)>6){
            unset($uidArr[6]);
        }

        return $this->getPhotos($uidArr);
    }


    private function getPhotos(&$idarray){
        $idstr=implode(',',$idarray);
        $list= UserModel::getUserInstance()->where("uid in ($idstr)")->field('uid,headPic,album')->select();
        foreach($list as $k=>$v){

            $imgs=explode(',',$v['album']);
            $idx=array_rand($imgs,1);
            $list[$k]['album']=$imgs[$idx];

            if( $idarray[0] != $v['uid'] ){
                unset($list[$k]['headPic']);
            }
        }
        shuffle($list);
        return $list;
    }





    /**
     * 用户进入
     */
    public function userLogin(){
        $openId     = I('post.openId');
        $headPic    = I('post.headPic');
        $sex        = I('post.gender');
        $nickname   = I('post.nickname');

        $this->ajaxReturn(UserModel::login($openId,$nickname,$headPic,$sex));
    }

    /**
     * 更新图集
     */
    public function updateAlbum(){
        $uid=I('post.uid',0,'intval');
        if(UserModel::isStar($uid)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'非法的参数'));
        }
        $albumStr=I('post.album');
        if(UserModel::updateAlbum($uid,$albumStr)===false){
            $this->ajaxReturn(array('status'=>1,'msg'=>'更新失败'));
        }
        $this->ajaxReturn(array('status'=>0,'msg'=>'success'));

    }


    public function buildOrder(){
        $prizeMoney=I('post.prizeMoney',0,'floatval');
        $num=I('post.num',1,'intval');
        $min=I('post.min',1,'floatval');

        $isBet=I('post.isBet',1,'intval');
        if($prizeMoney<1){
            $this->ajaxReturn(array('status'=>1,'msg'=>'红包金额至少为1元'));
        }
        if($num<1){
            $this->ajaxReturn(array('status'=>1,'msg'=>'红包个数至少为1元'));
        }
        if($min<1){
            $this->ajaxReturn(array('status'=>1,'msg'=>'最小红包至少为1元'));
        }
        if (!RedpackModel::checkAssign($prizeMoney,$num,$min)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'总红包金额不够分配到全部红包中'));
        }

        $uid=session('uid')+0;
        $gid=GameModel::getGameInstance()->add(array(
            'uid'=>$uid,
            'addTime'=>time(),
            'totalMoney'=>$prizeMoney,
            'packNum'=>$num,
            'packMin'=>$min,
            'isBet'=>$isBet
        ));
        if($gid===false){
            $this->ajaxReturn(array('status'=>1,'msg'=>'服务器出现错误，请过段时间再试'));
        }else{
            $this->ajaxReturn(array('status'=>0,'gid'=>$gid));
        }


    }


    /**
     * 支付赌注订单
     */
    public function wagerOrder(){

        $gid=I('post.gid',0,'intval');//游戏编号

        $uid=session('uid')+0;

        $wid=GameModel::getWagerInstance()->where("gid={$gid} and uid={$uid} and hasPay=0")->getField('wid');
        if(!$wid) {
            $wid = GameModel::getWagerInstance()->add(array(
                'gid' => $gid,
                'uid' => $uid,
                'addTime' => time(),
                'hasPay' => 0
            ));
        }
        if($wid===false){
            $this->ajaxReturn(array('status'=>1,'msg'=>'服务器出现错误，请过段时间再试'));
        }else{
            $this->ajaxReturn(array('status'=>0,'wid'=>$wid));
        }
    }

    /**
     * 获取游戏基本信息
     */
    public function getGame(){
        $gid=I('post.id',0,'intval');
        $game=GameModel::getGameDetail($gid);
        $this->ajaxReturn($game);
        //$this->ajaxReturn(array('status'=>1,'msg'=>"为啥错了，你还不知道吗"));

    }


    /**
     * 星探排行
     */
    public function starRank(){
        $page=I('post.p',1,'intval');
        $pageSize=10;
        $uid=session('uid');

        $offset=($page-1)*$pageSize;
        $list=UserModel::getUserInstance()->where('uid >'.self::USER_STAR_DIVIDE)->order('historyScore desc,historyTime asc')->limit($offset,$pageSize)->field('nickname,headPic,historyScore')->select();


    }
}