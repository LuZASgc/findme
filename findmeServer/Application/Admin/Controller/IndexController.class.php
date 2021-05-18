<?php
namespace Admin\Controller;
use Admin\BaseController;
use Admin\Model\ThirdModel;

class IndexController extends BaseController {

    public function index(){

            $this->index_selfOrHalf();

    }

    /**
     * 自主/半自主权限验证
     * Author: shijy
     */
    private function index_selfOrHalf(){
        $uid = session('auth_id');
        if(!$uid){
            $this->error("请登陆系统");
        }
        /*获得组信息*/
        $sysUser = M("gm_sys_user",null,DB_GM_CFG);
        $sysNode = M("gm_sys_node",null,DB_GM_CFG);
        $result	= $sysUser
            ->field('role_id')
            ->where('id=' . $uid)
            ->find();
        $userRoleId = intval($result['role_id']);

        // $menu_secondary_item 获取二级菜单栏
        $menu_secondary_item = getSecondaryMenu();

        // $menu_third_item 获取三级菜单栏
        $menu_third_item = getThirdMenu($userRoleId);

        $menu = array();
        foreach($menu_secondary_item as $key=>$value){
            $menu_item = array(
                'title'   => $value['title'],
                'class'   => $value['classname'],
                'items'   => array(),
            );

            //如果三级菜单栏的pid 等于 二级菜单栏的id 则添加到 menu的items内
            foreach($menu_third_item as $k=>$v){
//                if($value['id'] == $v['pid'] && $v['is_menu']) {
//
//                    $path = getAllPathMenu($v['path']);
//                    $item = array($v['title'], U("{$path}"));
//                    array_push($menu_item['items'], $item);
//                }
                if($value['group'] == $v['group'] && $v['is_menu']){
                    $path = getAllPathMenu($v['path']);
                    $item = array($v['title'], U("{$path}"));
                    array_push($menu_item['items'], $item);
                }
            }
            array_push($menu, $menu_item);
        }





        $smarty=getSmarty();
        $smarty->assign('menus',$menu);
        $smarty->assign('user', session("real_name"));
        if (CURRENT_PERMISSION == PERMISSION_HALF){
            $smarty->template_dir = './Tpl/Manage/';
        }
        $smarty->display('index.html');
    }


}