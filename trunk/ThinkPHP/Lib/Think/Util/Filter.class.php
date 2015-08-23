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
 * 过滤器类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Filter extends Base
{
    /**
     +----------------------------------------------------------
     * 加载过滤器
     * 
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @param string $filterNames  过滤器名称
     * @param string $method  执行的方法名称
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static  function load($filterNames,$method='execute') 
    {
        $filterPath = dirname(__FILE__).'/Filter/';
        $filters    =   explode(',',$filterNames);
        $load = false;
        foreach($filters as $key=>$val) {
            if(strpos($val,'.')) {
                $filterClass = strtolower(substr(strrchr($val, '.'),1));
            	import($val);
            }else {
                $filterClass = 'Filter'.$val ;
                require_cache( $filterPath.$filterClass . '.class.php');
            }
            if(class_exists($filterClass)) {
                $filter = get_instance_of($filterClass);
                $filter->{$method}();            	
            }
        }
        return ;
    }
};
?>