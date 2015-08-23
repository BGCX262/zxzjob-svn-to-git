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
 * 语言包管理类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Language extends Base 
{

	// 语言包数组
	static $_lang = array();

	// 加载语言文件
	// 支持php数组定义、常量定义
	static function load($file,$return=false) {
		if(file_exists($file)) {
			$before = get_defined_constants();
			// 数组返回值方式定义
			$lang = include $file;
			if(!is_array($lang)) {
				if(isset($_lang)) {
					// 采用 $_lang['aaa']= value 方式
					$lang = &$_lang;
				}else{
					// 采用define('aaa',value) 方式
					$after  = get_defined_constants();
					$define = array_diff_assoc($after,$before);
					$lang = $define;
				}
			}
			self::$_lang = array_merge(self::$_lang, array_change_key_case($lang));
			unset($lang);
			if($return) {
				return self::$_lang;
			}
		}else{
			return false;
		}
	}

	// 追加参数
	static function append($array,$override=true) {
		if($override) {
			// 覆盖模式追加
			self::$_lang = array_merge(self::$_lang,array_change_key_case($array));
		}else{
			self::$_lang = array_merge(array_change_key_case($array),self::$_lang);
		}
	}

	// 设置参数
	static function set($name,$value) {
		self::$_lang[$name] = $value;
	}

	static function is_set($name) {
		return isset(self::$_lang[$name]);
	}

	// 获取参数
	static function get($name) {
		if(isset(self::$_lang[$name])) {
			return self::$_lang[$name];
		}else{
			return null;
		}
	}
}
?>