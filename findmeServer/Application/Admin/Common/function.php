<?php
/**
 *
 * Created by www.soe-soe.com
 * Author: shijy
 * Date: 2015/12/10
 * Time: 10:56
 */

function getSmarty(){
    static $smartyAdmin;
    if ($smartyAdmin) {
        return $smartyAdmin;
    }
    import('Vendor.Smarty.Smarty');
    $smartyAdmin = new Smarty();
    $smartyAdmin->template_dir = './Tpl/Admin/';
    $smartyAdmin->compile_dir = RUNTIME_PATH.'Smarty/';
    $smartyAdmin->addPluginsDir(WEB_ROOT.'/smarty_plugin');
    $smartyAdmin->caching = false;
    $smartyAdmin->left_delimiter='<{';
    $smartyAdmin->right_delimiter='}>';
    $smartyAdmin->assign('module',MODULE_NAME);
    $smartyAdmin->assign('controller',CONTROLLER_NAME);
    $smartyAdmin->assign('action',ACTION_NAME);

    $smartyAdmin->assign('preUploadUrl',C('UPLOAD_RESOURCE_PREFIX'));//
    $smartyAdmin->assign('preStaticUrl',C('STATIC_RESOURCE_PREFIX'));//

    $smartyAdmin->assign('resVersion',RESOURCE_VERSION);//静态资源版本控制

    return $smartyAdmin;
}
/**
 *根据输入的字段来判断是否存在
 *@parm filed 字段的名称
 *@parm filed_vale 字段的值
 *@parm table 数据库表的字
 */
function is_exist_filed($filed,$filed_value, $table) {
    $model = M($table);
    $data = array(
        $filed => $filed_value,
    );
    $result = $model->where($data)->select();
    if($result) {
        return true;
    }else{
        return false;
    }

}
/**
 * 获取所有二级菜单
 */
function getSecondaryMenu(){
    $item = array();
    $sysNode = M('SysNode');
    $item = $sysNode
        ->where('level=2 and is_menu=1 and pid=216')
        ->order('`sort` desc')
        ->select();
    return $item;
}



/**
 * 获取级所有三级菜单
 */
function getThirdMenu($role_id){
    $item = array();
    $sysNode = M('SysNode');
    $item = $sysNode
        ->join('gm_sys_access on gm_sys_access.node_id = gm_sys_node.id and gm_sys_access.role_id=' . $role_id)
         ->where('gm_sys_node.level=3 and gm_sys_node.path like \'216/%\' ')
        ->order('gm_sys_node.`sort` desc')
        ->select();
    return $item;
}




//获取所有菜单栏的路径
function getAllPathMenu($path){
    $sysNode = M("gm_sys_node",null,DB_GM_CFG);
    $path_array = explode('/',$path);
    //去掉第一个数组 为一级路径
    //array_shift($path_array);
    //去掉最后个数组 如果格式为/**/**/**/ 最后个数组为空
    array_pop($path_array);
    $path = array();
    foreach($path_array as $value) {
        $result = $sysNode->field('name')->find($value);
        $path[] = $result['name'];
    }
    //pre_dump($path);
    $path[0] = ucfirst($path[0]);
    $path = implode('/', $path);
    return $path;
}
/**
 * 根据用户输入的商户id  随机生成002~999工号
 */
function createRandomJobNumber($restaurantId){
    $verification = M('s_verification',null,DB_MAIN_CFG);
    //获取该商铺下所有的工号
    $jobNumList = $verification
        ->field('jobNum')
        ->where('restaurantId=' . $restaurantId )
        ->select();
    //生成1000个工号
    $rangeArray = range(2,999);
    foreach($rangeArray as $key => $value){
        $randomArray[] = array('jobNum'=> str_pad(strval($value),3,0,STR_PAD_LEFT));
    }
    //判断是否存在在商铺里存在该工号 返回不存在的工号
    //有待考虑 是否有更有效的查找方法
    foreach($randomArray as $key => $value){
        if(!in_array($value,$jobNumList)){
            $result [] = $value;
        }
    }
    //判断是否有差集，没有差集则不存在该工号
    if(empty($result)){
        $this->error('工号已创满');
    }
    //返回当前第一个数值
    $jobNum = each($result);
    //返回工号
    return $jobNum['value']['jobNum'];
}


/**
 *
 * Author: shijy
 * @param $powerName  CONTROLLER_NAME  /  ACTION_NAME
 * @return bool
 */
function checkPower($powerName)
{
    
    if (CURRENT_PERMISSION == PERMISSION_OUTSIDE){
        if (!\Admin\Model\ThirdModel::checkPower($powerName)) {
            return false;
        }
        return true;
    }else{
        $appName='Admin';
        list($controller,$action)=explode('/',$powerName);
        //登录验证模式，比较登录后保存的权限访问列表
        $accessList = session('_ACCESS_LIST');

        //判断是否为组件化模式，如果是，验证其全模块名
        if(!isset($accessList[strtoupper($appName)][strtoupper($controller)][strtoupper($action)])) {
            return false;
        }
        else {
            return true;
        }
    }
}


function fetchAjaxPager(&$smarty,$pager,$pageSize){
    $smarty->assign('limit',$pageSize);
    $smarty->assign('totalDataCount',$pager['total']);
    $smarty->assign('totalPage',$pager['totalPage']);
    $smarty->assign('nowPage',$pager['page']);
    if($pager['page']>1){
        $smarty->assign('prevPage',$pager['page']-1);
    }else{
        $smarty->assign('prevPage',$pager['page']);
    }

    if($pager['nextPage']){
        $smarty->assign('nextPage',$pager['page']+1);
    }else{
        $smarty->assign('nextPage',$pager['page']);
    }
}



