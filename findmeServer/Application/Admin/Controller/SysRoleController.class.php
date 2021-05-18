<?php

namespace Admin\Controller;
use Comon\Common;
use Think\Controller;
use Think\Page;
use Admin\BaseController;
class SysRoleController extends BaseController{
	
	public function index(){
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);

		import("ORG.Util.Page");
		$count = $sysRole->count();
		$Page = new Page($count,10);
		fetchPage($this->smarty,$Page);

		$list = $sysRole->order("id ASC")->limit($Page->firstRow.','.$Page->listRows)
						->select();

		$this->smarty->assign('list',$list);
		$this->smarty->assign('limit',$limit);
		$this->smarty->display('SysRole/index.html');

	}

	/**
	 * function add 角色添加页面
	 */
	public function add(){
		$this->smarty->display('SysRole/add.html');
	}
	/**
	 * function insert 角色添加控制
	 */
	public function insert(){
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);

		if($vo = $sysRole->create()) {
			$b=$sysRole->add();
			if($b){
				$this->success('数据添加成功');
			}else{
				$this->error("数据添加失败");
			}
		}else{
			$this->error($sysRole->getError());
		}
		
	}
	/**
	 * function insert 角色编辑页面
	 */
	public function edit(){
		$id=I('get.id',0,'int');
		if($id>0) {
			$sysRole = M("gm_sys_role",null,DB_GM_CFG);
			$result	= $sysRole->find($id);
			$this->smarty->assign('obj', $result);
		}
		$this->smarty->display('SysRole/edit.html');
		
	}
	/**
	 * function insert 角色编辑控制
	 */
	public function update(){
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);
		if($vo = $sysRole->create()) {
			$b=$sysRole->save();
			if($b!==false){
				$this->success('数据修改成功');
			}else{
				$this->error('数据修改失败');
			}
		}else{
			$this->error($sysRole->getError());
		}
		
	}
	/**
	 * function insert 角色删除
	 */
	public function delete(){
		$id=I('get.id',0,'int');
		if($id>0) {
			$sysRole = M("SysRole");
			$result	= $sysRole->delete($id);
			if(false !== $result) {
				$this->success('数据删除成功');
			}else{
				$this->error('数据删除失败');
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
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);
		$o = I('post.operater');//$_POST["operate"];
		$delid_str = implode(",",$_POST["delid"]);
		
		if($o=="delete"){
			
			$b = $sysRole->where("id in ($delid_str)")->delete();
			if($b){
				$this->success('数据删除成功');
			}else{
				$this->error('数据删除失败');
			}
		}
		
		
	}
	
	/*
	* 角色搜索
	*/
	public function search(){
		$sysRole = M("gm_sys_role",null,DB_GM_CFG);
		$id = I('get.id',0,'int');//$_REQUEST["id"];
		$keywords =I('get.keywords','');// $_REQUEST["keywords"];
		$where = " 1 = 1 ";
		if($id>0){
			$where .= " and id = $id ";
		}
		
		if(''!=$keywords){
			$where .= " and name like '%$keywords%' ";
		}
		import("ORG.Util.Page");
		$count = $sysRole->where($where)->count();
		$this->smarty->assign('totalDataCount',$count);

		$limit = I('get.limit',10,'int');
		$Page = new Page($count,$limit);
		fetchPage($this->smarty,$Page);
		$Page->parameter = "id=$id&keywords=$keywords";
		$list = $sysRole->where($where)
						  ->limit($Page->firstRow.','.$Page->listRows)
						  ->select();
		$this->smarty->assign('list',$list);
		$this->smarty->assign('limit',$limit);
		$this->smarty->assign('keywords',$keywords);
		$this->smarty->assign('id',$id);

		$this->smarty->display("SysRole/index.html");
	}
	
	
	/*
	*	分配权限
	*/
	public function access(){
		$id=I('get.id',0,'int');
		if($id>0) {
			
			/*获得组信息*/
			$sysRole = M("gm_sys_role",null,DB_GM_CFG);
			$result	= $sysRole->find($id);
			$this->smarty->assign('role',$result);
			
			/*获得组对应的权限列表*/
			$sysAccess = M("gm_sys_access",null,DB_GM_CFG);
			$accessResult	= $sysAccess->where("role_id='{$id}'")->select();

			$this->smarty->assign('access',$accessResult);

			$sysNode = M("gm_sys_node",null,DB_GM_CFG);
			$mods=$sysNode->where("level=1")->order('id asc')->select();

///主管理模块
			//0 为 Admin
			$module_level_1 = $mods[0];

			/*这里获取所有模块和操作列表来分配权限用，因为level=1是项目，比如后台*/
			$module_level_2	= $sysNode->where("level=2 and path like '".$module_level_1["id"]."/%'")->order("sort asc")->select();
//			$module_level_2	= $sysNode->where("level=2 ")->order("sort asc")->select();
			$i=0;
			foreach($module_level_2 as $m){
				$result	= $sysNode->where("pid=$m[id]")->order("sort asc")->select();				
				if(!empty($result)){
					
					foreach($accessResult as $ar){
						if($ar["node_id"]==$m["id"]){
							$module_level_2[$i]["checked"] = 1;
						}
					}
					$n=0;
					foreach($result as $r){
						foreach($accessResult as $ar){
							if($ar["node_id"]==$r["id"]){
								$result[$n]["checked"] = 1;
								break;
							}
						}
						$n++;
					}
					$module_level_2[$i]["sub"] = $result;
				}
				$i++;
			}

			//1 为 Shop
			$module_level_1_shop = $mods[1];

			/*这里获取所有模块和操作列表来分配权限用，因为level=1是项目，比如后台*/
			$module_level_2_shop	= $sysNode->where("level=2 and path like '".$module_level_1_shop["id"]."/%'")->order("sort asc")->select();
//			$module_level_2	= $sysNode->where("level=2 ")->order("sort asc")->select();
			$i=0;
			foreach($module_level_2_shop as $m){
				$result	= $sysNode->where("pid=$m[id]")->order("sort asc")->select();
				if(!empty($result)){

					foreach($accessResult as $ar){
						if($ar["node_id"]==$m["id"]){
							$module_level_2[$i]["checked"] = 1;
						}
					}
					$n=0;
					foreach($result as $r){
						foreach($accessResult as $ar){
							if($ar["node_id"]==$r["id"]){
								$result[$n]["checked"] = 1;
								break;
							}
						}
						$n++;
					}
					$module_level_2_shop[$i]["sub"] = $result;
				}
				$i++;
			}

			$this->smarty->assign('module_level_1',$module_level_1);
			$this->smarty->assign('module_level_2',$module_level_2);

			$this->smarty->assign('module_level_1_shop',$module_level_1_shop);
			$this->smarty->assign('module_level_2_shop',$module_level_2_shop);
			unset($module_level_1);
			unset($module_level_2);
			unset($module_level_1_shop);
			unset($module_level_2_shop);



		}
		$this->smarty->display('SysRole/access.html');
		
	}
	
	/*
	*	根据AddOrUpdate 添加更新用户组权限
	*/
	public function accessUpdate(){
		$id=I("post.id",0,'int');
		$access = I('post.access','');
		$sysNode = M("gm_sys_node",null,DB_GM_CFG);
		$sysAccess = M("gm_sys_access",null,DB_GM_CFG);

		$sysAccess->where("role_id='{$id}'")->delete();

		$b = false;
		foreach($access as $vo){
			if(0+$vo<1){continue;}
			$data["role_id"] = $_POST["id"];
			$data["node_id"] = $vo;
			$result = $sysNode->field("pid,level")->where("id=$vo")->find();
			$data["level"] = $result["level"];
			$data["pid"] = $result["pid"];

			$b = $sysAccess->data($data)->add();
		}

		if($b){
//			$this->success('数据修改成功');
			$this->ajaxReturn(array('status'=>0,'msg'=>'数据修改成功'));
		}else{
//			$this->error('数据修改失败');
			$this->ajaxReturn(array('status'=>1,'msg'=>'数据修改失败'));
		}

	}
	
	
	/*获得角色列表*/
	public function getRoleList(){
		$sysRole = M("SysRole");
		return $sysRole->select();
	}
	
	
	
}
?>