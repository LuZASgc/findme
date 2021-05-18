<?php
/**
 * 微信红包管理
 */
namespace Admin\Controller;
use Admin\BaseController;
use Common\Model\EditorModel;
use Common\Model\RedpackModel;
use Common\Model\UtilsModel;



class RedpackController extends BaseController{

	/**
	 * 文章列表页
	 * Author: shijy
	 */
	public function setList(){

		$status		=I('post.itemStatus',RedpackModel::PACK_STATUS_ALL,'int');
		$pageSize	=I('post.limit',10,'int');
		$page		=I('post.p',1,'int');


		$itemStatus = RedpackModel::getPackStatus();
		$this->smarty->assign('itemStatus',$itemStatus);



		$list=RedpackModel::getList($status,$page,$pageSize,0);
		fetchAjaxPager($this->smarty,$list,$pageSize);

		$this->smarty->assign('auditStatus',RedpackModel::PACK_STATUS_ONLINE);

		if(IS_AJAX){
			$this->smarty->assign('list',$list['data']);
			$listHtml=$this->smarty->fetch('Redpack/set_list.html');


			$pagerHtml=$this->smarty->fetch('Public/page_ajax.html');
			$this->ajaxReturn(array(
				'status'=>0,
				'listHTML'=>$listHtml,
				'pagerHTML'=>$pagerHtml
			));
		}else{
			$this->smarty->assign('list',$list['data']);
			$this->smarty->display('Redpack/set.html');
		}

	}


	/**
	 * 红包记录
	 */
	public function recordList(){

		$beginTime	=I('post.startDate');
		$endTime	=I('post.endDate');

		$setId		=I('get.id',0,'int');
		if(!$setId){
			$setId	=I('post.id',0,'int');
		}

		$pageSize	=I('post.limit',10,'int');
		$page		=I('post.p',1,'int');



		$recordStatus = RedpackModel::getPackStatus();
		$this->smarty->assign('recordStatus',$recordStatus);


		$beginTS=strtotime($beginTime);
		$endTs	=strtotime($endTime);
		if($endTs==$beginTS && $beginTS>0){
			$endTs +=UtilsModel::ONE_DAY;
		}
		$list=RedpackModel::getRecordList($beginTS,$endTs,$page,$pageSize,0,$setId);
		fetchAjaxPager($this->smarty,$list,$pageSize);

		$this->smarty->assign('auditStatus',AUDIT_PASS);

		if(IS_AJAX){
			$this->smarty->assign('list',$list['data']);
			$listHtml=$this->smarty->fetch('Redpack/record_list.html');


			$pagerHtml=$this->smarty->fetch('Public/page_ajax.html');
			$this->ajaxReturn(array(
				'status'=>0,
				'listHTML'=>$listHtml,
				'pagerHTML'=>$pagerHtml
			));

		}else{
			$packKV=RedpackModel::getSetKV();
			$this->smarty->assign('setKV',$packKV);
			$this->smarty->assign('setId',$setId);


			$this->smarty->assign('list',$list['data']);
			$this->smarty->display('Redpack/record.html');
		}

	}

	//活动添加
	public function add(){
		$this->smarty->display('Redpack/edit.html');
	}



	//审核
	public function audit(){
		$id=I('get.id',0,'int');

		if($id<1){
			$this->ajaxReturn(array('status'=>1,'msg'=>"请指定活动"));
		}


		$exchangeItemM=RedpackModel::getPackInstance();

		$article=$exchangeItemM->where("id={$id}")->find();
		if(!$article){
			$this->ajaxReturn(array('status'=>1,'msg'=>"指定的活动不存在"));
		}

		$data=array();
		switch($article['status']+0){
			case RedpackModel::PACK_STATUS_ONLINE:
				$newAudit=RedpackModel::PACK_STATUS_PAUSE;
				break;
			default:
				$newAudit=RedpackModel::PACK_STATUS_ONLINE;
				break;
		}
		$data['status']=$newAudit;
		$exchangeItemM->where("id={$id}")->save($data);
		RedpackModel::getRecordInstance()->where("setId={$id}")->setField('status',$newAudit);

		$this->ajaxReturn(array('status'=>0,'msg'=>"修改成功",'audit'=>$newAudit));




	}


	public function edit(){
		$id=I('get.id',0,'int');
		$detail=RedpackModel::getPackDetail($id);
		$this->smarty->assign('obj',$detail);

		$canEdit=$detail['status']==RedpackModel::PACK_STATUS_PAUSE && $detail['issuedMoney']==0 && $detail['issuedNum']==0;
		$this->smarty->assign('canEdit',$canEdit);
		$this->smarty->display('Redpack/edit.html');
	}




