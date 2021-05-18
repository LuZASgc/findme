<?php

//数据库相关配置
define('MAIN_DB_HOST','');//业务_服务器地址|星悦业务库|LT|1
define('MAIN_DB_NAME','');//业务_数据库名|||1
define('MAIN_DB_USER','');//业务_用户名|||1
define('MAIN_DB_PWD','');//业务_密码|||1
define('MAIN_DB_PORT','');//业务_端口|||1
define('MAIN_DB_PREFIX','');//业务_表前缀|默认为空||1
//
define('GM_DB_HOST','');//管理_服务器地址|星悦管理库|LT|1
define('GM_DB_NAME','');//管理_数据库名|||1
define('GM_DB_USER','');//管理_用户名|||1
define('GM_DB_PWD','');//管理_密码|||1
define('GM_DB_PORT','');//管理_端口|||1
define('GM_DB_PREFIX','gm_');//管理_表前缀|默认为gm_||1


//路径类配置
define('USER_HEADPIC_SAVE_PATH','/data/www/starjoy/server/upload/');//用户头像保存路径|仅头像上传保存路径使用|LT|1
define('USER_HEADPIC_SHOW_PATH','http://223.corp.soe-soe.com/server/upload/');//用户头像显示路径|将替换上面路径供前端显示使用|LT|1

define('FILE_UPLOAD_DIR','/data/www/starjoy/server/upload/');//图片上传路径|非头像上传保存路径使用|LT|1
define('FILE_SHOW_PATH','http://223.corp.soe-soe.com/server/upload/');//图片显示路径|将替换上面路径供前端显示使用|LT|1

//微信小程序配置
define('XIAO_APPID','wx0bc6c5ceddbae730');//小程序appid||LT|1
//微信商户
define('PAY_MCHID','1499526672');//小程序appid||LT|1
define('PAY_APIKEY','49f1809020c2fb56212d7fa02c33be01');//小程序appid||LT|1

//本行为结束行，置于文末，请勿删除