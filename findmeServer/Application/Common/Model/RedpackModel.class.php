<?php
/**
 * 微信红包管理
 */
namespace Common\Model;
use Think\Exception;

class RedpackModel extends BaseModel{
	static $recordInstance=null;
	public static function getRecordInstance(){
		if(!self::$recordInstance){
			self::$recordInstance=M('redpack_record',null,DB_MAIN_CFG);
		}
		return self::$recordInstance;
	}



	/**
	 * 获取红包细分记录
	 * @param $beginTime 	开始时间戳
	 * @param $endTime 	结束时间戳
	 * @param $page
	 * @param $pageSize
	 * @param int $uid
	 * @param int $setId
	 * @return array
	 */
	public static function getRecordList($beginTime,$endTime,$page,$pageSize,$uid=0,$setId=0){
		$dataSuccess = array('status'=>self::SUCCESS,'msg'=>'成功');
		$dataFailure = array('status'=>self::FAILURE,'msg'=>'无效参数');
		$tradeM = self::getRecordInstance();

		$where='1';

		if ($uid>0){
			$where .=" and uid={$uid}";
		}

		if ($setId>0){
			$where .=" and setId={$setId}";
		}

		if($beginTime>0){
			$where .=" and sendTime >={$beginTime}";
		}
		if($endTime>0){
			$where .=" and sendTime <={$endTime}";
		}


		$where .=" and uid>0 and sendTime>0";


		$dataSuccess['total'] = $tradeM->where($where)->count();//获取数据总条数
		$dataSuccess['page'] = $page ? $page : 1;//返回当前页
		$dataSuccess['totalPage'] = ceil($dataSuccess['total']/$pageSize);//总共页数
		$dataSuccess['nextPage'] = $dataSuccess['page'] >= $dataSuccess['totalPage'] ? 0 : 1;//是有有下一页
		$offset=($dataSuccess['page']-1)*$pageSize;


		$exchangeList = $tradeM->where($where)
			->order('id desc')
			->limit($offset,$pageSize)
			->select();

		$dataSuccess['data']=$exchangeList;
		return $dataSuccess;
	}




	/////////////////////////生成红包分配方案//////////////////////////////////////////////////////
	public static function checkAssign($money,$num,$minMoney){
		return $minMoney*$num<=$money;
	}

	//资讯添加或修改
	public static function createRecord($gid,$money,$num,$minMoney){
		$recordData=array();
		$setArray=array();
		$setArray[]=array($minMoney,$money,$num);
		$rt=self::calcuteAssign($money,$setArray);
		if($rt['status']!=0){
			return array('status'=>1,'msg'=>$rt['msg']);
		}else{
			foreach($rt['data'] as $packValue){
				$recordData[]=array(
					'setId'=>$gid,
					'money'=>$packValue,
				);
			}
		}

		shuffle($recordData);
		self::getRecordInstance()->addAll($recordData);
		return array('status'=>0,'msg'=>'success');

	}


	/**
	 * 计算分配方案
	 * 金额以分为单位
	 * @param $money 分配金额
	 * @param $setArray 分配方案数组
	 * @return array
	 */
	private static function calcuteAssign($money,$setArray){
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
			return array('status'=>0,'data'=>self::flatten($assign) );
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
			return array('status'=>0,'data'=>self::flatten($assign) );
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
		return array('status'=>0,'data'=>self::flatten($assign) );
	}


	/**
	 * 使分配数组扁平化
	 * 从二维数组到一维数组
	 * @param $assign
	 * @return array
	 */
	private static function flatten(&$assign){
		$sequence=array();
		foreach($assign as $batch=>$arr){
			foreach ($arr as $k=>$v){
				$sequence[]=$v;
			}
		}
		return $sequence;

	}




}