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
 * ThinkPHP 默认的调试模式配置文件
 *  如果项目有定义自己的调试模式配置文件，本文件无效
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();
// 惯例配置定义 变量名大小写任意，都会统一转换成小写
return  array(
	/* 日志设置 */
	'WEB_LOG_RECORD'=>true,	 // 默认进行日志记录
	'LOG_FILE_SIZE'=>2097152,	// 日志文件大小限制

	/* 防刷新设置 */
	'LIMIT_RESFLESH_ON'=>false,	// 默认关闭防刷新机制
	'LIMIT_REFLESH_TIMES'=>30,	// 页面防刷新时间 默认3秒

	/* 模板设置 */
	'TMPL_CACHE_ON'=>true,		// 默认开启模板缓存
	'TMPL_CACHE_TIME'=>-1,		// 模板缓存有效期 -1 永久 单位为秒

	/* 数据库设置 */
	'DB_DEPLOY_TYPE'=>0,			// 数据库部署方式 0 集中式（单一服务器） 1 分布式（主从服务器）
	'DB_CACHE_ON'=>false,			// 默认关闭数据库缓存
	'SQL_DEBUG_LOG'=>true,			// 记录SQL语句到日志文件

	/* 数据缓存设置 */
	'DATA_CACHE_ON'=>false,			// 默认关闭数据缓存
	'DATA_CACHE_TIME'=>-1,			// 数据缓存有效期
	'DATA_CACHE_MAX'=>5000,			// 数据缓存最多记录
	'DATA_CACHE_COMPRESS'=>false,		// 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK'=>false,		// 数据缓存是否校验缓存
	'DATA_CACHE_TYPE'=>'File',		// 数据缓存类型 支持 File Db Apc Memcache Shmop Sqlite Xcache Apachenote Eaccelerator
	'SHARE_MEM_SIZE'=>1048576,		// 共享内存分配大小

	/* 运行时间设置 */
	'SHOW_RUN_TIME'=>true,			// 运行时间显示
	'SHOW_ADV_TIME'=>true,			// 显示详细的运行时间
	'SHOW_DB_TIMES'=>true,			// 显示数据库查询和写入次数
	'SHOW_CACHE_TIMES'=>true,		// 显示缓存操作次数
	'SHOW_USE_MEM'=>true,			// 显示内存开销
	'SHOW_PAGE_TRACE'=>true,		// 显示页面Trace信息 由Trace文件定义和Action操作赋值
);
?>