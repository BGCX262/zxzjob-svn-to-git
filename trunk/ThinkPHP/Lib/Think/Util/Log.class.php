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
 * 日志处理类
 * 支持下面的日志类型
 * WEB_LOG_DEBUG 调试信息
 * WEB_LOG_ERROR 错误信息
 * SQL_LOG_DEBUG SQL调试
 * 分别对象的默认日志文件为
 * 调试日志文件 systemOut.log
 * 错误日志文件  systemErr.log
 * SQL日志文件  systemSql.log
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Log extends Base
{//类定义开始
	
	static $log	=	array();

    /**
     +----------------------------------------------------------
     * 日志写入
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @param string $message 日志信息
     * @param string $type  日志类型
     * @param string $file  写入文件 默认取定义日志文件
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static function write($message,$type=WEB_LOG_ERROR,$file='')
    {
        $now = date('[ y-m-d H:i:s ]');
        switch($type){
            case WEB_LOG_DEBUG:
                $logType ='[调试]';
                $destination = $file == ''? LOG_PATH.date('y_m_d')."_systemOut.log" : $file;
                break;
			case SQL_LOG_DEBUG:
				// 调试SQL记录
                $logType ='[SQL]';
                $destination = $file == ''? LOG_PATH.date('y_m_d')."_systemSql.log" : $file;
                break;
			case WEB_LOG_ERROR:
                $logType ='[错误]';
                $destination = $file == ''? LOG_PATH.date('y_m_d')."_systemErr.log" : $file;
				break;
        }
        if(!is_writable(LOG_PATH)){
            halt(L('_FILE_NOT_WRITEABLE_').':'.$destination);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
		if(file_exists($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) ){
			  rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
		}        	
        error_log("$now\n$message\n", FILE_LOG,$destination );
		self::$log[$type][]	=	$message;
		clearstatcache();
    }

}//类定义结束
?>