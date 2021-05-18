<?php
/**
 * 基础model
 */
namespace Common\Model;

use Think\Model;

/**
 * 用户基础模型
 */
class BaseModel extends Model {
	const SUCCESS   =0;//成功
	const FAILURE   =1;//失败
	const UNCOMPLETE=2;//订单信息填写未完成
	const UNPAY=3;//订单未完成支付
	public $dataSuccess = array('status'=>self::SUCCESS,'msg'=>'成功');
	public $dataFailure = array('status'=>self::FAILURE,'msg'=>'无效参数');



	/**
	 * 可变属性的日志，积分，经验，余额
	 * Author: shijy
	 * @param $table
	 * @param $uid
	 * @param $actionType 操作枚举值
	 * @param $addOrReduce 增加或减少
	 * @param $changeVal 变化量
	 * @param $finalVal 最终值
	 * @param int $objID 操作对象编号
	 * @param string $desc
	 * @return mixed false表示日志失败
	 */
	public static function valLog($table,$uid,$actionType,$addOrReduce,$changeVal,$finalVal,$objID=0,$desc=''){
		$now=time();
		return M($table,null,DB_MAIN_CFG)->add(
			array(
				'uid'=>$uid,
				'objID'=>$objID,
				'act'=>$actionType,
				'addOrReduce'=>$addOrReduce,
				'changeVal'=>$changeVal,
				'finalVal'=>$finalVal,
				'addTime'=>$now,
				'desc'=>$desc
			)
		);
	}

	/**
	 *获取日志分页：积分，经验，余额
	 * Author: shijy
	 * @param $uid
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public static function getValLogList($table,$uid,$page,$pageSize){
		$dataSuccess = array('status'=>self::SUCCESS,'msg'=>'成功');
		$dataFailure = array('status'=>self::FAILURE,'msg'=>'无效参数');
		if(!$uid){
			return $dataFailure;
		}

		$M = M($table,null,DB_MAIN_CFG);
		$where="uid={$uid}";
		$dataSuccess['total'] = $M->where($where)->count();//获取数据总条数
		$dataSuccess['page'] = max($page , 1);//返回当前页
		$dataSuccess['totalPage'] = ceil($dataSuccess['total']/$pageSize);//总共页数
		$dataSuccess['nextPage'] = $dataSuccess['page'] >= $dataSuccess['totalPage'] ? 0 : 1;//是有有下一页
		$offset=($dataSuccess['page']-1) * $pageSize;
		$list = $M->where('uid=' . $uid)
			->order('addTime desc')
			->limit($offset,$pageSize)
			->select();
		$dataSuccess['data']=$list;
		return $dataSuccess;
	}


}