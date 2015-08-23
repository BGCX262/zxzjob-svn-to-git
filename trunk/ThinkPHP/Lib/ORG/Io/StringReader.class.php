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
 * String 流读取类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Io
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class StringReader 
{
    var $_pos;
    var $_str;

    function __construct($str='') 
    {
        $this->_str = $str;
        $this->_pos = 0;
    }

    function read($bytes)
    {
        $data = substr($this->_str, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if (strlen($this->_str)<$this->_pos)
          $this->_pos = strlen($this->_str);

        return $data;
    }

    function seekto($pos)
    {
        $this->_pos = $pos;
        if (strlen($this->_str)<$this->_pos)
          $this->_pos = strlen($this->_str);
        return $this->_pos;
    }

    function currentpos() 
    {
        return $this->_pos;
    }

    function length()
    {
        return strlen($this->_str);
    }

}
?>