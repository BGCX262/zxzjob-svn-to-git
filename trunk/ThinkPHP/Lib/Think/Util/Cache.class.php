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
 * 缓存管理类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Cache extends Base
{//类定义开始

    /**
     +----------------------------------------------------------
     * 是否连接
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $connected  ;

    /**
     +----------------------------------------------------------
     * 操作句柄
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $handler    ;

    /**
     +----------------------------------------------------------
     * 缓存存储前缀
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $prefix='~@';

    /**
     +----------------------------------------------------------
     * 缓存连接参数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $options = array();

    /**
     +----------------------------------------------------------
     * 缓存类型
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $type       ;

    /**
     +----------------------------------------------------------
     * 缓存过期时间
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $expire     ;

    /**
     +----------------------------------------------------------
     * 连接缓存
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $type 缓存类型
     * @param array $options  配置数组
     +----------------------------------------------------------
     * @return object
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($type='',$options=array())
    {
        if(empty($type)){
            $type = C('DATA_CACHE_TYPE');
        }
        if(Session::is_set('CACHE_'.strtoupper($type))) {
        	$cacheClass   = Session::get('CACHE_'.strtoupper($type));
        }else {
            $cachePath = dirname(__FILE__).'/Cache/';
            $cacheClass = 'Cache'.ucwords(strtolower(trim($type)));
            require_cache($cachePath.$cacheClass.'.class.php');
        }
        if(class_exists($cacheClass)){
            $cache = new $cacheClass($options);
        }else {
            throw_exception(L('_CACHE_TYPE_INVALID_').':'.$type);
        }
        return $cache;
    }

	protected function __get($name) {
		return $this->get($name);
	}

	protected function __set($name,$value) {
		return $this->set($name,$value);
	}

	public function setOptions($name,$value) {
		$this->options[$name]	=	$value;
	}

	public function getOptions($name) {
		return $this->options[$name];
	}
    /**
     +----------------------------------------------------------
     * 取得缓存类实例
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static function getInstance() 
    {
       $param = func_get_args();
        return get_instance_of(__CLASS__,'connect',$param);
    }

	// 读取缓存次数
	public function Q($times='') {
		static $_times = 0;
		if(empty($times)) {
			return $_times;
		}else{
			$_times++;
		}
	}

	// 写入缓存次数
	public  function W($times='') {
		static $_times = 0;
		if(empty($times)) {
			return $_times;
		}else{
			$_times++;
		}
	}
}//类定义结束
?>