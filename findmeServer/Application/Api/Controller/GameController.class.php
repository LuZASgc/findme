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
        $this->show('星悦，公共支持','utf-8');
    }


    public function createGame(){
        $uid=I('post.uid',0,'intval');
        $totalMoney=I('post.totalMoney',0,'floatval');
        $packNum=I('post.packNum',0,'intval');
        $isBet=I('post.isBet',0,'intval');

        $newData=array(
            'uid'=>$uid,
            'totalMoney'=>$totalMoney,
            'packNum'=>$packNum,
            'isBet'=>$isBet
        );
        $this->ajaxReturn(GameModel::newGame($newData));
    }

    /**
     * 提交结果，
     */
    public function submitResult(){
        $gid=I('post.gid',0,'intval');//游戏编号
        $oid=I('post.oid',0,'intval');//对家uid
        $uid=I('post.uid',0,'intval');//自己uid
        $sid=I('post.sid',0,'intval');//选中uid
        $userInfo=UserModel::getUserInstance()->find($uid);


        if($oid == $sid){//正确
            $result=GameModel::BET_RESULT_WIN;
        }else{//错误
            $result=GameModel::BET_RESULT_LOSE;
        }

        $return=array('status'=>0,'msg'=>'success','result'=>$result,'prize'=>0,'return'=>0,'next'=>'');
        if(UserModel::isStar($oid)){//明星赛
            $update=array();
            if($result==GameModel::BET_RESULT_WIN){
                $userInfo['nowScore']+=1;
                $update['nowScore']=$userInfo['nowScore'];

                if($userInfo['historyScore']>$userInfo['nowScore']){
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
    
    
    
    public function initGame(){
        $maxStarId=UserModel::getMaxStarId();
        $array=range(1,$maxStarId);
        shuffle($array);
        session('starId',implode(',',$array));
    }

    /**
     * 取下一组明星
     * @param $nowScore
     * @return bool|mixed
     */
    private function getNextTeam($nowScore){
        $idStr=session('starId');
        if(!$idStr){
            $this->initGame();
            $idStr=session('starId');
        }
        $idArray=explode(',',$idStr);
        if(!array_key_exists($nowScore,$idArray)){
            return false;
        }
        $nextStarId=$idArray[$nowScore];
        $others=array_rand($idArray,6);
        foreach ($others as $k=>$id){
            if($id == $nextStarId){
                unset($others[$k]);
                break;
            }
        }
        array_unshift($others,$nextStarId);
        return $this->getPhotos($others);
    }


    private function getPhotos(&$idarray){
        $idstr=implode(',',$idarray);
        $list= UserModel::getUserInstance()->where("uid in ($idstr)")->field('id,headPic,album')->select();
        foreach($list as $k=>$v){
            $imgs=explode(',',$v['album']);
            $list[$k]['album']=array_rand($imgs,1);
            if( $k>0 ){
                unset($list[$k]['headPic']);
            }
        }
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
        $num=session("num");
        if(!$num){
            session("num",1);
        }else{
            session("num",$num+1);
        }

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
}