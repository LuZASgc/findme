<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/28
 * Time: 10:14
 */

namespace Admin;
use Common\Model\UtilsModel;
use Think\Controller;
use Org\Util\Rbac;
class BaseController extends Controller {

    protected $smarty=null;
    public function _empty(){
        $this->error('无效的操作');
    }

    
/**
* 作用：权限判断

* 传参：
* 返回值：
* 作者: shijy@soe-soe.com
* 日期: 2015/10/28
* 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
*/
    function _initialize(){
            $this->module_name = MODULE_NAME;
            //判断是否开始RBAC认证 或者当前模块设置了不需要认证
            if( C('USER_AUTH_ON') && !in_array( MODULE_NAME,explode(",",C('NOT_AUTH_MODULE')))){
                //判断不通过认证
                if(!RBAC::AccessDecision()){
                    if(!session(C('USER_AUTH_KEY'))){
                        session(C('USER_AUTH_KEY'),empty($_COOKIE[C('USER_AUTH_KEY')]) ? NULL : $_COOKIE[C('USER_AUTH_KEY')]);
                        session('username',empty($_COOKIE['username']) ? NULL : $_COOKIE['username']);
                        session('real_name',empty($_COOKIE['real_name']) ? NULL : $_COOKIE['real_name']);
                        session(C('ADMIN_AUTH_KEY'),empty($_COOKIE[C('ADMIN_AUTH_KEY')]) ? NULL : $_COOKIE[C('ADMIN_AUTH_KEY')]);
                        //session('_ACCESS_LIST',empty($_COOKIE['_ACCESS_LIST']) ? NULL : $_COOKIE['_ACCESS_LIST']);
                    }
                    //如果session没有值，则不通过
                    if(!session(C('USER_AUTH_KEY'))){
                        //$this->jumpUrl=C("USER_AUTH_GETWAY");

                        $this->error("未登陆",U("Admin/Public/login"));
                    }

                    $this->error("未授权".MODULE_NAME."_".CONTROLLER_NAME."_".ACTION_NAME);
                }
            }

            $this->smarty=getSmarty();
            $this->smarty->assign('EDITOR_UPLOAD_FILE_PATH',C('EDITOR_UPLOAD_FILE_PATH'));
            $this->smarty->assign('WEBSITE',BASE_DOMAIN);

    }



    /**
     * 添加日志
     * Author: shijy
     * @param $act 操作
     * @param $extra 具体操作数据
     */
    public function log($extra='',$act=''){
        $opt    = CONTROLLER_NAME."_".ACTION_NAME.'_'.$act;
        $user   = session('username');
        $adminLogM = M('gm_opt_log',null,DB_GM_CFG);
        $data=array(
            'user'      =>$user,
            'opt'       =>$opt,
            'extra'     =>$extra,
            'ip'        =>get_client_ip(),
            'addTime'   =>time()
        );
        $adminLogM->add($data);
    }

}