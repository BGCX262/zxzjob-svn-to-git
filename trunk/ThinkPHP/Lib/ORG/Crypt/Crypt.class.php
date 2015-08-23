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
 * Crypt 加密实现类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Crypt
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Crypt extends Base
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
    function encrypt($str,$key,$toBase64=false){ 
        $r = md5($key); 
        $c=0; 
        $v = ""; 
        for ($i=0;$i<strlen($str);$i++){ 
         if ($c==strlen($r)) $c=0; 
         $v.= substr($r,$c,1) . 
             (substr($str,$i,1) ^ substr($r,$c,1)); 
         $c++; 
        } 
        if($toBase64) {
            return base64_encode(Crypt::ed($v,$key)); 
        }else {
            return Crypt::ed($v,$key); 
        }
        
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
    function decrypt($str,$key,$toBase64=false) { 
        if($toBase64) {
            $str = Crypt::ed(base64_decode($str),$key); 
        }else {
            $str = Crypt::ed($str,$key); 
        }
        $v = ""; 
        for ($i=0;$i<strlen($str);$i++){ 
         $md5 = substr($str,$i,1); 
         $i++; 
         $v.= (substr($str,$i,1) ^ $md5); 
        } 
        return $v; 
    } 


   function ed($str,$key) { 
      $r = md5($key); 
      $c=0; 
      $v = ""; 
      for ($i=0;$i<strlen($str);$i++) { 
         if ($c==strlen($r)) $c=0; 
         $v.= substr($str,$i,1) ^ substr($r,$c,1); 
         $c++; 
      } 
      return $v; 
   } 
}
?>