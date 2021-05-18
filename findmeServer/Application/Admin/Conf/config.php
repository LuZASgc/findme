<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE'               =>  'mysql',        // 数据库类型
    'DB_HOST'               =>  GM_DB_HOST,    // 服务器地址
    'DB_NAME'               =>  GM_DB_NAME,      // 数据库名
    'DB_USER'               =>  GM_DB_USER,         // 用户名
    'DB_PWD'                =>  GM_DB_PWD,       // 密码
    'DB_PORT'               =>  GM_DB_PORT,         // 端口
    'DB_PREFIX'             =>  GM_DB_PREFIX,          // 数据库表前缀
    
	    /*RBAC*/
    'URL_CASE_INSENSITIVE'=>false,
    'USER_AUTH_ON'=>true,
    'USER_AUTH_TYPE'=>1,
    'USER_AUTH_KEY'=>'auth_id',
    'ADMIN_AUTH_KEY'=>'admin',
    'USER_AUTH_MODEL'=>'Sys_user',
    'AUTH_PWD_ENCODER'=>'md5',
    'USER_AUTH_GETWAY'=>'/public/login',
    'NOT_AUTH_MODULE'=>'Public,Common',
    'REQUIRE_AUTH_MODULE'=>'',
    'NOT_AUTH_ACTION'=>'',
    'REQUIRE_AUTH_ACTION'=>'',
    'GUEST_AUTH_ON'=>false,
    'GUEST_AUTH_ID'=>0,

    'RBAC_ROLE_TABLE'=>'gm_sys_role',
    'RBAC_USER_TABLE'=>'gm_sys_user',
    'RBAC_ACCESS_TABLE'=>'gm_sys_access',
    'RBAC_NODE_TABLE'=>'gm_sys_node',
    /* RBAC 结束 */
);