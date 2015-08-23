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
 * 认证委托管理器
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  RBAC
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ProviderManager extends Base
{//类定义开始

    /**
     +----------------------------------------------------------
     * 认证后的用户信息
     +----------------------------------------------------------
     * @var mixed
     * @access protected
     +----------------------------------------------------------
     */
    protected $data;

    /**
     +----------------------------------------------------------
     * 取得委托管理类实例
     * 
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @return mixed 返回委托管理类
     +----------------------------------------------------------
     */
    public static function getInstance() 
    {
        $param = func_get_args();
        return get_instance_of(__CLASS__,'connect',$param);
    }

    /**
     +----------------------------------------------------------
     * 加载委托管理
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $authProvider 委托方式
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($authProvider='') 
    {
        $providerPath = dirname(__FILE__).'/Provider/';
        $authProvider = empty($authProvider)? C('USER_AUTH_PROVIDER'):$authProvider;
        if (require_cache( $providerPath . $authProvider . '.class.php'))    
                $provider = & new $authProvider();
        else 
            throw_exception(L('系统暂时不支持委托方式: ') .$authProvider);
        return $provider;
    }
}//类定义结束
?>