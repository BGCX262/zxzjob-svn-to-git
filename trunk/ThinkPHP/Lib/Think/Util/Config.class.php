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
 * 配置文件管理类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Config extends Base
{//类定义开始

    /**
     +----------------------------------------------------------
     * 配置参数的内部存储
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $_config = array();

    /**
     +----------------------------------------------------------
     * 配置文件类型
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $_type = array('xml','ini','obj','array','def','dao');

    /**
     +----------------------------------------------------------
     * 配置文件名称
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $filename;

    /**
     +----------------------------------------------------------
     * 是否连接
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $_connect;

	// 实例化
    static function getInstance() 
    {
        return get_instance_of(__CLASS__);
    }

    /**
     +----------------------------------------------------------
     * 加载配置文件
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $config 配置数据
     * @param string $type  配置类型 默认自动判断
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function parse($config,$type='') 
    {
        if(is_array($config)) {
            $confType  = 'array';
        }elseif(is_object($config)) {
            $confType  = 'obj';
        }elseif(is_string($config) && empty($type) ) {
            $info = pathinfo($config);
            $confType = strtolower($info['extension']);
        }else {
            if(in_array(strtolower($type),$this->_type)) {
                $confType  = $type;
            }
        }
        $confClass = 'Config'.ucwords(strtolower($confType));
        $configClassPath = dirname(__FILE__).'/Config/';		
        if(require_cache ($configClassPath.$confClass . '.class.php')){
            $con =  new $confClass($config);
            if(!$con->connect()){
                throw_exception(L('_CONFIG_FILE_INVALID_'));
            }
            return $con;
        }else {
            throw_exception(L('_CONFIG_TYPE_INVALID_'));
        }
    }

    /**
     +----------------------------------------------------------
     * 是否正常加载配置文件
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function connect() 
    {
        return $this->_connect;
    }

    /**
     +----------------------------------------------------------
     * 获取配置项
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 数据
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function get($name) 
    {
		if(isset($this->_config[$name])) {
	        return $this->_config[$name];
		}else{
			return null;
		}
    }

	protected function __get($name) {
		return $this->get($name);
	}

    /**
     +----------------------------------------------------------
     * 设置配置项
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 配置项名称
     * @param string $value  配置项值
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function set($name,$value) 
    {
        $previous = $this->get($name);
        $this->_config[$name] = $value;
        return $previous;
    }

	protected function __set($name,$value) {
		return $this->set($name,$value);
	}

	// 追加配置项目
	public function append($array,$override=true) {
		if($override) {
			// 覆盖模式追加
			$this->_config = array_merge($this->_config,array_change_key_case($array));
		}else{
			$this->_config = array_merge(array_change_key_case($array),$this->_config);
		}
	}

    /**
     +----------------------------------------------------------
     * 转换成数组，如果指定文件则保存到数组文件
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $filename  文件名
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function toArray($filename='') 
    {
        if(!empty($filename) && file_exists(dirname($filename))) {
            $content  = "<?php\n";
			$content .= "if (!defined('THINK_PATH')) exit();\n";
            $content .= "return ".var_export($this->_config,true);
            $content .= ";\n\r?>";
            file_put_contents($filename,$content);
            return ;
        }
        return $this->_config;
    }

    /**
     +----------------------------------------------------------
     * 把配置文件转换成XML字串
     * 如果指定文件则保存到xml文件
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $root XML文件的根节点名
     * @param string $encoding  XML文件编码
     * @param string $filename  文件名
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function toXML($root='config',$encoding='utf-8',$filename='') 
    {
        $xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';
        $xml.= '<'.$root.'>';
        $xml.= $this->arrayToXml($this->toArray());   
        $xml.= '</'.$root.'>'; 
        if(!empty($filename) && file_exists(dirname($filename))) {
            file_put_contents($filename,$xml);
            return ;
        }
        return $xml; 
    }

    /**
     +----------------------------------------------------------
     * 把数组转换成XML节点
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $root XML文件的根节点名
     * @param string $encoding  XML文件编码
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function arrayToXml($array) {
        $xml = '';
        foreach($array as $key=>$val) {
            is_numeric($key) && $key="item id=\"$key\"";
            $xml.="<$key>";
            $xml.=is_array($val)?$this->arrayToXml($val):$val;
            list($key,)=explode(' ',$key);
            $xml.="</$key>";
        }
        return $xml;
    }


    /**
     +----------------------------------------------------------
     * 转换成INI文件
     * 如果指定文件名，则生成ini文件 否则返回ini字符串
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $filename  文件名
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function toIni($filename='') 
    {
        $ini = $this->arrayToIni($this->toArray());
        if(!empty($filename) && file_exists(dirname($filename))) {
            file_put_contents($filename,$ini);
            return ;
        }
        return $ini;
    }

    /**
     +----------------------------------------------------------
     * 把数组转换成ini文件内容
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $array  数组
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function arrayToIni($array) 
    {
        foreach($array as $key => $val) {
         if(is_array($val)) {
           $content .= "\n[{$key}]\n";
           foreach ($val as $key2 => $val2) {
               if(is_int($val2) || is_float($val2)) {
                   $content .= "{$key2} = {$val2}\n";
               }
               elseif(is_bool($val2)) {
                   $val2 = ($val2)?'true':'false';
                   $content .= "{$key2} = {$val2}\n";
               }
               else{
                   $content .= "{$key2} = \"{$val2}\"\n";
               }
           }        
         }
         else
         {
           if(is_int($val) || is_float($val)) {
               $content .= "{$key} = {$val}\n";
           }
           elseif(is_bool($val)) {
               $val = ($val)?'on':'off';
               $content .= "{$key} = {$val}\n";
           }
           else{
               $content .= "{$key} = \"{$val}\"\n";
           }
         }
       }   
       return $content;
    }

    /**
     +----------------------------------------------------------
     * 转换成常量
     * 
     +----------------------------------------------------------
     * @param array $filename 需要输出的常量定义文件名
     * @param array $prefix 常量前缀
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function toConst($filename='',$prefix='') 
    { 
        $content = $this->arrayToConst($this->_config,$prefix);
        if(!empty($filename) && file_exists(dirname($filename))) {
            file_put_contents($filename,$content);
        }
        return ;
    }


    /**
     +----------------------------------------------------------
     * 把数组转换成系统常量 键名作为常量名，并转换为大写
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $array 数组
     * @param array $prefix 常量前缀
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function arrayToConst($array,$prefix='') 
    {
        $content  = "<?php\n";
        $content .= "if (!defined('THINK_PATH')) exit();\n";
        foreach($array as $key=>$val) {
            if(!defined(strtoupper($prefix.$key))) {
                define(strtoupper($prefix.$key),$val);
            }
            if(is_int($val) || is_float($val)) {
                $content .= "define('".strtoupper($prefix.$key)."',".$val."); \n";   
            }
            elseif(is_bool($val)) {
                $val = ($val)?'true':'false';
                $content .= "define('".strtoupper($prefix.$key)."',".$val."); \n";  
            }
            elseif(is_string($val)) {
                $content .= "define('".strtoupper($prefix.$key)."','".addslashes($val)."'); \n";   
            }
        }
        $content .= "\n?>";
		return $content;
    }

    /**
     +----------------------------------------------------------
     * 转换成对象
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return object
     +----------------------------------------------------------
     */
    public function toObject() 
    {	
        $object = new stdClass();
        foreach($this->_config as $key=>$val) {
            $object->$key = $val;
        }
        return $object;
    }

}//类定义结束
?>