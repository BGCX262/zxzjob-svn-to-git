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
 * ThinkPHP公共文件
 +------------------------------------------------------------------------------
 * @copyright  Copyright (c) 2005-2006 liu21st.com.  All rights reserved. 
 * @author     liu21st <liu21st@gmail.com>
 * @version    $Id$
 +------------------------------------------------------------------------------
 */

//记录开始运行时间
$GLOBALS['_beginTime'] = array_sum(explode(' ', microtime()));

//ThinkPHP系统目录定义
if(!defined('THINK_PATH')) define('THINK_PATH', dirname(__FILE__));

// 部署模式只需要加载一个Core文件
// 包括了定义文件、函数库、Core类库和异常、日志类库
// require(THINK_PATH.'/Core.php');


// 开发模式
// 加载系统定义文件
require THINK_PATH."/Common/defines.php";

// 记录内存初始使用
if(MEMORY_LIMIT_ON) {
	 $GLOBALS['_startUseMems'] = memory_get_usage();
}
// 系统函数库
require THINK_PATH."/Common/functions.php";


//加载ThinkPHP基类
import("Think.Core.Base");
//加载异常处理类
import("Think.Exception.ThinkException");
//加载Think核心类
import("Think.Core.App");

// 记录加载文件时间
$GLOBALS['_loadTime'] = array_sum(explode(' ', microtime()));
?>