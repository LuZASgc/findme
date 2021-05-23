<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


class GameModel extends BaseModel{

    const BET_RESULT_UNKNOW = 0;//未结束
    const BET_RESULT_WIN    = 1;//成功
    const BET_RESULT_LOSE   = 2;//失败


    private static $gameInstance=null;
    public static function getGameInstance(){
        if(!self::$gameInstance){
            self::$gameInstance=M('g_game',null,DB_MAIN_CFG);
        }
        return self::$gameInstance;
    }

    private static $wagerInstance=null;
    public static function getWagerInstance(){
        if(!self::$wagerInstance){
            self::$wagerInstance=M('w_wager',null,DB_MAIN_CFG);
        }
        return self::$wagerInstance;
    }

    private static $joinInstance=null;
    public static function getJoinInstance(){
        if(!self::$joinInstance){
            self::$joinInstance=M('j_join',null,DB_MAIN_CFG);
        }
        return self::$joinInstance;
    }

    private static $prizeInstance=null;
    public static function getPrizeInstance(){
        if(!self::$prizeInstance){
            self::$prizeInstance=M('redpack_record',null,DB_MAIN_CFG);
        }
        return self::$prizeInstance;
    }


    const PRIZE_RECORD_STATUS_UNSEND=0;
    const PRIZE_RECORD_STATUS_SEND=1;


    /**
     * 根据配置获取未发奖项
     * @param $gid
     * @return mixed
     */
    public static function getPrizeRecordByGid($gid){
        $record=self::getPrizeInstance()
            ->where("setId={$gid} and status=".self::PRIZE_RECORD_STATUS_UNSEND)
            ->find();
        return $record;
    }

    /**
     * 设置发送状态
     * @param $prId
     * @param int $status
     */
    public static function setPrizeRecordStatus($prId,$status=self::PRIZE_RECORD_STATUS_SEND){
        self::getPrizeInstance()->where("id={$prId}")->setField('status',$status);
    }


    //行为类型
    const OPT_PRIZE         = 1;//答对奖励
    const OPT_BET_WIN       = 2;//对垒赢得
    const OPT_BET_RETURN    = 3;//答对后退回
    const OPT_OVERTIME      = 4;//超时退回
    const OPT_BET_LOSE      = 5;//对垒失败

    /**
     * 增加日志
     * @param $uid
     * @param $msg
     */
    public static function addLog($uid,$msg){
        M('l_log',null,DB_MAIN_CFG)->add(
            array('uid'=>$uid,'msg'=>$msg,'addTime'=>time())
        );
    }


    /**
     * 获取游戏
     * @param $gid
     * @return mixed
     */
    public static function getGameDetail($gid){
        $baseInfo = self::getGameInstance()->where('gid='.$gid)->find();
        if(!$baseInfo){
            return array('status'=>1,'msg'=>'指定的游戏不存在');
        }
        $userInfo = UserModel::getUserDetail($baseInfo['uid']);
        return array('status'=>0,'msg'=>'success','base'=>$baseInfo,'user'=>$userInfo);
    }
    
    

}