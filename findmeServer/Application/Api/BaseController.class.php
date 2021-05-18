<?php
/**
 * Created by PhpStorm.
 * User: RickSun
 * Date: 2016/04/14
 * Time: 15:14
 */

namespace Api;
use Think\Controller;
class BaseController extends Controller {

    const SUCCESS   =0;//成功
    const FAILURE   =1;//失败

    public $dataSuccess = array('status'=>self::SUCCESS,'msg'=>'成功');
    public $dataFailure = array('status'=>self::FAILURE,'msg'=>'无效参数');

}
