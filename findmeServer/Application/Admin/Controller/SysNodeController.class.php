<?php
/**
 * 作用：
 * 作者: shijy@soe-soe.com
 * 日期: 2015/10/26
 * 公司: 浙江宣逸网络科技有限公司<www.soe-soe.com>
 */
namespace Admin\Controller;
use Admin\Model\ThirdModel;
use Think\Page;
use Admin\BaseController;
class SysNodeController extends BaseController{
	/**
	 * function index 权限管理列表
	 */

	public function index(){
		$sysNode = M("gm_sys_node",null,DB_GM_CFG);
		import("ORG.Util.Page");
		$count = $sysNode->count();
		$Page = new Page($count,10);
		fetchPage($this->smarty,$Page);
		$show = $Page->show(); // 分页显示输出
		$list = $sysNode->table("gm_sys_node m")
			->field("m.*,(select title from gm_sys_node where id= m.pid) as parent")
			->limit($Page->firstRow.','.$Page->listRows)
			->order('createdate desc')
			->select();


		$this->smarty->assign('list',$this->replaceShowText($list));


		if(defined('POWER_CENTER_PUSH') && POWER_CENTER_PUSH){
			$powerPush=true;
		}else{
			$powerPush=false;
		}
		$this->smarty->assign('limit',$limit);
		$this->smarty->assign('powerPush',$powerPush);
		$this->smarty->display('SysNode/index.html');
	}

	/**
	 * function add 权限管理添加页面
	 */

	public function add(){
		$this->smarty->assign('node_list',$this->get_node_list());
		$this->smarty->display('SysNode/add.html');
	}


	/**
	 * 权限管理添加功能
	 * Author: shijy
	 */
	public function insert(){
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$level=0;

		$pid1  = I("post.pid",0,'int');
		$pid2 = I("post.pid2",0,'int');
		if($pid1<=0){
			$this->error('请指定上级权限分组');
		}
		if($_POST["pid2"]<=0){
			$level	= 2;
			$pid=$pid1;
		}else{
			$level	= 3;
			$pid=$pid2;
		}
		$title=I('post.title','');
		$name =I('post.name','');
		if(strlen($title)<1 || strlen($name)<1){
			$this->error('请指定标题及名称');
		}

		$sort =I('post.sort',0,'int');
		$status =I('post.status',0,'int');
		$is_menu =I('post.is_menu',0,'int');
		$classname =I('post.classname','');
		$group = I('post.group',0,'int');
		if($is_menu && !$group){
			$this->error('请选择菜单项显示区域');
		}


		$newData = array(
			'title'		=> $title,
			'name'		=> $name,
			'pid'		=> $pid,
			'level'		=> $level,
			'sort'		=> $sort ,
			'status'	=> $status ,
			'is_menu'	=> $is_menu ,
			'classname'	=> $classname ,
			'group'		=> $group,
		);
		$b = $sysNode->add($newData);

		if($b){
			$this->updateModulePath($b,$pid);
			//$this->jumpUrl = U('SysNode/add?pid='.$pid.'&pid2='.$pid2.'&pid3='.$pid3);
			$this->success('数据添加成功',U('SysNode/edit?id='.$b));
		}else{
			$this->error("数据添加失败");
		}
	}

	/**
	 * function edit 权限管理编辑页面
	 */

	public function edit(){
		$id=I('get.id',0,'int');
		if($id>0) {
			$sysNode = M("gm_sys_node",null,DB_GM_CFG);
			$result	= $sysNode->find($id);
			$this->smarty->assign('obj',$result);
		}
		$this->smarty->display('SysNode/edit.html');

	}

	/**
	 * function update 权限管理编辑功能
	 */

	public function update(){
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$pid1 	= I('post.pid',0,'int');// $_POST["pid"]; 1级
		$pid2 	= I('post.pid2',0,'int');//$_POST["pid2"];2级
		$pid3 	= I('post.pid3',0,'int');//$_POST["pid3"];3级
		$id = I('post.id', null, 'intval'); //当前权限项

		$level=0;
		if($pid1<=0){
			$this->error('请指定上级权限分组');
		}

		$level = $sysNode->where("id={$id}")->getField('level');
		switch ($level){
			case 2:
				if($pid2>0 && $pid2 <> $id){
					$this->error('当前是二级权限<br />不可进行降级处理！');
				}
				$pid=$pid1;
				$path=$pid1.'/'.$id.'/';
				break;
			case 3:
				$pid=$pid2;
				$path=$pid1.'/'.$pid2.'/'.$id.'/';
				break;
		}


		$title=I('post.title','');
		$name =I('post.name','');
		if(strlen($title)<1 || strlen($name)<1){
			$this->error('请指定标题及名称');
		}

		$sort =I('post.sort',0,'int');
		$status =I('post.status',0,'int');
		$is_menu =I('post.is_menu',0,'int');
		$classname =I('post.classname','');
		$group = I('post.group',0,'int');
		if($is_menu && !$group){
			$this->error('请选择菜单项显示区域');
		}


		$newData = array(
			'id'		=> $id,
			'title'		=> $title,
			'name'		=> $name,
			'pid'		=> $pid,
			'level'		=> $level,
			'sort'		=> $sort ,
			'status'	=> $status ,
			'is_menu'	=> $is_menu ,
			'classname'	=> $classname ,
			'path'		=> $path,
			'group'		=> $group,
		);
		 if($sysNode->save($newData)===false){
			 $this->error("数据更新失败");
		 }else{
			 $this->success('数据更新成功',U('SysNode/index'));
		 }
	}


