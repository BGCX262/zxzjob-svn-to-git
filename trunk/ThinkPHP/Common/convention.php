<?php 
// +----------------------------------------------------------------------+
// | ThinkPHP                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006~2007 http://thinkphp.cn All rights reserved.      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: liu21st <liu21st@gmail.com>                                  |
// +----------------------------------------------------------------------+
// $Id$

/**
 +------------------------------------------------------------------------------
 * ThinkPHP惯例配置文件
 * 项目配置文件只需要配置和惯例不符的配置项
 * 请不要修改该文件 修改请在项目配置文件中定义
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();

// 惯例配置定义 变量名大小写任意，都会统一转换成小写
// 如果要覆盖惯例配置的值，请在项目配置文件中设置
// 所有配置参数都可以在生效前动态改变
return  array(

	/* Dispatch设置 */
	'DISPATCH_ON'=>true,	// 是否启用Dispatcher
	'DISPATCH_NAME'=>'Think',	// 默认的Dispatcher名称
	// URL模式： 0 普通模式 1 PATHINFO 2 REWRITE
	'URL_MODEL'=>1,		// 默认为PATHINFO 模式，提供最好的用户体验和SEO支持
	// PATHINFO 模式 
	// 普通模式1 参数没有顺序/m/module/a/action/id/1
	// 智能模式2 自动识别模块和操作/module/action/id/1/ 或者 /module,action,id,1/...
	'PATH_MODEL'=>2,	// 默认采用智能模式
	'PATH_DEPR'=>'/',	// PATHINFO参数之间分割号 
	'ROUTER_ON'=>true,	// 启用路由判断

	/* 日志设置 */
	'WEB_LOG_RECORD'=>false,	 // 默认不记录日志
	'LOG_FILE_SIZE'=>2097152,	// 日志文件大小限制

	/* 插件设置 */
	'THINK_PLUGIN_ON'=>false,	// 默认启用插件机制

	/* 防刷新设置 */
	'LIMIT_RESFLESH_ON'=>false,	// 默认关闭防刷新机制
	'LIMIT_REFLESH_TIMES'=>3,	// 页面防刷新时间 默认3秒

	/* 错误设置 */
	'DEBUG_MODE'=>false,	 // 调试模式默认关闭
	'ERROR_MESSAGE'=>'您浏览的页面暂时发生了错误！请稍后在试～',	// 错误显示信息 非调试模式有效
	'ERROR_PAGE'=>'',	// 错误定向页面

	/* 系统变量设置 */
	'VAR_PATHINFO'=>'s',	// PATHINFO 兼容模式获取变量例如 ?s=/module/action/id/1 后面的参数取决于PATH_MODEL 和 PATH_DEPR
	'VAR_MODULE'=>'m',		// 默认模块获取变量
	'VAR_ACTION'=>'a',		// 默认操作获取变量
	'VAR_ROUTER'=>'r',		// 默认路由获取变量
	'VAR_FILE'=>'f',		// 默认文件变量
	'VAR_PAGE'=>'p',		// 默认分页跳转变量
	'VAR_LANGUAGE'=>'l',		// 默认语言切换变量
	'VAR_TEMPLATE'=>'t',		// 默认模板切换变量
	'VAR_AJAX_SUBMIT'=>'ajax', // 默认的AJAX提交变量

	/* 模块和操作设置 */
	'DEFAULT_MODULE'=>'Index', // 默认模块名称
	'DEFAULT_ACTION'=>'index', // 默认操作名称

	/* 模板设置 */
	'TMPL_CACHE_ON'=>true,		// 默认开启模板缓存
	'TMPL_CACHE_TIME'=>-1,		// 模板缓存有效期 -1 永久 单位为秒
	'DEFAULT_TEMPLATE'=>'default',	// 默认模板名称
	'TEMPLATE_SUFFIX'=>'.html',	 // 默认模板文件后缀
	'CACHFILE_SUFFIX'=>'.php',	// 默认模板缓存后缀
	'TEMPLATE_CHARSET'=>'utf-8',	// 模板模板编码
	'OUTPUT_CHARSET'=>'utf-8',	// 默认输出编码

	/* 模型设置 */
	'CONTR_CLASS_PREFIX'=>'',
	'CONTR_CLASS_SUFFIX'=>'Action',
	'ACTION_PREFIX'=>'',
	'ACTION_SUFFIX'=>'',
	'MODEL_CLASS_PREFIX'=>'',
	'MODEL_CLASS_SUFFIX'=>'Model',

	/* 静态缓存设置 */
	'HTML_FILE_SUFFIX'=>'.shtml',	 // 默认静态文件后缀
	'HTML_CACHE_ON'=>false,		 // 默认关闭静态缓存
	'HTML_CACHE_TIME'=>60,		 // 静态缓存有效期
	'HTML_READ_TYPE'=>1,			// 静态缓存读取方式 0 readfile 1 redirect
	'HTML_URL_SUFFIX'=>'.shtml',	// 伪静态后缀设置

	/* 语言时区设置 */
	'DEFAULT_LANGUAGE'=>'zh-cn',	 // 默认语言
	'TIME_ZONE'=>'PRC',		 // 默认时区

	/* 用户认证设置 */
	'USER_AUTH_ON'=>false,		// 默认不启用用户认证
	'USER_AUTH_TYPE'=>1,		// 默认认证类型 1 登录认证 2 实时认证
	'USER_AUTH_KEY'=>'authId',	// 用户认证SESSION标记
	'AUTH_PWD_ENCODER'=>'md5',	// 用户认证密码加密方式
	'USER_AUTH_PROVIDER'=>'DaoAuthentictionProvider',	 // 默认认证委托器
	'USER_AUTH_GATEWAY'=>'/Public/login',	// 默认认证网关
	'NOT_AUTH_MODULE'=>'Public',		// 默认无需认证模块
	'REQUIRE_AUTH_MODULE'=>'',		// 默认需要认证模块

	/* SESSION设置 */
	'SESSION_NAME'=>'ThinkID',		// 默认Session_name 如果需要不同项目共享SESSION 可以设置相同
	'SESSION_PATH'=>'',			// 采用默认的Session save path
	'SESSION_TYPE'=>'File',			// 默认Session类型 支持 DB 和 File 
	'SESSION_EXPIRE'=>'300000',		// 默认Session有效期
	'SESSION_TABLE'=>'think_session',	// 数据库Session方式表名
	'SESSION_CALLBACK'=>'',			// 反序列化对象的回调方法

	/* 数据库设置 */
	'DB_CHARSET'=>'utf8',			// 数据库编码默认采用utf8
	'DB_DEPLOY_TYPE'=>0,			// 数据库部署方式 0 集中式（单一服务器） 1 分布式（主从服务器）
	'DB_CACHE_ON'=>false,			// 默认关闭数据库缓存
	'DB_CACHE_TIME'=>60,			// 数据库缓存有效期
	'DB_CACHE_MAX'=>5000,			// 数据库缓存最多记录
	'SQL_DEBUG_LOG'=>false,			// 记录SQL语句到日志文件

	/* 数据缓存设置 */
	'DATA_CACHE_ON'=>false,			// 默认关闭数据缓存
	'DATA_CACHE_TIME'=>-1,			// 数据缓存有效期
	'DATA_CACHE_MAX'=>5000,			// 数据缓存最多记录
	'DATA_CACHE_COMPRESS'=>false,		// 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK'=>false,		// 数据缓存是否校验缓存
	'DATA_CACHE_TYPE'=>'File',		// 数据缓存类型 支持 File Db Apc Memcache Shmop Sqlite Xcache Apachenote Eaccelerator
	'DATA_CACHE_TABLE'=>'think_cache',	// 数据缓存表 当使用数据库缓存方式时有效
	'CACHE_SERIAL_HEADER'=>"<?php\n//",	// 文件缓存开始标记
	'CACHE_SERIAL_FOOTER'=>"\n?".">",	// 文件缓存结束标记
	'SHARE_MEM_SIZE'=>1048576,		// 共享内存分配大小

	/* 运行时间设置 */
	'SHOW_RUN_TIME'=>false,			// 运行时间显示
	'SHOW_ADV_TIME'=>false,			// 显示详细的运行时间
	'SHOW_DB_TIMES'=>false,			// 显示数据库查询和写入次数
	'SHOW_CACHE_TIMES'=>false,		// 显示缓存操作次数
	'SHOW_USE_MEM'=>false,			// 显示内存开销
	'SHOW_PAGE_TRACE'=>false,		// 显示页面Trace信息 由Trace文件定义和Action操作赋值

	/* 模板引擎设置 */
	'TMPL_ENGINE_TYPE'=>'Think',		// 默认模板引擎 以下设置仅对使用Think模板引擎有效
	'TMPL_DENY_FUNC_LIST'=>'echo,exit',	// 模板引擎禁用函数
	'TMPL_L_DELIM'=>'{',			// 模板引擎普通标签开始标记
	'TMPL_R_DELIM'=>'}',			// 模板引擎普通标签结束标记
	'TAGLIB_BEGIN'=>'<',			// 标签库标签开始标记
	'TAGLIB_END'=>'>',			// 标签库标签结束标记

	/* Cookie设置 */
	'COOKIE_EXPIRE'=>3600,		// Coodie有效期
	'COOKIE_DOMAIN'=>'',	// Cookie有效域名
	'COOKIE_PATH'=>'/',			// Cookie路径
	'COOKIE_PREFIX'=>'THINK_', // Cookie前缀 避免冲突

	/* 分页设置 */	
	'PAGE_NUMBERS'=>5,			// 分页显示页数
	'LIST_NUMBERS'=>20,			// 分页每页显示记录数

	/* 数据格式设置 */
	'AJAX_RETURN_TYPE'=>'JSON', //AJAX 数据返回格式 JSON XML ...
	'DATA_RESULT_TYPE'=>0,	// 默认数据返回格式 1 对象 0 数组

	/* 其它设置 */
	'AUTO_LOAD_PATH'=>'Think.Util.',	//	 __autoLoad 的路径设置 当前项目的Model和Action类会自动加载，无需设置 注意搜索顺序
	'CALLBACK_LOAD_PATH'=>'',				//	反序列化对象时自动加载的路径设置
	'UPLOAD_FILE_RULE'=>'uniqid',			//  文件上传命名规则 例如 time uniqid com_create_guid 等 支持自定义函数 仅适用于内置的UploadFile类
	'LIKE_MATCH_FIELDS'=>'title|content|value|remark|company|address', //数据库查询的时候需要进行模糊匹配的字段
);
?>