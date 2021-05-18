<?php
/**
 * 面向公共（未登陆）页面
 */
namespace Admin\Controller;
use Admin\BaseController;
use Org\Util\Rbac;

class PublicController extends BaseController {
    public function index(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }






    public function login(){
        $auth_id = session('auth_id');
        if(isset($auth_id)) {
            redirect(U('Index/index'));
        }else{
            $smarty = getSmarty();
            $smarty->assign('verifyUrl', u('Public/verify'));
            $smarty->assign('postUrl', u('Public/check_login'));
            $smarty->display('login.html');
        }
    }


    //检查登陆
    public function check_login(){

        $userName=I('post.userName','');
      if(strcmp($userName,'')==0){
           $this->error("请输入用户名");
        }

        $passWord=I('post.passWord','');
        if(strcmp($passWord,'')==0){
            $this->error("请输入密码");
        }



        $data = array();
        $data["username"] = $userName;
        $data["status"] = array('gt',0);

        $user = RBAC::authenticate($data);
        if($user){
            if($user["password"] == md5($passWord)) {

                $lifeTime = 24 * 3600;
                session_set_cookie_params($lifeTime);
                session_start();


                session(C('USER_AUTH_KEY'),$user["id"]);
                session("username",$user["username"]);
                session("real_name",$user["real_name"]);
                session('belong_store',$user['belong_store']+0);

                if($user["username"]==C("ADMIN_AUTH_KEY")) {
                    session(C("ADMIN_AUTH_KEY"),true);
                }

                //缓存RBAC权限信息
                RBAC::saveAccessList();
                $this->success("登陆成功");
            } else {
                $this->error("账号名或密码错误");
          }
        } else {
            $this->error("账号不存在或已被关闭");
        }

    }


    /*
*	注销，退出后台
*/
    public function logout(){
        session(C('USER_AUTH_KEY'),null);
        session("username",null);
        session("real_name",null);
        session(C("ADMIN_AUTH_KEY"),null);

        setcookie(C('USER_AUTH_KEY'), NULL, -1, "/", ".cp2.kannb.com");
        setcookie('username', NULL, -1, "/", ".cp2.kannb.com");
        setcookie('real_name', NULL, -1, "/", ".cp2.kannb.com");
        setcookie(C('ADMIN_AUTH_KEY'), NULL, -1, "/", ".cp2.kannb.com");
        setcookie('A_ACCESS_LIST', NULL, -1, "/", ".cp2.kannb.com");
        $this->redirect('login');
        //U('Public/login',NULL,true);
    }

    /*
*	验证码显示
*	参数：空
*	返回值：图片流
*/
    public function verify()
    {
        $Verify = new \Think\Verify();
        $Verify->fontSize = 12;
        $Verify->length   = 4;
        $Verify->useNoise = false;
        $Verify->useCurve=false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 80;
        $Verify->imageH = 50;

        //$Verify->useZh=true;
        //$Verify->expire = 600;
        $Verify->entry();
    }
}