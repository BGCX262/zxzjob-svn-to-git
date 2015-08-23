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
 * 委托数据库的Dao对象验证用户信息
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  RBAC
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class DaoAuthentictionProvider extends ProviderManager
{//类定义开始

    /**
     +----------------------------------------------------------
     * 数据库认证的表名
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $authTable;

    /**
     +----------------------------------------------------------
     * 数据库表主键名
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $pk =   'id';

    /**
     +----------------------------------------------------------
     * 数据库表主键名
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $authType =   array();


    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     */
    public function authenticate($map,$daoClass='User')
    {
        $dao    = D($daoClass);
        $result = $dao->find($map);
        if($result) {
            $this->data =   $result;
            return true;
        }else {
            return false;
        }
    }

}//类定义结束
?>