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
 * Apc缓存类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CacheApc extends Cache
{//类定义开始


    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
    function __construct($options='')
    {
		if(!function_exists('apc_cache_info')) {
			throw_exception(L('系统不支持Apc'));
		}
		$this->expire = isset($options['expire'])?$options['expire']:C('DATA_CACHE_TIME');
        $this->type = strtoupper(substr(__CLASS__,6));
    }

    /**
     +----------------------------------------------------------
     * 读取缓存
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
     function get($name)
     {
		$this->Q(1);
         return apc_fetch($name);
     }

    /**
     +----------------------------------------------------------
     * 写入缓存
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
     function set($name, $value, $ttl = null)
     {
		$this->W(1);
        if(isset($ttl) && is_int($ttl))
            $expire = $ttl;
        else 
            $expire = $this->expire;
         return apc_store($name, $value, $expire);
     }

    /**
     +----------------------------------------------------------
     * 删除缓存
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
     function rm($name)
     {
         return apc_delete($name);
     }

    /**
     +----------------------------------------------------------
     * 清除缓存
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    function clear()
    {
        return apc_clear_cache();
    }

}//类定义结束
?>