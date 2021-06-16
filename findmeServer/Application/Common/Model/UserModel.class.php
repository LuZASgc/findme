<?php
/**
 * 作用：公用模块，用于放置零碎，不能组成模块的功能函数及常量
 * 作者: shijy@soe-soe.com
 * 日期: 2016/5/11 16:09
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */


namespace Common\Model;


class UserModel extends BaseModel{
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


//素人
    private static $userInstance=null;
    public static function getUserInstance(){
        if(!self::$userInstance){
            self::$userInstance=M('u_user',null,DB_MAIN_CFG);
        }
        return self::$userInstance;
    }


//明星
    private static $starInstance=null;
    public static function getStarInstance(){
        if(!self::$starInstance){
            self::$starInstance=M('s_star',null,DB_MAIN_CFG);
        }
        return self::$starInstance;
    }

    public static function getMaxStarId(){
        return self::getStarInstance()->max('uid');
    }


    public static function getUserDetail($uid){
        return self::getUserInstance()->where('uid='.$uid)->find();
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
            $uinfo['rank']=self::getUserInstance()->where("historyScore>={$uinfo['historyScore']} and uid!={$uinfo['uid']}")->count()+1;
        }else{
            $uid=self::getUserInstance()->add(array(
                'nickname'=>$nickname,
                'openId'=>$openId,
                'headPic'=>$headPic,
                'sex'=>$sex,
                'addTime'=>time()
            ));
            $uinfo=self::getUserInstance()->where("uid='{$uid}'")->find();
            $uinfo['rank']='999+';
        }
        session('uid',$uinfo['uid']);
        return $uinfo;
    }

    /**
     * 更新用户图集
     * @param $uid
     * @param $albumStr
     * @return bool
     */
    public static function updateAlbum($uid,$albumStr){
        $pics=explode(',',$albumStr);
        $audit=PHOTO_AUDIT_PASS;
        foreach($pics as $pic){
            $rt=PornModel::checkPic($pic);
            if($rt==PHOTO_AUDIT_UNPASS){
                return array('status'=>1,'msg'=>'包含色情图片请调整');
                break;
            }elseif($rt==PHOTO_AUDIT_REVIEW){
                $audit  = PHOTO_AUDIT_REVIEW;
                break;
            }elseif($rt==PHOTO_AUDIT_PASS){
            }
        }
        self::getUserInstance()->where('uid='.$uid)->save(array('album'=>$albumStr,'audit'=>$audit));
        if ($audit==PHOTO_AUDIT_PASS){
            return array('status'=>0,'msg'=>'图集已保存');
        }else{
            return array('status'=>0,'msg'=>'图集已保存，系统发现其中可能含有不适当，需要进行人工审核，当然您也可以换张图片再试试');
        }

    }
}