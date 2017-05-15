<?php
return array(
	//修改定界符
	'TMPL_L_DELIM'    =>    '{{',
	'TMPL_R_DELIM'    =>    '}}',

	/* 数据库设置 */
    'DB_TYPE'         =>  'mysqli',     // 数据库类型
    'DB_HOST'         =>  'localhost', // 服务器地址
    'DB_NAME'         =>  'exam',          // 数据库名
    'DB_USER'         =>  'root',      // 用户名
    'DB_PWD'          =>  '123',          // 密码
    'DB_PORT'         =>  '3306',        // 端口
    'DB_PREFIX'       =>  'exam_',    // 数据库表前缀

	'SHOW_PAGE_TRACE'	=> 	TRUE,
	'URL_MODEL'			=> 	2,

    'MODULE_ALLOW_LIST' =>    array('Home','Xdl'),//允许列表
    'DEFAULT_MODULE'    =>    'Home',  // 默认模块
    'URL_MODULE_MAP'    =>    array('xdl'=>'admin'), //模块映射   
);