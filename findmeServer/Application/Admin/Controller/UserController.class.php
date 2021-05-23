<?php
/**
 * 商户管理
 * User: hls
 * Date: 2016/3/21
 * Time: 10:10
 */

namespace Admin\Controller;
use Admin\BaseController;
use Common\Model\BusinessModel;
use Common\Model\UserModel;
use Common\Model\UtilsModel;
use Think\Controller;
use Think\Db;
use Think\Page;

class UserController extends BaseController
{

    /**
     * storeList 门店列表
     */
    public function UserList(){

       
        $where  = '1';
        $userType=I('post.userType',0,'intval');
        switch ($userType){
            case UserModel::USER_TYPE_ALL:
                break;
            case UserModel::USER_TYPE_NORMAL:
                $where .=' and uid>'.UserModel::USER_STAR_DIVIDE;
                break;
            case UserModel::USER_TYPE_STAR:
                $where .=' and uid<='.UserModel::USER_STAR_DIVIDE;
                break;
        }

        $auditStatus=I('post.auditStatus',0,'intval');
        if($auditStatus!=UserModel::AUDIT_ALL){
            $where .=' and status='.$auditStatus;
        }

        $userM  = M('u_user',null,DB_MAIN_CFG);
        $count = $userM->where($where)->count();
        $this->smarty->assign('totalDataCount',$count);


        $limit = I('get.limit',10,'int');	//每页显示条数
        $Page = new Page($count,$limit);

        //分页
        fetchPage($this->smarty, $Page);

        $list =$userM
            ->where($where)
            ->order('uid desc')
            ->limit($Page->firstRow , $Page->listRows)
            ->select();



        $this->smarty->assign('list', $list);

        $sex=UserModel::getSex();
        $race=UserModel::getRace();
        $userTypeKV=UserModel::getUserType();

        $auditStatusKV=UserModel::getAuditStatus();

        $this->smarty->assign('auditType', $auditStatusKV);
        $this->smarty->assign('selectedAuditType', $auditStatus);
        $this->smarty->assign('userType', $userTypeKV);
        $this->smarty->assign('selectedUserType', $userType);
        $this->smarty->assign('sex',  $sex);
        $this->smarty->assign('race', $race);
        $this->smarty->assign('limit',$limit);

        $this->smarty->display('User/user_list.html');
    }
    /**
     * storeAdd 明星增加
     */
    public function UserAdd(){

        if(IS_POST){
            $nickname=I('post.nickname');
            $restaurantItem = M('u_user',null,DB_MAIN_CFG)->where("nickname like '{$nickname}'")->find();
            if($restaurantItem){
                $this->error('同名明星已经存在');
            }

            $album=I('post.album');
            if(count($album)<1){
                $this->error('明星图集至少需要一张图片');
            }

            $userM = M('u_user',null,DB_MAIN_CFG);
            $rules = array(
                array('nickname', 'require', '门店名称必须填写'),
                array('sex', 'require', '门店名称必须填写'),
                array('race', 'require', '门店地址必须填写'),
                array('headPic', 'require','头像必须上传'),
            );

            if($data = $userM->validate($rules)->create()){

                $data['album']=implode(',',$album);
                $data['uid']=UserModel::getMaxStarId()+1;

                 $userMId=$userM->add($data);
                 $this->success('添加成功');
            }else{
                $this->error($userM->getError());
            }
        }
        else{
            $this->smarty->assign('sex',UserModel::getSex());
            $this->smarty->assign('race',UserModel::getRace());

            $this->smarty->display('User/user_edit.html');
        }
    }
    /**
     * storeEdit 明星编辑
     */
    public function UserEdit(){
        if(IS_POST){
            $uid=I('post.uid',0,'intval');
            $nickname=I('post.nickname');
            $restaurantItem = M('u_user',null,DB_MAIN_CFG)->where("nickname like '{$nickname}' and uid<>{$uid}")->find();
            if($restaurantItem){
                $this->error('同名明星已经存在');
            }

            $album=I('post.album');
            if(count($album)<1){
                $this->error('明星图集至少需要一张图片');
            }

            $userM = M('u_user',null,DB_MAIN_CFG);
            $rules = array(
                array('nickname', 'require', '门店名称必须填写'),
                array('sex', 'require', '门店名称必须填写'),
                array('race', 'require', '门店地址必须填写'),
                array('headPic', 'require','头像必须上传'),
            );

            if($data = $userM->validate($rules)->create()){

                $data['album']=implode(',',$album);
                $userMId=$userM->where("uid={$uid}")->save($data);
                $this->success('修改成功');
            }else{
                $this->error($userM->getError());
            }
        }
        else{
            $uid=I('get.uid',0,'intval');
            $uinfo=M('u_user',null,DB_MAIN_CFG)->find($uid);

            $this->smarty->assign('sex',UserModel::getSex());
            $this->smarty->assign('race',UserModel::getRace());

            $this->smarty->assign('obj',$uinfo);

            $this->smarty->display('User/user_edit.html');
        }
    }
    /**
     * storeUpdate 门店更改启用状态
     */
    public function UserUpdate(){
        $uid = I('get.uid', null, 'intval');
        $userM = M('u_user',null,DB_MAIN_CFG);
        $item = $userM->find($uid);
        if(empty($item)){
            $this->error('没有存在该用户');
        }
        if($item['status']!=UserModel::AUDIT_PASS) {
            $result = $userM->where('uid=' . $uid)->setField('status', UserModel::AUDIT_PASS);
        }else{
            $result = $userM->where('uid=' . $uid)->setField('status', UserModel::AUDIT_UNPASS);
        }
        if ($result) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }


}
