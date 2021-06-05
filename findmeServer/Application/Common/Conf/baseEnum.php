<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/9
 * Time: 20:10
 */

define('DB_GM_CFG','DB_GM_CFG');//管理后台数据库
define('DB_MAIN_CFG','DB_MAIN_CFG');//主库


//启用状态 1是启用 0是未启用
define('ENABLE_ENABLE'   ,1);
define('ENABLE_DISABLE'     ,2);

//图片鉴黄
define('PHOTO_AUDIT_UNCHECK'    ,0);//未检查
define('PHOTO_AUDIT_PASS'       ,1);//通过
define('PHOTO_AUDIT_UNPASS'     ,2);//不通过
define('PHOTO_AUDIT_REVIEW'     ,3);//需要人工