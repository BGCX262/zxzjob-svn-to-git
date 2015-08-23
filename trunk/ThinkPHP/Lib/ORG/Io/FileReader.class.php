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
 * 文件流读取类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Io
 * @author    liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
class FileReader 
{
    var $_pos;
    var $_fd;
    var $_length;

    function __construct($filename) {
        if (file_exists($filename)) {
          $this->_length=filesize($filename);
          $this->_pos = 0;
          $this->_fd = fopen($filename,'rb');
          if (!$this->_fd) {
            $this->error = 3; // Cannot read file, probably permissions
            return false;
          }
        } else {
          $this->error = 2; // File doesn't exist
          return false;
        }
    }

    function read($bytes) {
        if ($bytes) {
          fseek($this->_fd, $this->_pos);
          $data = fread($this->_fd, $bytes);
          $this->_pos = ftell($this->_fd);
          
          return $data;
        } else return '';
    }

    function seekto($pos) {
        fseek($this->_fd, $pos);
        $this->_pos = ftell($this->_fd);
        return $this->_pos;
    }

    function currentpos() {
        return $this->_pos;
    }

    function length() {
        return $this->_length;
    }

    function close() {
        fclose($this->_fd);
    }

}
?>