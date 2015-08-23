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
 * ResultIterator类 用于实现数据库的延迟加载
 * TODO 实现SQL的动态组装
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ResultIterator extends Base implements IteratorAggregate
{
	// 执行查询的SQL
	private $sql	=	null;
	// 查询的对象封装
	private $map	=	null;
	// 数据库操作对象
	private $db	=	null;
	// 返回的查询数据的数目
	private $size	=	null;
	// 返回的查询数据
	private $data	=	null;

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $array  初始化数组元素
     +----------------------------------------------------------
     */
    public function __construct($sql='')
    {
		$this->sql	=	$sql;
    }

    /**
     +----------------------------------------------------------
     * 获取Iterator因子
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return Iterate
     +----------------------------------------------------------
     */
    public function getIterator()
    {
		$result	=	$this->getData();
		return $result;
    }
  
    /**
     +----------------------------------------------------------
     * 实际获取查询结果
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return ArrayObject
     +----------------------------------------------------------
     */
	public function getData() {
		if(empty($this->data)) {
			$this->db	=	Db::getInstance();
			$this->data	=	$this->db->query($this->sql);
			$this->size	 =	 $this->data->count();
		}
		return $this->data;
	}

    /**
     +----------------------------------------------------------
     * 获取查询结果数目
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
	public function size() {
		if(empty($this->size)) {
			$this->getData();
		}
		return $this->size;
	}

};
?>