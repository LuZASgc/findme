<?php
return array(
	//'配置项'=>'配置值'
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',            // 数据库类型
    'DB_HOST'               =>  MAIN_DB_HOST,    // 服务器地址
    'DB_NAME'               =>  MAIN_DB_NAME,        // 数据库名
    'DB_USER'               =>  MAIN_DB_USER,             // 用户名
    'DB_PWD'                =>  MAIN_DB_PWD,           // 密码
    'DB_PORT'               =>  MAIN_DB_PORT,             // 端口
    'DB_PREFIX'             =>  null,    // 数据库表前缀
    'DB_CHARSET'            => 'utf8',

    'DB_MAIN_CFG'=>array(
        'DB_TYPE'               =>  'mysql',     // 数据库类型
        'DB_HOST'               =>  MAIN_DB_HOST, // 服务器地址
        'DB_NAME'               =>  MAIN_DB_NAME,          // 数据库名
        'DB_USER'               =>  MAIN_DB_USER,      // 用户名
        'DB_PWD'                =>  MAIN_DB_PWD,          // 密码
        'DB_PORT'               =>  MAIN_DB_PORT,        // 端口
        'DB_PREFIX'             =>  null,    // 数据库表前缀
    ),

    /* GM数据库设置 */
    'DB_GM_CFG'=>array(
        'DB_TYPE'               =>  'mysql',                // 数据库类型
        'DB_HOST'               =>  GM_DB_HOST,        // 服务器地址
        'DB_NAME'               =>  GM_DB_NAME,              // 数据库名
        'DB_USER'               =>  GM_DB_USER,                 // 用户名
        'DB_PWD'                =>  GM_DB_PWD,      // 密码
        'DB_PORT'               =>  GM_DB_PORT,          // 端口
        'DB_PREFIX'             =>  'gm_',           // 数据库表前缀
    ),
    'SESSION_AUTO_START' =>true,
    'SESSION_PREFIX'=>'fm_',
    //缓存配置
    'DATA_CACHE_TYPE' => 'Memcache',
    'MEMCACHE_HOST'   => 'tcp://127.0.0.1:11211',
    'DATA_CACHE_TIME' => '3600',
    'STATIC_RESOURCE_PREFIX'=> './Public/',//前端静态资源路径前缀     用/结尾
    'URL_MODEL'             =>  0,
    'LOAD_EXT_CONFIG'=>'imgPosition',
);