	//资讯添加或修改
	public function update(){
		$id=I('post.id',0,'int');
		if ($id>0 ){//修改
			if(!RedpackModel::checkCanEdit($id)){
				$this->error("只有没人参加过的且为暂停状态的活动才可编辑！");
			}
		}


		$title=I('post.title','');
		$wishstr=I('post.wish',null);
		if(!$wishstr){
			$this->error("请设置祝福语！");
		}
		$tmpWishArr=explode("\n",$wishstr);
		$wishArr=array();
		foreach ($tmpWishArr as $one){
			$t=trim($one);
			if(strlen($t)>1){
				$wishArr[]=$t;
			}
		}
		$wishNum=count($wishArr);
		if($wishNum<1){
			$this->error("请设置祝福语！！");
		}
		shuffle($wishArr);
		$wishIndex=0;


		$money		= I('post.money');
		$startTime	= I('post.startTime');
		$endTime	= I('post.endTime');
		$set		= I('post.set');


		$waveCount=count($money);//总波数
		$totalMoney=0;
		$setBegin=PHP_INT_MAX;
		$setEnd=0;

		$waveData=array();
		$recordData=array();
		foreach($money as $k=>$one){
			$waveNum=$k+1;
			$waveMoney=intval($one*1000)/10;
			if($waveMoney<=0){
				$this->error('第'.$waveNum.'波，红包金额未设置');
			}
			$totalMoney+=$waveMoney;


			$beginTS=strtotime($startTime[$k]);
			if($beginTS<=0){
				$this->error('第'.$waveNum.'波，开始时间错误');
			}

			if($beginTS>$setEnd){
				$setEnd=$beginTS;
			}else{
				$this->error('第'.$waveNum.'波，本波次开始结束时间必须晚于上一波次时间');
			}

			if($beginTS<$setBegin){//活动开始时间
				$setBegin=$beginTS;
			}

			$endTS=strtotime($endTime[$k]);
			if($endTS<=$beginTS ){
				$this->error('第'.$waveNum.'波，结束时间必须晚于开始时间');
			}

			if($endTS>$setEnd){
				$setEnd=$endTS;
			}else{
				$this->error('第'.$waveNum.'波，本波次开始结束时间必须晚于上一波次时间');
			}


			$waveSet=explode("\r\n",$set[$k]);
			$setArray=array();
			foreach ($waveSet as $oneSet){
				$oneSet=trim($oneSet);
				if(strlen($oneSet)<1){
					continue;
				}
				$tmpSet=explode(',',$oneSet);
				if (count($tmpSet)<3){
					$this->error('第'.$waveNum.'波，奖励配置错误。<br />格式为：金额下限,金额上限,数量');
				}
				$tmpSet[0] =intval(1000*$tmpSet[0])/10;
				$tmpSet[1] =intval(1000*$tmpSet[1])/10;
				$tmpSet[2] =intval($tmpSet[2]);

				if ($tmpSet[0]>$tmpSet[1] || $tmpSet[0]<=0 || $tmpSet[1]<=0 || $tmpSet[2]<=0){
					$this->error('第'.$waveNum.'波，奖励配置错误。<br />下限金额不可超出上限金额<br />金额上下限必须大于0<br />红包数量必须大于0');
				}

				$setArray[]=$tmpSet;
			}
			$rt=$this->calcuteAssign($waveMoney,$setArray);
			if($rt['status']!=0){
				$this->error('第'.$waveNum.'波，'.$rt['msg']);
			}else{
				foreach($rt['data'] as $packValue){
					$wishIndex++;
					$wishIndex %= $wishNum;
					$recordData[]=array(
						'setId'=>0,
						'waveId'=>$waveNum,
						'wish' =>$wishArr[$wishIndex],
						'money'=>$packValue,
						'status'=>RedpackModel::PACK_STATUS_ONLINE
					);
				}
			}


			$waveData[]=array(
				'setId'=>0,
				'waveId'=>$waveNum,
				'money'=>$waveMoney,
				'waveSet'=>$set[$k],
				'beginTime'=>$beginTS,
				'endTime'=>$endTS
			);


		}

		//保存配置
		RedpackModel::getPackInstance()->startTrans();

		$setData=array(
			'title'=>$title,
			'wish'=>$wishstr,
			'waves'=>$waveCount,
			'totalMoney'=>$totalMoney,
			'addTime'=>time(),
			'beginTime'=>$setBegin,
			'endTime'=>$setEnd			
		);
		if ($id==0){//添加
			$setData['status']=RedpackModel::PACK_STATUS_ONLINE;
			$result=RedpackModel::getPackInstance()->add($setData);
			$setId=$result;
		}else{
			$result=RedpackModel::getPackInstance()->where("id={$id}")->save($setData);
			$setId=$id;
		}

		if ($result==false){
			$this->error("活动保存失败");
		}

		if ($id>0 ) {//修改
			RedpackModel::getWaveInstance()->where("setId={$setId}")->delete();
			RedpackModel::getRecordInstance()->where("setId={$setId}")->delete();
		}

		foreach($waveData as $k=>$d){
			$waveData[$k]['setId']=$setId;
		}
		RedpackModel::getWaveInstance()->addAll($waveData);


		foreach($recordData as $k=>$d){
			$recordData[$k]['setId']=$setId;
		}
		shuffle($recordData);
		RedpackModel::getRecordInstance()->addAll($recordData);

		RedpackModel::getPackInstance()->commit();

		$this->success("操作成功");

	}


