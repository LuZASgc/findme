<?php
namespace Admin\Controller;
use Comon\Common;
use Think\Controller;
use Think\Page;
use Admin\BaseController;
class SysUserController extends BaseController{

	/*
	 * function index 管理员列表
	 */
	public function index(){
		$sysUser = M("gm_sys_user",null,DB_GM_CFG);
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);


		import("ORG.Util.Page");
		$count = $sysUser->count();
        $options['status'] = 1;
		$Page = new Page($count,10);
		fetchPage($this->smarty,$Page);
		//$show = $Page->show(); // 分页显示输出
		$list = $sysUser->limit($Page->firstRow.','.$Page->listRows)
						  ->where($options)
			              ->field('role_id,username,gm_sys_user.id,real_name,status,uid,nickname')
						  ->select();
		$this->smarty->assign('list',$list);

		$roleList=$sysRole->select();
		$roleKV=array();
		foreach($roleList as $role){
			$roleKV[$role['id']]=$role['remark'];
		}

		$this->smarty->assign('rolelist',$roleKV);
		$this->smarty->display('SysUser/index.html');

	}
	/*
	 * function add 管理员添加页面
	 */
	public function add(){
		import("@.Action.Home.SysRoleAction");
		$sysRoleAction = A("SysRole");
		$roleList = $sysRoleAction->getRoleList();
		$this->smarty->assign('role_list',$roleList);
		$this->smarty->display('SysUser/add.html');


	}

	public function insert(){
		$sysUser = M("SysUser");
		//$sysRole = M('SysRoleUser');
		$password = I('post.password', null ,'md5');
		$role_id = I('post.role_id', null, 'intval');
		$username = I('post.username', null ,'htmlspecialchars');
		$email = I('post.email', null , 'email');
		$phone = I('post.phone', null , 'intval');

		/*在创建管理员前对表单字段进行判断*/

		//判断登陆账号是否存在
		if(is_exist_filed('username', $username, 'SysUser')){
			$this->error('账号已存在');
		}
		
		//判断邮箱的格式是否正确
		if(!(filedPreMatch('email', $email))) {
			//$this->error('邮箱格式不正确');
		}

		//判断手机的格式是否正确
		if(!(filedPreMatch('phone', $phone))) {
			//$this->error('手机格式不正确');
		}

		if($vo = $sysUser->create()) {
			$vo['password']=$password;
			$b=$sysUser->add($vo);
			if($b){
				//$sysRole->add(array('role_id'=>$role_id,'user_id'=>$b));
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}else{
			$this->error($sysUser->getError());
		}
	}

	public function edit(){
		$sysUser = M("SysUser");
		$id = I('get.id',0,'int');// $_GET['id'];
		$username = I('get.username', null, 'htmlspecialchars');
		if($id <=0 ){
			$user = $sysUser->where("username = '{session('username')}'")->find();
			$id = $user['id'];
			$this->smarty->assign('user',1);
		}else {

			//根据用户ID获得角色，供前台显示
			//$sysRoleUser = M("SysRoleUser");
			//$roleUserResult = $sysRoleUser->where("user_id='". $id ."'")->find();
			$sysUser = M("SysUser");
			$result	= $sysUser->find($id);
			//$result["role_id"] = $roleUserResult["role_id"];
			$this->smarty->assign('obj',$result);

			//import("@.Action.Home.SysRoleController");
			$sysRoleM = M("SysRole");
			$this->smarty->assign('role_list',$sysRoleM->select());
		}
		$this->smarty->display('SysUser/edit.html');

	}

	public function update(){
		$sysUser = M("SysUser");
		$email = I('post.email', null, 'email');
		$phone = I('post.phone', null);
		$id = I('post.id', null, 'intval');
		$role_id = I('post.role_id', null, 'intval');
		$username = I('post.username', null);

		//获取更新前的用户的信息
		$prev_sysUser = $sysUser->where('id=' . $id)->find();

		//判断修改后的登陆账号是否存在
		if($username != $prev_sysUser['username']){
			if(is_exist_filed('username', $username, 'SysUser')){
				$this->error('账号已存在');
			}
		}
		//判断邮箱的格式是否正确
		if(!(filedPreMatch('email', $email))) {
			//$this->error('邮箱格式不正确');
		}

		//判断手机的格式是否正确
		if(!(filedPreMatch('phone', $phone))) {
			//$this->error('手机格式不正确');
		}


		if($data=$sysUser->create()) {
			if(!empty($_POST["password"])){
				$data["password"] = md5($_POST["password"]);
			}else{
				unset($data["password"]);
			}
			$b=$sysUser->save($data);

			if($b!==false){
				$this->success('数据更新成功');
			}else{
				$this->error('数据更新失败');
			}
		}else{
			$this->error($sysUser->getError());
		}

	}

	public function delete(){
		$id = I('get.id', null, 'intval');
		//获取id 
		if(!empty($id)) {
			$sysUser = M("SysUser");
			$result	= $sysUser->delete($_GET['id']);
			if(false !== $result) {
				$this->success('删除管理员用户成功');
			}else{
				$this->error('删除管理员用户失败');
			}
		}else{
			$this->error('数据不存在');
		}

	}


	/*
	 * 批量操作数据
	 * 根据post过来的operate参数进行执行
	 * 启用 open
	 * 禁用 close
	 * 删除 delete
	 */
	public function batchOperate(){
		$sysUser = M("SysUser");
		$o = $_POST["operate"];
		$delid_str = implode(",",$_POST["delid"]);

		if($o=="open"){

			$data["status"] = 1;
			$b = $sysUser->where("id in ($delid_str)")->data($data)->save();

			if($b){
				$this->success(L("DataUpdateSuccess"));
			}else{
				$this->error(L("DataUpdateFaild"));
			}

		}
		if($o=="close"){

			$data["status"] = 0;
			$b = $sysUser->where("id in ($delid_str)")->data($data)->save();

			if($b){
				$this->success(L("DataUpdateSuccess"));
			}else{
				$this->error(L("DataUpdateFaild"));
			}

		}

		if($o=="delete"){

			$b = $sysUser->where("id in ($delid_str)")->delete();
			if($b){
				$this->success(L("DataDeleteSuccess"));
			}else{
				$this->error(L("DataDeleteFaild"));
			}
		}


	}

	/*
	* 搜索
	*/
	public function search(){
		$sysUser = M("SysUser");
		$id = I('get.id',0,'int');
		$status = I('get.status',null,'int');
		$keywords = I('get.keywords',null);
		$where = " 1 = 1 ";
		if(!empty($id)){
			$where .= " and id = $id ";
		}

		if(!is_null($status)){
			$where .= " and status = '$status' ";
		}

		if(!empty($keywords)){
			$where .= " and real_name like '%$keywords%' ";
		}
		import("ORG.Util.Page");
		$count = $sysUser->where($where)->count();
		$this->smarty->assign('totalDataCount',$count);
		$limit = I('get.limit',10,'int');
		$Page = new Page($count,$limit);
		fetchPage($this->smarty,$Page);
		$Page->parameter = "id=$id&status=$status&keywords=$keywords";
		$show = $Page->show(); // 分页显示输出
		$list = $sysUser->table("gm_sys_user")
						  ->where($where)
						  ->limit($Page->firstRow.','.$Page->listRows)
						  ->select();
		$this->smarty->assign('list',$list);

		$roleList=M("gm_sys_role",null,DB_GM_CFG)->select();
		$roleKV=array();
		foreach($roleList as $role){
			$roleKV[$role['id']]=$role['remark'];
		}

		$this->smarty->assign('rolelist',$roleKV);

		$this->smarty->assign('limit',$limit);
		$this->smarty->assign('keywords',$keywords);
		$this->smarty->assign('status',$status);
		$this->smarty->display("SysUser/index.html");
	}

	/*
	*	替换显示函数
	*	为了更友好的用户体验，替换一些数据为中文表示方法,比如status=1的数据，页面显示"启用"
	*/
	function replaceShowText($list){

		$i = 0;
		foreach($list as $l){
			if($l["status"]==1){
				$list[$i]["status"] = L("Open");
			}else{
				$list[$i]["status"] = "<font color='#FF0000'>".L("Close")."</font>";
			}
			$i++;
		}
		return $list;
	}

}
?>