	/**
	 * function delete 权限管理删除
	 */

	public function delete(){
		
		if(!empty($_GET['id'])) {
			$sysNode = M("sys_node",'',DB_GM_CFG);
			$result	= $sysNode->delete($_GET['id']);
			if(false !== $result) {
				$this->success('数据删除成功');
			}else{
				$this->error('数据删除失败');
			}
		}else{
			$this->error('数据不存在');
		}
		
	}

	public function get_node_list($pid=0){
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$result = $sysNode->where("pid=$pid")->order("sort asc")->select();
		return $result;
	}

	/**
	 * function getSonCategory ajax接口 返回level=1的目录
	 */

	public function getSonCategory(){
		
		$pid = (int)$_GET["pid"];
		//Log::write($pid);
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$result = $sysNode->where("pid=$pid and status=1 and level=1")->select();
		$this->ajaxReturn($result);
	}
	
	//根据id获得level数据
	public function updatePostLevel($pid,$id){
		$sysNode = M("sys_node",'',DB_GM_CFG);
		if($pid!=$id){
			$rs = $sysNode->field("level")->where("id=".$pid)->find();
			$_POST["level"] = $rs["level"]+1;
		}else{
			$rs = $sysNode->field("level")->where("id=".$pid)->find();
			$_POST["level"] = $rs["level"];
			unset($_POST['pid']);
		}
		
	}
	
	/*
	*	根据模块ID更新模块路径(path)
	*/
	public function updateModulePath($id,$pid){
		$sysNode = M("sys_node",'',DB_GM_CFG);
		if($id){
			if($pid==0){
				$data["path"] = $id."/";
			}else{
				$rs = $sysNode->field("path")->where("id=".$pid)->find();
				$data["path"] = $rs["path"].$id."/";
			}
			$sysNode->data($data)->where("id=".$id)->save();
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
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$o = $_POST["operate"];
		$delid_str = implode(",",$_POST["delid"]);
		
		if($o=="open"){
			
			$data["status"] = 1;
			$b = $sysNode->where("id in ($delid_str)")->data($data)->save();
			
			if($b){
				$this->success(L("DataUpdateSuccess"));
			}else{
				$this->error(L("DataUpdateFaild"));
			}
			
		}
		if($o=="close"){
			
			$data["status"] = 0;
			$b = $sysNode->where("id in ($delid_str)")->data($data)->save();
			
			if($b){
				$this->success(L("DataUpdateSuccess"));
			}else{
				$this->error(L("DataUpdateFaild"));
			}
			
		}
		
		if($o=="delete"){
			
			$b = $sysNode->where("id in ($delid_str)")->delete();
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
		$sysNode = M("sys_node",'',DB_GM_CFG);
		$id = I('get.id', null, 'intval');
		$status = I('get.status', null);
		$keywords = I('get.keywords', null);

		$where = " 1 = 1 ";
		if(!empty($id)){
			$where .= " and id = $id ";
		}
		
		if(!empty($status)){
			$where .= " and status = '$status' ";
		}
		
		if(!empty($keywords)){
			$where .= " and title like '%$keywords%' ";
		}
		
		import("ORG.Util.Page");
		$count = $sysNode->where($where)->count();
		$this->smarty->assign('totalDataCount',$count);

		$limit = I('get.limit',10,'int');
		$Page = new Page($count,$limit);
		fetchPage($this->smarty,$Page);
		$Page->parameter = "id=$id&status=$status&keywords=$keywords";
		$show = $Page->show(); // 分页显示输出
		$list = $sysNode->table("gm_sys_node m")
						  ->field("m.*,(select title from gm_sys_node where id= m.pid) as parent")
						  ->where($where)
						  ->limit($Page->firstRow.','.$Page->listRows)
			              ->order('createdate desc')
						  ->select();
		$this->smarty->assign('list',$this->replaceShowText($list));

		$this->smarty->assign('limit',$limit);
		$this->smarty->assign('id',$id);
		$this->smarty->assign('keywords',$keywords);

		$this->smarty->display('SysNode/index.html');
	}
	
	/*
	*	替换显示函数
	*	为了更友好的用户体验，替换一些数据为中文表示方法,比如status=1的数据，页面显示"启用"
	*/
	function replaceShowText($list){
		
		$i = 0;
		foreach($list as $l){
			if($l["status"]==1){
				$list[$i]["status"] = "开启";
			}else{
				$list[$i]["status"] = "<font color='#FF0000'>关闭</font>";
			}
			$i++;
		}
		return $list;
	}
	
	/*根据父级ID获得权限节点列表*/
	public function getNodeListByPid(){
		$pid = I('get.pid',0,'int');
		$sysNodeAction = A("Admin/SysNode");
		$result = $sysNodeAction->get_node_list($pid);
		$this->ajaxReturn($result,'json',1);
	}

	/**
	 * 推送菜单权限项到中心库
	 * Author: shijy
	 */
	public function pushMenuToCenter(){
		//同步权限
		$this->ajaxReturn(ThirdModel::pushPowerToOther());
	}

}