	/**
	 * 计算分配方案
	 * 金额以分为单位
	 * @param $money 分配金额
	 * @param $setArray 分配方案数组
	 * @return array
	 */
	private function calcuteAssign($money,$setArray){

		$minMoney=0;
		$maxMoney=0;
		$middle=array();
		foreach($setArray as $k=>$one){
			$min=$setArray[$k][0];
			$max=$setArray[$k][1];
			$num=$setArray[$k][2];

			$minMoney += ($min * $num);
			$maxMoney += ($max * $num);

			if($min==$max){
				$middle[$k]=0;
			}else{
				$middle[$k]=($max-$min)*$num;
			}
		}



		if($minMoney > $money){
			//不够分
			return array('status'=>1,'msg'=>'金额不足，最低金额要求'.($minMoney/100).'元');
		}elseif ($minMoney == $money){
			$assign=array();
			foreach($setArray as $k=>$one){
				$min=$setArray[$k][0];
				$num=$setArray[$k][2];

				$assign[]=array_fill(0,$num,$min);
			}
			return array('status'=>0,'data'=>$this->flatten($assign) );
		}



		if($maxMoney < $money){
			//分不完
			return array('status'=>1,'msg'=>'金额过大，最大金额要求'.($maxMoney/100).'元');
		}elseif ($maxMoney == $money){
			//刚好分完
			$assign=array();
			foreach($setArray as $k=>$one){
				$max=$setArray[$k][1]+0;
				$num=$setArray[$k][2]+0;

				$assign[]=array_fill(0,$num,$max);
			}
			return array('status'=>0,'data'=>$this->flatten($assign) );
		}

		/**
		 * 余值按比例分配
		 */
		$left=$money-$minMoney;
		$total=array_sum($middle);
		foreach ($middle as $k=>$v){
			$middle[$k]=round($v/$total * $left);
		}
		$assignedMoney=array_sum($middle);
		$left -= $assignedMoney;
		if($left!=0){
			//todo 未分本配的处理
		}


		$assignedMoney=0;
		$assign=array();
		foreach ($setArray as $k=>$v){
			$baseV	=$v[0];
			$maxV	=$v[1];
			$num	=$v[2];

			$fillValue=floor($middle[$k]/$num+$baseV);//单红包初始分配金

			$assign[$k]=array_fill(0,$num,$fillValue);

			if ($num<=1)continue;
			for($i=0;$i<$num;$i++){
				$currentValue=$assign[$k][$i];
				$changeValue=rand($baseV,$currentValue)-$baseV;//变化量
				if($i==0) {
					$otherPos = 1;
				}else{
					$otherPos = rand(0, $num - 1);
				}

				if($otherPos==$i) continue;//不与自己交换
				if($assign[$k][$otherPos] + $changeValue >=$maxV){
					$assign[$k][$i] 		-=($maxV-$assign[$k][$otherPos]);
					$assign[$k][$otherPos]	= $maxV;
				}else{
					$assign[$k][$i] 		-=$changeValue;
					$assign[$k][$otherPos]	+=$changeValue;
				}
			}

			$tmpSum=(array_sum($assign[$k]));
			$assignedMoney += $tmpSum;
		}

		$leftMoney=$money-$assignedMoney;
		if($leftMoney>0){//分配有剩余，再次扫描追加
			foreach($assign as $batch=>$arr){
				foreach ($arr as $k=>$v){
					if($v+$leftMoney <=$setArray[$batch][1]){
						$assign[$batch][$k] += $leftMoney;
						break 2;
					}
				}
			}
		}


		return array('status'=>0,'data'=>$this->flatten($assign) );
	}


	/**
	 * 使分配数组扁平化
	 * 从二维数组到一维数组
	 * @param $assign
	 * @return array
	 */
	private function flatten(&$assign){
		$sequence=array();
		foreach($assign as $batch=>$arr){
			foreach ($arr as $k=>$v){
				$sequence[]=$v;
			}
		}
		return $sequence;

	}




}
