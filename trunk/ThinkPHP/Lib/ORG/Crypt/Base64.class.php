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
 * Base64 加密实现类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Crypt
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Base64 extends Base
{
	
    /**
     +----------------------------------------------------------
     * 加密字符串
     * 
     +----------------------------------------------------------
     * @access static 
     +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    function encrypt($data,$key) 
    { 
        $key    =   md5($key); 
        $data   =   base64_encode($data); 
        $x=0; 
        for ($i=0;$i<strlen($data);$i++) 
        { 
            if ($x==strlen($key)) $x=0; 
            $char   .=substr($key,$x,1); 
            $x++;     
        } 
        for ($i=0;$i<strlen($data);$i++) 
        { 
            $str    .=chr(ord(substr($data,$i,1))+(ord(substr($char,$i,1)))%256);     
        } 
        return $str; 
    }       
    /**
     +----------------------------------------------------------
     * 解密字符串
     * 
     +----------------------------------------------------------
     * @access static 
     +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    function decrypt($data,$key) 
    { 
        $key    =   md5($key); 
        $x=0; 
        for ($i=0;$i<strlen($data);$i++) 
        { 
            if ($x==strlen($key)) $x=0; 
            $char   .=substr($key,$x,1); 
            $x++;     
        } 
        for ($i=0;$i<strlen($data);$i++) 
        { 
            if (ord(substr($data,$i,1))<ord(substr($char,$i,1))) 
            { 
                $str    .=chr((ord(substr($data,$i,1))+256)-ord(substr($char,$i,1)));     
            } 
            else 
            { 
                $str    .=chr(ord(substr($data,$i,1))-ord(substr($char,$i,1))); 
            } 
        } 
        return base64_decode($str); 
    } 
}
?>