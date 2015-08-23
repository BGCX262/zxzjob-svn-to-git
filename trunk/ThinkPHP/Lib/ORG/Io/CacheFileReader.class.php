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

import('Think.Util.StringReader');
/**
 +------------------------------------------------------------------------------
 * 文件缓存读取类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Io
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CachedFileReader extends StringReader 
{
    function __construct($filename) {
        if (file_exists($filename)) {

          $length=filesize($filename);
          $fd = fopen($filename,'rb');

          if (!$fd) {
        $this->error = 3; // Cannot read file, probably permissions
        return false;
          }
          $this->_str = fread($fd, $length);
          fclose($fd);

        } else {
          $this->error = 2; // File doesn't exist
          return false;
        }
    }
}
?>