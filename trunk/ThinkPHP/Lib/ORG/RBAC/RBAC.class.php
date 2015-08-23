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
 * 基于角色的验证类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  RBAC
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
// 配置文件增加设置
// USER_AUTH_ON 是否需要认证
// USER_AUTH_TYPE 认证类型
// USER_AUTH_KEY 认证识别号
// REQUIRE_AUTH_MODULE  需要认证模块
// NOT_AUTH_MODULE 无需认证模块
// USER_AUTH_GATEWAY 认证网关
class RBAC extends Base 
{
    //委托身份认证方法
    static function authenticate($map,$model='User',$provider='') 
    {
            //调用委托管理器进行身份认证
            import("ORG.RBAC.ProviderManager");
			if(empty($provider)) {
				$provider	=	C('USER_AUTH_PROVIDER');
			}
            $authProvider   =   ProviderManager::getInstance($provider);
            //使用给定的Map进行认证
            if($authProvider->authenticate($map,$model)) {	
                $authInfo   =   $authProvider->data;
                return $authInfo;
            }else {
                //认证失败
                return false;
            }
    }

    //用于检测用户权限的方法,并保存到Session中
    static function saveAccessList($authId=null) 
    {
            // 如果使用普通权限模式，保存当前用户的访问权限列表
            // 对管理员开发所有权限
            if(C('USER_AUTH_TYPE') !=2 && !Session::is_setLocal('administrator') ) {
                Session::set('_ACCESS_LIST',RBAC::getAccessList($authId));
            }	
            return ;
    }

    //取得用户的授权列表
    static function getAccessList($authId=null) 
    {
            if(null===$authId) {
                $authId = Session::get(C('USER_AUTH_KEY'));
            }
            //获取权限访问列表
            import("ORG.RBAC.AccessDecisionManager");
            $accessManager = new AccessDecisionManager();
            $accessList = $accessManager->getAccessList($authId);
            return $accessList;
    }

	// 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
	static function getRecordAccessList($authId=null,$module='') {
            if(null===$authId) {
                $authId = Session::get(C('USER_AUTH_KEY'));
            }
			if(empty($module)) {
				$module	=	MODULE_NAME;
			}
            //获取权限访问列表
            import("ORG.RBAC.AccessDecisionManager");
            $accessManager = new AccessDecisionManager();
            $accessList = $accessManager->getModuleAccessList($authId,$module);
            return $accessList;
	}

    //检查当前操作是否需要认证
    static function checkAccess() 
    {

        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if( C('USER_AUTH_ON') ){
            $notAuthModuleList = array(); 
            $requireAuthModuleList = array();
            if("" != C('REQUIRE_AUTH_MODULE')) {
                //需要认证的模块
                $requireAuthModuleList = explode(',',C('REQUIRE_AUTH_MODULE'));
            }else {
                //无需认证的模块
                $notAuthModuleList = explode(',',C('NOT_AUTH_MODULE'));  
            }
            //检查当前模块是否需要认证
            if((!empty($notAuthModuleList) && !in_array(MODULE_NAME,$notAuthModuleList)) || (!empty($requireAuthModuleList) && in_array(MODULE_NAME,$requireAuthModuleList))) {
                return true;
            }else {
                return false;
            }
        }
        return false;	
    }

    //权限认证的过滤器方法
    static function AccessDecision() 
    {
        //检查是否需要认证
        if(RBAC::checkAccess()) {
            //检查认证识别号
            if(!Session::is_set(C('USER_AUTH_KEY'))) {
                //跳转到认证网关
                redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
            }
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5(APP_NAME.MODULE_NAME.ACTION_NAME);
            if(!Session::is_setLocal('administrator')) {//管理员无需认证
                if(C('USER_AUTH_TYPE')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = RBAC::getAccessList();
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( Session::is_set($accessGuid)) {
                        return ;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = Session::get('_ACCESS_LIST');
                }
                if(!isset($accessList[strtoupper(APP_NAME)][strtoupper(MODULE_NAME)][strtoupper(ACTION_NAME)])) {
                    throw_exception(L('_VALID_ACCESS_'));
                }else {
                    Session::set($accessGuid,true);
                }
            }
        }
        return true;
    }	
}//end class
?>