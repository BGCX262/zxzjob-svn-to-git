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
 * ThinkPHP系统基类 抽象类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
abstract class Base
{

    /**
     +----------------------------------------------------------
     * 自动变量设置
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param $name 属性名称
     * @param $value  属性值
     +----------------------------------------------------------
     */
    private function __set($name ,$value)
    {
        if(property_exists($this,$name)){
            $this->$name = $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 自动变量获取
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param $name 属性名称
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    private function __get($name)
    {
        if(isset($this->$name)){
            return $this->$name;
        }else {
            return null;
        }
    }

}//类定义结束
?>