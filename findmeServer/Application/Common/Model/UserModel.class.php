<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


class UserModel extends BaseModel{
    const USER_STAR_DIVIDE=10000;

    const SEX_MALE   = 1; //性别男
    const SEX_FEMALE = 0; //性别女

    public static  function getSex(){
        return array(
            self::SEX_FEMALE=>'女',
            self::SEX_MALE=>'男',
        );
    }


    const RACE_YELLOW   =1;//
    const RACE_WHITE    =2;//
    const RACE_BLACK    =3;//
    public static  function getRace(){
        return array(
            self::RACE_YELLOW=>'黄种人',
            self::RACE_WHITE=>'白种人',
            self::RACE_BLACK=>'黑色人种',
        );
    }



    const USER_TYPE_ALL=0;
    const USER_TYPE_STAR=1;
    const USER_TYPE_NORMAL=2;

    public static function getUserType(){
        return array(
            self::USER_TYPE_ALL=>'全部',
            self::USER_TYPE_STAR=>'明星',
            self::USER_TYPE_NORMAL=>'素人',
        );
    }


    const AUDIT_ALL =0;
    const AUDIT_DEFAULT =3;
    const AUDIT_PASS    =1;
    const AUDIT_UNPASS  =2;
    public static function getAuditStatus(){
        return array(
            self::AUDIT_ALL=>'全部',
            self::AUDIT_DEFAULT=>'未审',
            self::AUDIT_PASS=>'通过',
            self::AUDIT_UNPASS=>'不通过',
        );
    }

    /**
     * 是否明星判断
     * @param $uid
     * @return bool
     */
    public static function isStar($uid){
        return $uid<=self::USER_STAR_DIVIDE;
    }


    private static $userInstance=null;
    public static function getUserInstance(){
        if(!self::$userInstance){
            self::$userInstance=M('u_user',null,DB_MAIN_CFG);
        }
        return self::$userInstance;
    }

    public static function getMaxStarId(){
        return self::getUserInstance()->where('uid<='.self::USER_STAR_DIVIDE)->max('uid');
    }

    public static function getMaxUserId(){
        return min(self::USER_STAR_DIVIDE + 1, self::getUserInstance()->where('uid >'.self::USER_STAR_DIVIDE)->max('uid'));
    }

    /**
     * 金额转移
     * @param $money
     * @param $toUid
     * @param $opt
     * @param $fromUid
     */
    public static function addMoney($money,$toUid,$opt,$fromUid=0){
        self::getUserInstance()->where("uid={$toUid}")->setInc('depositMoney',$money);
        $moneyYuan=$money/100;
        $otherNickname=$fromUid>0?self::getNickname($fromUid):'';

        switch ($opt){
            case GameModel::OPT_BET_RETURN:
                $msg="对垒'{$otherNickname}'获胜，退回金额：{$moneyYuan}元";
                break;
            case GameModel::OPT_BET_WIN:
                $msg="对垒'{$otherNickname}'获胜，赚得奖励：{$moneyYuan}元";
                break;
            case GameModel::OPT_BET_LOSE:
                $msg="'{$otherNickname}'没找对，输给您：{$moneyYuan}元";
                break;
            case GameModel::OPT_PRIZE:
                $msg="找对'{$otherNickname}'，赚得奖励：{$moneyYuan}元";
                break;
            case GameModel::OPT_OVERTIME:
                $msg="游戏超过时效，退回：{$moneyYuan}元";
                break;
        }
        GameModel::addLog($toUid,$msg);
    }

    public static function getNickname($uid){
        return self::getUserInstance()->where("uid={$uid}")->getField('nickname');
    }

    public static function login($openId,$nickname,$headPic,$sex){
        $uinfo=self::getUserInstance()->where("openid='{$openId}'")->find();
        if($uinfo){
            return $uinfo;
        }else{
            $uid=self::getUserInstance()->add(array(
                'nickname'=>$nickname,
                'openId'=>$openId,
                'headPic'=>$headPic,
                'sex'=>$sex,
                'addTime'=>time()
            ));
            $uinfo=self::getUserInstance()->where("openid='{$openId}'")->find();
            return $uinfo;
        }
    }

    /**
     * 更新用户图集
     * @param $uid
     * @param $albumStr
     * @return bool
     */
    public static function updateAlbum($uid,$albumStr){
        return self::getUserInstance()->where('uid='.$uid)->setField('album',$albumStr);
    }
}