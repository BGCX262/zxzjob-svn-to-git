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
 * Sqlite缓存类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CacheSqlite extends Cache
{

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
    public function __construct($options='')
    {
        if ( !extension_loaded('sqlite') ) {    
            throw_exception(L('系统不支持sqlite'));
        }
        if(empty($options)){
            $options= array
            (
                'db'        => ':memory:',
                'table'     => 'sharedmemory',
                'var'       => 'var',
                'value'     => 'value',
                'expire'    => 'expire',
                'persistent'=> false
            );
        }
        $this->options = $options;
        $func = $this->options['persistent'] ? 'sqlite_popen' : 'sqlite_open';
        $this->handler = $func($this->options['db']);
        $this->connected = is_resource($this->handler);
        $this->type = strtoupper(substr(__CLASS__,6));

    }

    /**
     +----------------------------------------------------------
     * 是否连接
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function isConnected()
    {
        return $this->connected;
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
    public function get($name)
    {
		$this->Q(1);
		$name   = sqlite_escape_string($name);
        $sql = 'SELECT '.$this->options['value'].
               ' FROM '.$this->options['table'].
               ' WHERE '.$this->options['var'].'=\''.$name.'\' AND '.$this->options['expire'].'!=-1 AND '.$this->options['expire'].'<'.time().
               ' LIMIT 1';
        $result = sqlite_query($this->handler, $sql);
        if (sqlite_num_rows($result)) {
            $content   =  sqlite_fetch_single($result);
            if(C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                //启用数据压缩
                $content   =   gzuncompress($content);
            }
            return unserialize($content);
        }
        return false;
    }

    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name, $value,$expireTime=0)
    {
		$this->Q(1);
        $expire =  !empty($expireTime)? $expireTime : C('DATA_CACHE_TIME');
        $name  = sqlite_escape_string($name);
        $value = sqlite_escape_string(serialize($value));
        $expire =  ($expireTime==-1)?-1: (time()+$expire);
        if( C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            //数据压缩
            $value   =   gzcompress($value,3);
        }
        $sql  = 'REPLACE INTO '.$this->options['table'].
                ' ('.$this->options['var'].', '.$this->options['value'].','.$this->options['expire'].
                ') VALUES (\''.$name.'\', \''.$value.'\', \''.$expire.'\')';
        sqlite_query($this->handler, $sql);
        return true;
    }

    /**
     +----------------------------------------------------------
     * 删除缓存
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name)
    {
        $name  = sqlite_escape_string($name);
        $sql  = 'DELETE FROM '.$this->options['table'].
               ' WHERE '.$this->options['var'].'=\''.$name.'\'';
        sqlite_query($this->handler, $sql);
        return true;
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
    public function clear()
    {
        $sql  = 'delete from `'.$this->options['table'].'`';
        sqlite_query($this->handler, $sql);
        return ;
    }
}//类定义结束
?>