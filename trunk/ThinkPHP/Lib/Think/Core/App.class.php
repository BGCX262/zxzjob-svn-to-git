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

import("Think.Util.Config");
import("Think.Util.Session");
import("Think.Util.Cookie");

/**
 +------------------------------------------------------------------------------
 * ThinkPHP 应用程序类 执行应用过程管理
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class App extends Base 
{//类定义开始

    /**
     +----------------------------------------------------------
     * 应用程序名称
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $name ;

    /**
     +----------------------------------------------------------
     * 应用程序标识号
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $id;

    /**
     +----------------------------------------------------------
     * 应用程序调试信息
     +----------------------------------------------------------
     * @var array
     * @access static 
     +----------------------------------------------------------
     */
    static public $debug = array();

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $name 应用名称
     * @param string $id  应用标识号
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __construct($name='App',$id='')
    {    
        $this->name = $name;
        $this->id   =  $id ;//| create_guid();
    }

    /**
     +----------------------------------------------------------
     * 取得应用实例对象 
     * 静态方法
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return App
     +----------------------------------------------------------
     */
    public static function  getInstance() 
    {
        return get_instance_of(__CLASS__);
    }

    /**
     +----------------------------------------------------------
     * 应用程序初始化
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function init()
    {
        // 设定错误和异常处理
        set_error_handler(array(&$this,"appError"));
		set_exception_handler(array(&$this,"appException"));

		// 加载惯例配置文件
		C(array_change_key_case(include THINK_PATH.'/Common/convention.php'));

        // 加载项目配置文件
        // 支持App.* 作为配置文件
        // 自动生成_appConfig.php 配置缓存
        $this->loadConfig('App',CONFIG_PATH,'_config.php');

		// 如果是调试模式加载调试模式配置文件
		if(C('DEBUG_MODE')) {
			// 加载系统默认的开发模式配置文件
			C(array_change_key_case(include THINK_PATH.'/Common/debug.php'));
			if(file_exists(CONFIG_PATH.'_debug.php')) {
				// 允许项目增加开发模式配置定义
				C(array_change_key_case(include CONFIG_PATH.'_debug.php'));
			}
		}

        // 设置系统时区 PHP5支持
        if(function_exists('date_default_timezone_set')) 
            date_default_timezone_set(C('TIME_ZONE'));

        if('FILE' != strtoupper(C('SESSION_TYPE'))) {
			// 其它方式Session支持 目前支持Db 通过过滤器方式扩展
			import("Think.Util.Filter");
			Filter::load(ucwords(C('SESSION_TYPE')).'Session');
        }
        // Session初始化
        Session::start();   

        // 加载插件 必须在Session开启之后加载插件
		if(C('THINK_PLUGIN_ON')) {
	        $this->loadPlugIn();  
		}

        // 应用调度过滤器
        // 如果没有加载任何URL调度器
        // 默认只支持 QUERY_STRING 方式
        // 例如 ?m=user&a=add
		if(C('DISPATCH_ON')) {
			if( 'Think'== C('DISPATCH_NAME') ) {
				// 使用内置的ThinkDispatcher调度器
				import('Think.Core.Dispatcher');
				Dispatcher::dispatch();
			}else{
				// 加载第三方调度器
		        apply_filter('app_dispatch');
			}
		}

        if(!defined('PHP_FILE')) {
            // PHP_FILE 由内置的Dispacher定义
            // 如果不使用该插件，需要重新定义
        	define('PHP_FILE',_PHP_FILE_);
        }

        // 取得模块和操作名称 如果有伪装 则返回真实的名称
		// 可以在Dispatcher中定义获取规则
		if(!defined('MODULE_NAME')) define('MODULE_NAME',   $this->getModule());       // Module名称
        if(!defined('ACTION_NAME')) define('ACTION_NAME',   $this->getAction());        // Action操作

		// 加载模块配置文件 并自动生成配置缓存文件
		$this->loadConfig('m_'.MODULE_NAME,CONFIG_PATH,'m_'.MODULE_NAME.'Config.php',false);

		//	启用页面防刷新机制
		if(C('LIMIT_RESFLESH_ON')) {
			$guid	=	md5($_SERVER['PHP_SELF']);
			// 检查页面刷新间隔
			if(Session::is_set('_last_visit_time_'.$guid) && Session::get('_last_visit_time_'.$guid)>time()-C('LIMIT_REFLESH_TIMES')) {
				// 页面刷新读取浏览器缓存
				header('HTTP/1.1 304 Not Modified');
				exit;
			}else{
				// 缓存当前地址访问时间
				Session::set('_last_visit_time_'.$guid,time());
				header('Last-Modified:'.(date('D,d M Y H:i:s',time()-C('LIMIT_REFLESH_TIMES'))).' GMT');
			}
		}

        // 加载项目公共文件
		if(file_exists(APP_PATH.'/Common/common.php')) {
	       	include APP_PATH.'/Common/common.php';
		}

        // 系统检查
		$this->checkLanguage();     //语言检查
        $this->checkTemplate();     //模板检查

		if(C('USER_AUTH_ON')) {
			// 启用权限认证 调用RBAC组件
			import('ORG.RBAC.RBAC');
			RBAC::AccessDecision();
		}

		if(C('HTML_CACHE_ON')) {
			import('Think.Util.HtmlCache');
			HtmlCache::readHTMLCache();
		}
        // 应用初始化过滤插件
        apply_filter('app_init');

		// 记录应用初始化时间
		$GLOBALS['_initTime'] = array_sum(explode(' ', microtime()));

        return ;
    }

    /**
     +----------------------------------------------------------
     * 获得实际的模块名称
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function getModule()
    {
        $module = isset($_POST[C('VAR_MODULE')]) ? 
            $_POST[C('VAR_MODULE')] :
            (isset($_GET[C('VAR_MODULE')])? $_GET[C('VAR_MODULE')]:'');
        // 如果 $module 为空，则赋予默认值
        if (empty($module)) $module = C('DEFAULT_MODULE'); 
		// 检查组件模块
		if(strpos($module,':')) {
			// 记录完整的模块名
			define('C_MODULE_NAME',$module);
			$array	=	explode(':',$module);
			// 实际的模块名称
			$module	=	array_pop($array);
		}
		// 检查模块URL伪装
		if(C('MODULE_REDIRECT')) {
            $res = preg_replace('@(\w+):([^,\/]+)@e', '$modules[\'\\1\']="\\2";', C('MODULE_REDIRECT'));
			if(array_key_exists($module,$modules)) {
				// 记录伪装的模块名称
				define('P_MODULE_NAME',$module);
				$module	=	$modules[$module];
			}
		}
        return $module; 
    }

    /**
     +----------------------------------------------------------
     * 获得实际的操作名称
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function getAction()
    {
        $action   = isset($_POST[C('VAR_ACTION')]) ? 
            $_POST[C('VAR_ACTION')] : 
            (isset($_GET[C('VAR_ACTION')])?$_GET[C('VAR_ACTION')]:'');
        // 如果 $action 为空，则赋予默认值
        if (empty($action)) $action = C('DEFAULT_ACTION');
		// 检查操作链
		if(strpos($action,':')) {
			// 记录完整的模块名
			define('C_ACTION_NAME',$action);
			$array	=	explode(':',$action);
			// 实际的模块名称
			$action	=	array_pop($array);
		}
		// 检查操作URL伪装
		if(C('ACTION_REDIRECT')) {
            $res = preg_replace('@(\w+):([^,\/]+)@e', '$actions[\'\\1\']="\\2";', C('ACTION_REDIRECT'));
			if(array_key_exists($action,$actions)) {
				// 记录伪装的操作名称
				define('P_ACTION_NAME',$action);
				$action	=	$actions[$action];
			}
		}
        return $action; 
    }

    /**
     +----------------------------------------------------------
     * 加载配置文件
     * 支持XML、INI和PHP数组、对象和常量定义文件
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function loadConfig($name,$path,$configFile) 
    {
        //加载项目配置文件
        $cacheFile =   $path.$configFile;
        //如果存在系统生成的配置缓存文件，则直接引入，无需再进行配置文件解析
        if(!file_exists($cacheFile)) {
            //寻找匹配的项目配置文件
            //支持XML、INI和PHP数组、对象和常量定义文件
            $list = glob($path.$name.'.*');
            if(!empty($list)) {
                $config  = Config::getInstance();
                //分析第一个配置文件
                $result  = $config->parse($list[0]);
                // 生成配置缓存文件供下次加载
				// 默认采用PHP数组方式缓存
				$result->toArray($cacheFile);
            }
        }
		if(file_exists($cacheFile)) {
			C(array_change_key_case(include $cacheFile));
		}
    }

    /**
     +----------------------------------------------------------
     * 语言检查
     * 检查浏览器支持语言，并自动加载语言包
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function checkLanguage()
    {
        //检测浏览器支持语言
        $langSet = detect_browser_language();
        // setlocale操作比较费时，暂时屏蔽 
        //setlocale(LC_ALL, $langSet);       

        // 定义当前语言
        define('LANG_SET',$langSet);
		// 加载语言类
		import("Think.Util.Language");
		// 加载框架语言包
        if (!file_exists(THINK_PATH.'/Lang/'.LANG_SET.'.php')){
			Language::load(THINK_PATH.'/Lang/'.LANG_SET.'.php');
		}else{
			Language::load(THINK_PATH.'/Lang/'.C('DEFAULT_LANGUAGE').'.php');       
		}
        // 读取项目（公共）语言包
		if (file_exists(LANG_PATH.LANG_SET.'.php'))
	        Language::load(LANG_PATH.LANG_SET.'.php');  

		// 读取当前模块的语言包
		if (file_exists(LANG_PATH.strtolower(MODULE_NAME).'_'.LANG_SET.'.php'))
	        Language::load(LANG_PATH.strtolower(MODULE_NAME).'_'.LANG_SET.'.php');  

		// 缓存语言变量
		L(Language::$_lang);
        return ;
    }

    /**
     +----------------------------------------------------------
     * 模板检查，如果不存在使用默认
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    private function checkTemplate()
    {
        if ( isset($_GET[C('VAR_TEMPLATE')]) ) {
            $templateSet = $_GET[C('VAR_TEMPLATE')];
			Cookie::set('t',$templateSet);
        } else {
			if(Cookie::is_set('t')) {
				$templateSet = Cookie::get('t');
            }
            else {
                $templateSet =    C('DEFAULT_TEMPLATE');
				Cookie::set('t',$templateSet);
            }
        }
        if (!is_dir(TMPL_PATH.$templateSet)) {
            //模版不存在的话，使用默认模版
            $templateSet =    C('DEFAULT_TEMPLATE');
        }
        //模版名称
        define('TEMPLATE_NAME',$templateSet); 
        // 当前模版路径
        define('TEMPLATE_PATH',TMPL_PATH.TEMPLATE_NAME); 
        //当前网站地址
        define('__ROOT__',WEB_URL);
        //当前项目地址
        define('__APP__',PHP_FILE);

		$module	=	defined('P_MODULE_NAME')?P_MODULE_NAME:MODULE_NAME;
		$action		=	defined('P_ACTION_NAME')?P_ACTION_NAME:ACTION_NAME;

        //模块地址
        define('__URL__',PHP_FILE.'/'.$module);
        //当前操作地址
        define('__ACTION__',PHP_FILE.'/'.$module.'/'.$action);  
        //当前页面地址
        define('__SELF__',$_SERVER['PHP_SELF']);
        // 默认加载的模板文件名
		if(defined('C_MODULE_NAME')) {
	        C('TMPL_FILE_NAME',TEMPLATE_PATH.'/'.str_replace(':','/',C_MODULE_NAME).'/'.ACTION_NAME.C('TEMPLATE_SUFFIX'));
	        define('__CURRENT__', WEB_URL.'/'.APP_NAME.'/'.TMPL_DIR.'/'.TEMPLATE_NAME.'/'.str_replace(':','/',C_MODULE_NAME));
		}else{
	        C('TMPL_FILE_NAME',TEMPLATE_PATH.'/'.MODULE_NAME.'/'.ACTION_NAME.C('TEMPLATE_SUFFIX'));
	        define('__CURRENT__', WEB_URL.'/'.APP_NAME.'/'.TMPL_DIR.'/'.TEMPLATE_NAME.'/'.MODULE_NAME);
		}

        //网站公共文件地址
        define('WEB_PUBLIC_URL', WEB_URL.'/Public');
        //项目公共文件地址
        define('APP_PUBLIC_URL', WEB_URL.'/'.APP_NAME.'/'.TMPL_DIR.'/'.TEMPLATE_NAME.'/Public'); 

        return ;
    }

    /**
     +----------------------------------------------------------
     * 加载插件
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function loadPlugIn()
    {
        // 如果存在缓存插件数据，直接包含
        if(file_exists(CONFIG_PATH.'_plugins.php')) {
            $plugins    = include CONFIG_PATH.'_plugins.php';
        }else {
            // 检查插件数据
            $common_plugins = get_plugins(THINK_PATH.'/PlugIns','Think');// 公共插件
            $app_plugins = get_plugins();// 项目插件
            // 合并插件数据
            $plugins    = array_merge($common_plugins,$app_plugins);   
            // 缓存插件数据
            $content  = "<?php\nreturn ".var_export($plugins,true).";\n?>";
            file_put_contents(CONFIG_PATH.'_plugins.php',$content);              
        }

        //加载有效插件文件
        foreach($plugins as $key=>$val) {
            include_cache($val['file']);
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * 执行应用程序
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function exec()
    {
		import("Think.Core.Action");
        //创建Action控制器实例
		if(defined('C_MODULE_NAME')) {
			$module  =  A(C_MODULE_NAME);
		}else{
			$module  =  A(MODULE_NAME);
		}
		if(!$module) {
			// 模块不存在
			if(C('DEBUG_MODE')) {
				// 调试模式 抛出异常
				throw_exception(L('_MODULE_NOT_EXIST_').MODULE_NAME);    
			}else{
				// 部署模式重定向到默认模块
				$url	=	__APP__.'/'.C('DEFAULT_MODULE');
				redirect($url);
			}
		}

        //获取当前操作名
        $action = ACTION_NAME.C('ACTION_SUFFIX');
		if(defined('C_ACTION_NAME')) {
			// 执行操作链 最多只能有一个输出
			$actionList	=	explode(':',C_ACTION_NAME);
			foreach ($actionList as $action){
				$module->$action();
			}
		}else{
			//如果存在前置操作，首先执行
			if (method_exists($module,'_before_'.$action)) {    
				$module->{'_before_'.$action}();
			}
			//执行操作
			$module->{$action}();
			//如果存在后置操作，继续执行
			if (method_exists($module,'_after_'.$action)) {
				$module->{'_after_'.$action}();
			}  
		}
        // 执行应用结束过滤器
        apply_filter('app_end');

		// 写入错误日志
        if(C('WEB_LOG_RECORD'))
            system_out(trim(implode('',self::$debug)));

        return ;
    }

    /**
     +----------------------------------------------------------
     * 运行应用实例 入口文件使用的快捷方法
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function run() {
		$this->init();
		$this->exec();
		return ;
	}

    /**
     +----------------------------------------------------------
     * 自定义异常处理
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $e 异常对象
     +----------------------------------------------------------
     */
    public function appException($e)
    {
        halt($e->__toString());
    }

    /**
     +----------------------------------------------------------
     * 自定义错误处理
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function appError($errno, $errstr, $errfile, $errline)
    {
      switch ($errno) {
          case E_ERROR: 
          case E_USER_ERROR: 
              $errorStr = "错误：[$errno] $errstr ".basename($errfile)." 第 $errline 行.\n";
              if(C('WEB_LOG_RECORD')){
                 system_out($errorStr);
              }
              halt($errorStr);
              break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          default: 
			$errorStr = "注意：[$errno] $errstr ".basename($errfile)." 第 $errline 行.\n";
			self::$debug[] = $errorStr;
             break;
      }
    }

};//类定义结束
?>