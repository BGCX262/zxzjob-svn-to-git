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

import("Think.Db.Db");

define('HAS_ONE',1);
define('BELONGS_TO',2);
define('HAS_MANY',3);
define('MANY_TO_MANY',4);

define('MUST_TO_VALIDATE',1);	 // 必须验证
define('EXISTS_TO_VAILIDATE',0);		// 表单存在字段则验证
define('VALUE_TO_VAILIDATE',2);		// 表单值不为空则验证

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Model模型类 抽象类
 * 实现了ORM和ActiveRecords模式
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
abstract class Model extends Base  implements IteratorAggregate
{
	// 数据库底层操作对象
	protected $db ;

	// 数据表前缀
	protected $tablePrefix	=	'';

	// 数据表后缀
	protected $tableSuffix = '';

	// 模型名称
	protected $name;

	// 数据表名（不包含表前缀）
	protected $tableName;

	// 实际数据表名（包含表前缀）
	protected $trueTableName;

	// 字段信息
	protected $fields = array();

	// 字段类型信息
	protected $type	 =	 array();

	// 数据信息
	protected $data	=	array();

	// 数据列表信息
	protected $dataList	=	array();

	// 上次错误信息
	protected $error;

	// 包含的聚合对象
	protected $aggregation = array();
	// 是否为复合对象
	protected $composite = false;
	// 是否为静态模型
	protected $staticModel = false;
	// 是否为视图模型
	protected $viewModel = false;

	// 乐观锁
	protected $optimLock = 'lock_version';
	// 悲观锁
	protected $pessimisticLock = false;

	protected $autoSaveRelations	= false;		// 自动关联保存
	protected $autoDelRelations		= false;		// 自动关联删除
	protected $autoAddRelations	= false;		// 自动关联写入
	protected $autoReadRelations	= false;		// 自动关联查询
	protected $lazyQuery	=	false;					// 是否启用惰性查询

	// 自动写入时间戳
	protected $autoCreateTimestamps = array('create_at','create_on','cTime');
	protected $autoUpdateTimestamps = array('update_at','update_on','mTime');
	protected $autoTimeFormat = '';

	protected $blobFields	 =	 null;
	protected $blobValues = null;

	/**
     +----------------------------------------------------------
     * 架构函数 
	 * 取得DB类的实例对象 数据表字段检查
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 要创建的数据对象内容
     +----------------------------------------------------------
     */
	public function __construct($data='')
	{
		// 模型初始化
		$this->_initialize();
		// 模型名称获取
		$this->name	=	$this->getModelName();
		// 如果不是复合对象进行数据库初始化操作
		if(!$this->composite) {
			// 静态模型
			if($this->staticModel && S($this->name)) {
				// 获取数据后生成静态缓存
				$this->dataList	=	S($this->name);
			}else{
				// 获取数据库操作对象
				if(!empty($this->connection)) {
					// 当前模型有独立的数据库连接信息
					$this->db = new Db($this->connection);
				}else{
					$this->db = Db::getInstance();
				}
				// 设置数据库的返回数据格式
				$this->db->resultType	=	C('DATA_RESULT_TYPE');
				// 设置表前后缀
				$this->tablePrefix = C('DB_PREFIX')|'';
				$this->tableSuffix = C('DB_SUFFIX')|'';
				// 数据表字段检测
				$this->_checkTableInfo();
				// 静态模型
				if($this->staticModel) {
					// 获取数据后生成静态缓存
					S($this->name,$this->findAll());
				}
			}
		}
		// 如果有data数据进行实例化，则创建数据对象
		if(!empty($data)) {
			$this->create($data);
		}
	}

	/**
     +----------------------------------------------------------
     * 取得模型实例对象
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @return mixed 返回数据模型实例
     +----------------------------------------------------------
     */
	public static function getInstance()
	{
		return get_instance_of(__CLASS__);
	}

	/**
     +----------------------------------------------------------
     * 设置数据对象的值 （魔术方法）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param string $name 名称
     * @param mixed $value 值
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function __set($name,$value) {
		// 设置数据对象属性
		$this->data[$name]	=	$value;
	}

	/**
     +----------------------------------------------------------
     * 获取数据对象的值 （魔术方法）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param string $name 名称
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	private function __get($name) {
		if(isset($this->data[$name])) {
			return $this->data[$name];
		}elseif(property_exists($this,$name)){
			return $this->$name;
		}else{
			return null;
		}
	}

	/**
     +----------------------------------------------------------
     * 数据对象的属性是否设置 （魔术方法）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param string $name 名称
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	private function __isset($name) {
		return isset($this->data[$name]);
	}

	/**
     +----------------------------------------------------------
     * unset数据对象的属性 （魔术方法）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param string $name 名称
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	private function __unset($name) {
		unset($this->data[$name]);
	}

	/**
     +----------------------------------------------------------
     * 利用__call方法重载 实现一些特殊的Model方法 （魔术方法）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param string $method 方法名称
     * @param mixed $args 调用参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	private function __call($method,$args) {
		if(strtolower(substr($method,0,5))=='getby') {
			// 根据某个字段获取记录
			$field	 =	 strtolower(substr($method,5));
			if(in_array($field,$this->fields,true)) {
				array_unshift($args,$field);
				return call_user_func_array(array(&$this, 'getBy'), $args);
			}
		}elseif(strtolower(substr($method,0,6))=='getsby') {
			// 根据某个字段获取记录
			$field	 =	 strtolower(substr($method,6));
			if(in_array($field,$this->fields,true)) {
				array_unshift($args,$field);
				return call_user_func_array(array(&$this, 'getByAll'), $args);
			}
		}elseif(strtolower(substr($method,0,3))=='get'){
			// getter 模拟 仅针对数据对象
			$field	 =	 strtolower(substr($method,3));
			return $this->__get($field);
		}elseif(strtolower(substr($method,0,3))=='top'){
			// 获取前N条记录
			$count = substr($method,3);
			array_unshift($args,$count);
			return call_user_func_array(array(&$this, 'topN'), $args);
		}elseif(strtolower(substr($method,0,5))=='setby'){
			// 保存记录的某个字段
			$field	 =	 strtolower(substr($method,5));
			if(in_array($field,$this->fields,true)) {
				array_unshift($args,$field);
				return call_user_func_array(array(&$this, 'setField'), $args);
			}
		}elseif(strtolower(substr($method,0,3))=='set'){
			// setter 模拟 仅针对数据对象
			$field	 =	 strtolower(substr($method,3));
			array_unshift($args,$field);
			return call_user_func_array(array(&$this, '__set'), $args);
		}elseif(strtolower(substr($method,0,5))=='delby'){
			// 根据某个字段删除记录
			$field	 =	 strtolower(substr($method,5));
			if(in_array($field,$this->fields,true)) {
				array_unshift($args,$field);
				return call_user_func_array(array(&$this, 'deleteBy'), $args);
			}
		}elseif(strtolower(substr($method,0,3))=='del'){
			// unset 数据对象
			$field	 =	 strtolower(substr($method,3));
			if(in_array($field,$this->fields,true)) {
				if(isset($this->data[$field])) {
					unset($this->data[$field]);
				}
			}
		}elseif(strtolower(substr($method,0,5))=='isset'){
			// isset 数据对象
			$field	 =	 strtolower(substr($method,5));
			if(in_array($field,$this->fields,true)) {
				array_unshift($args,$field);
				return call_user_func_array(array(&$this, '__isset'), $args);
			}
		}
		return;
	}

	// 回调方法 初始化模型
	protected function _initialize() {}

	/**
     +----------------------------------------------------------
     * 数据库Create操作入口
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param array $data 要create的数据
     * @param boolean $autoLink 是否关联写入
     +----------------------------------------------------------
     * @return false|integer
     +----------------------------------------------------------
     */
	private function _create(&$data,$autoLink=false,$multi=false) {
		// 前置调用
		if(!$this->_before_create($data)) {
			return false;
		}
		// 插入数据库
		if(false === $this->db->add($data,$this->getTableName(),$multi)){
			// 数据库插入操作失败
			$this->error = L('_OPERATION_WRONG_');
			return false;
		}else {
			$insertId	=	$this->getLastInsID();
			$data[$this->getPk()]	=	 $insertId;
			$this->saveBlobFields($data);
			// 保存关联记录
			if ($this->autoAddRelations || $autoLink){
				$this->opRelation('ADD',$data);
			}
			// 后置调用
			$this->_after_create($data);
			//成功后返回插入ID
			return $insertId;
		}
	}
	// Create回调方法 before after 
	protected function _before_create(&$data) {return true;}
	protected function _after_create(&$data) {}

	/**
     +----------------------------------------------------------
     * 数据库Update操作入口
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param array $data 要create的数据
     * @param mixed $where 更新条件
     * @param string $limit limit
     * @param string $order order
     * @param boolean $autoLink 是否关联写入
     * @param boolean $lock 是否加锁
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	private function _update(&$data,$where,$limit='',$order='',$autoLink=false,$lock=false) {
		// 前置调用
		if(!$this->_before_update($data,$where)) {
			return false;
		}
		$lock	 =	 ($this->pessimisticLock || $lock);
		$where	=	$this->checkCondition($where);
		if(false === $this->db->save($data,$this->getTableName(),$where,$limit,$order,$lock)){
			$this->error = L('_OPERATION_WRONG_');
			return false;
		}else {
			$this->saveBlobFields($data);
			// 保存关联记录
			if ($this->autoSaveRelations || $autoLink){
				$this->opRelation('SAVE',$data);
			}
			// 后置调用
			$this->_after_update($data,$where);
			return true;
		}
	}
	// 更新回调方法 
	protected function _before_update(&$data,$where) {return true;}
	protected function _after_update(&$data,$where) {}

	/**
     +----------------------------------------------------------
     * 数据库Read操作入口
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param mixed $condition 查询条件
     * @param string $fields 查询字段
     * @param boolean $all 是否返回多个数据
     * @param string $order 
     * @param string $limit 
     * @param string $group 
     * @param string $having 
     * @param string $join 
     * @param boolean $cache 是否查询缓存
     * @param boolean $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     * @param boolean $lock 是否加锁
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	private function _read($condition,$fields='*',$all=false,$order='',$limit='',$group='',$having='',$join='',$cache=false,$relation=false,$lazy=false,$lock=false) {
		// 前置调用
		if(!$this->_before_read($condition)) {
			// 如果返回false 中止
			return false;
		}
		if($all) {
			$identify   = $this->name.'List_'.to_guid_string(func_get_args());
		}else{
			$identify   = $this->name.'_'.to_guid_string($condition);
		}
		if($cache) {//启用动态数据缓存
			$result  =  S($identify);
			if(false !== $result) {
				if(!$all) {
					$this->cacheLockVersion($result);
				}
				// 后置调用
				$this->_after_read($condition,$result);
				return $result;
			}
		}
		if($this->viewModel) {
			$condition	=	$this->checkCondition($condition);
			$fields	=	$this->checkFields($fields);
			$order	=	$this->checkOrder($order);
			$group	=	$this->checkGroup($group);
		}
		$lazy	 =	 ($this->lazyQuery || $lazy);
		$lock	 =	 ($this->pessimisticLock || $lock);
		$rs = $this->db->find($condition,$this->getTableName(),$fields,$order,$limit,$group,$having,$join,$cache,$lazy,$lock);
		$result	=	$this->rsToVo($rs,$all,0,$relation);
		// 后置调用
		$this->_after_read($condition,$result);
		if($result && $cache) {
			S($identify,$result);
		}
		return $result;
	}
	// Read回调方法
	protected function _before_read(&$condition) {return true;}
	protected function _after_read(&$condition,$result) {}

	/**
     +----------------------------------------------------------
     * 数据库Delete操作入口
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param mixed $data 删除的数据
     * @param mixed $condition 查询条件
     * @param string $limit 
     * @param string $order 
     * @param boolean $autoLink 是否关联删除
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	private function _delete($data,$where,$limit=0,$order='',$autoLink=false) {
		// 前置调用
		if(!$this->_before_delete($where)) {
			return false;
		}
		$where	=	$this->checkCondition($where);
		$result=    $this->db->remove($where,$this->getTableName(),$limit,$order);
		if(false === $result ){
			$this->error =  L('_OPERATION_WRONG_');
			return false;
		}else {
			// 删除Blob数据
			$this->delBlobFields($data);
			// 删除关联记录
			if ($this->autoDelRelations || $autoLink){
				$this->opRelation('DEL',$data);
			}
			// 后置调用
			$this->_after_delete($where);
			//返回删除记录个数
			return $result;
		}
	}
	// Delete回调方法
	protected function _before_delete(&$where) {return true;}
	protected function _after_delete(&$where) {}

	/**
     +----------------------------------------------------------
     * 数据库Query操作入口(使用SQL语句的Query）
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @param mixed $sql 查询的SQL语句
     * @param boolean $cache 是否使用查询缓存
     * @param boolean $lazy 是否惰性查询
     * @param boolean $lock 是否加锁
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	private function _query($sql,$cache=false,$lazy=false,$lock=false) {
		if(empty($sql)) return false;
		if(!$this->_before_query($sql)) {
			return false;
		}
		if($cache) {//启用动态数据缓存
			$identify   = md5($sql);
			$result =   S($identify);
			if(false !== $result) {
				return $result;
			}
		}
		$lazy	 =	 ($this->lazyQuery || $lazy);
		$lock	 =	 ($this->pessimisticLock || $lock);
		$result =   $this->db->query($sql,$cache,$lazy,$lock);
		if($cache)    S($identify,$result);
		$this->_after_query($result);
		return $result;
	}
	// Query回调方法
	protected function _before_query(&$sql) {return true;}
	protected function _after_query(&$result) {}

	/**
     +----------------------------------------------------------
     * 数据表字段检测 并自动缓存
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	private function _checkTableInfo() {
		// 自动记录数据表信息
		// 只在第一次执行记录
		if(empty($this->fields)) {
			// 如果数据表字段没有定义则自动获取
			$identify	=	$this->name.'_fields';
			$this->fields = S($identify);
			if(!$this->fields) {
				$this->flush();
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 强制刷新数据表信息
     +----------------------------------------------------------
     * @access private 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function flush() {
		// 缓存不存在则查询数据表信息
		if($this->viewModel) {
			// 缓存视图模型的字段信息
			$this->fields = array();
			$this->fields['_autoInc'] = false;
			foreach ($this->viewFields as $name=>$val){
				foreach ($val as $key=>$field){
					if(is_numeric($key)) {
						$this->fields[]	=	$name.'.'.$field;
					}else{
						$this->fields[]	=	$name.'.'.$key;
					}
				}
			}
		}else{
			$fields	=	$this->db->getFields($this->getTableName());
			$this->fields	=	array_keys($fields);
			$this->fields['_autoInc'] = false;
			foreach ($fields as $key=>$val){
				// 记录字段类型
				$this->type[$key]	 =	 $val['type'];
				if($val['primary']) {
					$this->fields['_pk']	=	$key;
					if($val['autoInc'])	$this->fields['_autoInc']	=	true;
				}
			}
		}
		$identify	=	$this->name.'_fields';
		// 永久缓存数据表信息
		S($identify,$this->fields,-1);
	}

	/**
     +----------------------------------------------------------
     * 获取数据集的文本字段
     +----------------------------------------------------------
     * @access pubic 
     +----------------------------------------------------------
     * @param mixed $resultSet 查询的数据
     * @param string $field 查询的字段
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function getListBlobFields(&$resultSet,$field='') {
		if(!empty($this->blobFields)) {
			foreach ($resultSet as $key=>$result){
				$result	=	$this->getBlobFields($result,$field);
				$resultSet->offsetSet($key,$result);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 获取数据的文本字段
     +----------------------------------------------------------
     * @access pubic 
     +----------------------------------------------------------
     * @param mixed $data 查询的数据
     * @param string $field 查询的字段
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function getBlobFields(&$data,$field='') {
		if(!empty($this->blobFields)) {
			$pk	=	$this->getPk();
			$id	=	is_array($data)?$data[$pk]:$data->$pk;
			if(empty($field)) {
				foreach ($this->blobFields as $field){
					if($this->viewModel) {
						$identify	=	$this->masterModel.'_'.$id.'_'.$field;
					}else{
						$identify	=	$this->name.'_'.$id.'_'.$field;
					}
					if(is_array($data)) {
						$data[$field]	=	F($identify);
					}else{
						$data->$field	 =	 F($identify);
					}
				}
				return $data;
			}else{
				$identify	=	$this->name.'_'.$id.'_'.$field;
				return F($identify);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 保存File方式的字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 保存的数据
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function saveBlobFields(&$data) {
		if(!empty($this->blobFields)) {
			foreach ($this->blobValues as $key=>$val){
				if(strpos($key,'@@_?id_@@')) {
					$key	=	str_replace('@@_?id_@@',$data[$this->getPk()],$key);
				}
				F($key,$val);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 删除File方式的字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 保存的数据
     * @param string $field 查询的字段
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function delBlobFields(&$data,$field='') {
		if(!empty($this->blobFields)) {
			$pk	=	$this->getPk();
			$id	=	is_array($data)?$data[$pk]:$data->$pk;
			if(empty($field)) {
				foreach ($this->blobFields as $field){
					$identify	=	$this->name.'_'.$id.'_'.$field;
					F($identify,null);
				}
			}else{
				$identify	=	$this->name.'_'.$id.'_'.$field;
				F($identify,null);
			}
		}
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
		if(!empty($this->dataList)) {
			// 存在数据集则返回数据集
			return $this->dataList;
		}elseif(!empty($this->data)){
			// 存在数据对象则返回对象的Iterator
			return new ArrayObject($this->data);
		}else{
			// 否则返回字段名称的Iterator
			$fields = $this->fields;
			unset($fields['_pk'],$fields['_autoInc']);
			return new ArrayObject($fields);
		}
	}

	/**
     +----------------------------------------------------------
     * 新增数据 支持数组、HashMap对象、std对象、数据对象
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param boolean $autoLink 自动关联写入
     +----------------------------------------------------------
     * @return int
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function add($data=null,$autoLink=false,$multi=false)
	{
		if(empty($data)) {
			// 没有传递数据，获取当前数据对象的值
			if(!empty($this->data)) {
				$data	 =	 $this->data;
			}elseif(!empty($this->dataList)){
				return $this->addAll($this->dataList);
			}else{
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}
		// 对保存到数据库的数据对象进行处理
		$data	=	$this->_facade($data);
		if(!$data) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		if($this->fields['_autoInc'] && isset($data[$this->getPk()])) {
			//如果主键为自动增长类型
			//删除主键属数据 由数据库自动生成
			unset($data[$this->getPk()]);
		}
		// 记录乐观锁
		if($this->optimLock && !isset($data[$this->optimLock]) ) {
			if(in_array($this->optimLock,$this->fields,true)) {
				$data[$this->optimLock]	 =	 0;
			}
		}
		return $this->_create($data,$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 对保存到数据库的数据对象进行处理
	 * 统一使用数组方式到数据库中间层 facade字段支持
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $data 要操作的数据
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
	protected function _facade($data) {
		if(is_instance_of($data,'HashMap')){
			// Map对象转换为数组
			$data = $data->toArray();
		}elseif(is_object($data)) {
			$data	 =	 get_object_vars($data);
		}elseif(!is_array($data)){
			return false;
		}
		// 检查聚合对象
		if(!empty($this->aggregation)) {
			foreach ($this->aggregation as $name){
				if(is_array($name)) {
					$fields	=	$name[1];
					$name	=	$name[0];
					if(is_string($fields)) $fields = explode(',',$fields);
				}
				if(!empty($data[$name])) {
					$combine = (array)$data[$name];
					if(!empty($fields)) {
						// 限制聚合对象的字段属性
						foreach ($fields as $key=>$field){
							if(is_int($key)) $key = $field;
							if(isset($combine[$key])) {
								$data[$field]	=	$combine[$key];
							}
						}
					}else{
						// 直接合并数据
						$data = $data+$combine;
					}
					unset($data[$name]);
				}
			}
		}
		// 检查Blob文件保存字段
		if(!empty($this->blobFields)) {
			foreach ($this->blobFields as $field){
				if(isset($data[$field])) {
					if(isset($data[$this->getPk()])) {
						$this->blobValues[$this->name.'_'.$data[$this->getPk()].'_'.$field]	=	$data[$field];
					}else{
						$this->blobValues[$this->name.'_@@_?id_@@_'.$field]	=	$data[$field];
					}
					unset($data[$field]);
				}
			}
		}
		// 检查字段映射
		if(isset($this->_map)) {
			foreach ($this->_map as $key=>$val){
				if(isset($data[$key])) {
					$data[$val]	=	$data[$key];
					unset($data[$key]);
				}
			}
		}
		return $data;
	}

	/**
     +----------------------------------------------------------
     * 检查条件中的视图字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 条件表达式
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */	
	public function checkCondition($data) {
		 if($this->viewModel ) {
			 if((empty($data) || (is_instance_of($data,'HashMap') && $data->isEmpty())) && !empty($this->viewCondition)) {
				 $data = $this->viewCondition;
			 }elseif(!is_string($data)) {
				$data	 =	 $this->_facade($data);
				$baseCondition = $this->viewCondition;
				$view	=	array();
				// 检查视图字段
				foreach ($this->viewFields as $key=>$val){
					foreach ($data as $name=>$value){
						if(false !== $field = array_search($name,$val)) {
							// 存在视图字段
							if(is_numeric($field)) {
								$_key	=	$key.'.'.$name;
							}else{
								$_key	=	$key.'.'.$field;
							}
							$view[$_key]	=	$value;
							unset($data[$name]);
							if(is_array($baseCondition) && isset($baseCondition[$_key])) {
								// 组合条件处理
								$view[$_key.','.$_key]	=	array($value,$baseCondition[$_key]);
								unset($baseCondition[$_key]);
								unset($view[$_key]);
							}
						}
					}
				}
				//if(!empty($view) && !empty($baseCondition)) {
					$data	 =	 array_merge($data,$baseCondition,$view);
				//}
			 }
		 }
		return $data;
	}

	/**
     +----------------------------------------------------------
     * 检查fields表达式中的视图字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $fields 字段
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */	
	public function checkFields($fields) {
		if(empty($fields) || '*'==$fields ) {
			// 获取全部视图字段
			$fields	=	array();
			foreach ($this->viewFields as $name=>$val){
				foreach ($val as $key=>$field){
					if(is_numeric($key)) {
						$fields[]	 =	 $name.'.'.$field.' AS '.$field;
					}else{
						$fields[]	 =	 $name.'.'.$key.' AS '.$field;
					}
				}
			}
		}else{
			$fields	=	explode(',',$fields);
			// 解析成视图字段
			foreach ($this->viewFields as $name=>$val){
				foreach ($fields as $key=>$field){
					if(false !== $_field = array_search($field,$val)) {
						// 存在视图字段
						if(is_numeric($_field)) {
							$fields[$key]	 =	 $name.'.'.$field.' AS '.$field;
						}else{
							$fields[$key]	 =	 $name.'.'.$_field.' AS '.$field;
						}
					}
				}
			}
		}
		$fields = implode(',',$fields);
		return $fields;
	}

	/**
     +----------------------------------------------------------
     * 检查Order表达式中的视图字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $order 字段
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */	
	public function checkOrder($order) {
		 if(!empty($order)) {
			$orders = explode(',',$order);
			$_order = array();
			foreach ($orders as $order){
				$array = explode(' ',$order);
				$field	 =	 $array[0];
				$sort	 =	 isset($array[1])?$array[1]:'ASC';
				// 解析成视图字段
				foreach ($this->viewFields as $name=>$val){
					if(false !== $_field = array_search($field,$val)) {
						// 存在视图字段
						if(is_numeric($_field)) {
							$field =	 $name.'.'.$field;
						}else{
							$field	 =	 $name.'.'.$_field;
						}
						break;
					}
				}
				$_order[] = $field.' '.$sort;
			}
			$order = implode(',',$_order);
		 }
		return $order;
	}

	/**
     +----------------------------------------------------------
     * 检查Group表达式中的视图字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $group 字段
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */	
	public function checkGroup($group) {
		 if(empty($group)) {
			 $group = $this->getPk();
		 }
		$groups = explode(',',$group);
		$_group = array();
		foreach ($groups as $group){
			$array = explode(' ',$group);
			$field	 =	 $array[0];
			$sort	 =	 isset($array[1])?$array[1]:'';
			// 解析成视图字段
			foreach ($this->viewFields as $name=>$val){
				if(false !== $_field = array_search($field,$val)) {
					// 存在视图字段
					if(is_numeric($_field)) {
						$field =	 $name.'.'.$field;
					}else{
						$field	 =	 $name.'.'.$_field;
					}
					break;
				}
			}
			$_group[$field] = $field.' '.$sort;
		}
		$group	=	$_group;
		return $group;
	}

	/**
     +----------------------------------------------------------
     * 批量新增数据 支持ResultSet和ArrayObject对象
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $dataList 数据列表
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function addAll($dataList='',$autoLink=false)
	{
		if(empty($dataList)) {
			$dataList	=	$this->dataList;
		}elseif(is_instance_of($dataList,'ResultSet') || is_instance_of($dataList,'ArrayObject')){
			
		}elseif(!is_array($dataList)) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		return $this->_create($dataList,$autoLink,true);
	}

	/**
     +----------------------------------------------------------
     * 更新数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 要更新的数据
     * @param mixed $where 更新数据的条件
     * @param boolean $autoLink 自动关联操作
     * @param integer $limit 要更新的记录数
     * @param string $order  更新的顺序
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function save($data=null,$where='',$autoLink=false,$limit=0,$order='')
	{
		if(is_instance_of($data,'ResultSet') || is_instance_of($data,'ArrayObject')){
			// 批量保存数据
			//启用事务操作
			$this->startTrans();
			foreach ($data as $val){
				$result   =  $this->save($val,$where,$autoLink);
			}
			$this->commit();
			return $result;
		}elseif(empty($data)) {
			if(!empty($this->data)) {
				// 保存当前数据对象
				$data	 =	 $this->data;
			}elseif(!empty($this->dataList)){
				// 保存当前数据集
				$data	 =	 $this->dataList;
				$this->startTrans();
				foreach ($data as $val){
					$result   =  $this->save($val,$where,$autoLink);
				}
				$this->commit();
				return $result;
			}
		}
		$data	=	$this->_facade($data);
		if(!$data) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		// 检查乐观锁
		if(!$this->checkLockVersion($data,$where)) {
			$this->error = L('_RECORD_HAS_UPDATE_');
			return false;
		}
		return $this->_update($data,$where,$limit,$order,$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 检查乐观锁
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param array $data  当前数据
     * @param mixed $where 查询条件
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function checkLockVersion(&$data,&$where='') {
		$pk	=	$this->getPk();
		$id	=	$data[$pk];
		if(empty($where) && isset($id) ) {
			$where  = $pk."=".$id;
		}
		// 检查乐观锁
		$identify   = $this->name.'_'.$id.'_lock_version';
		if($this->optimLock && Session::is_set($identify)) {
			$lock_version = Session::get($identify);
			if(!empty($where)) {
				$vo = $this->find($where,$this->optimLock);
			}else {
				$vo = $this->find($data,$this->optimLock);
			}
			Session::set($identify,$lock_version);
			$curr_version = is_array($vo)?$vo[$this->optimLock]:$vo->{$this->optimLock};
			if(isset($curr_version)) {
				if($curr_version>0 && $lock_version != $curr_version) {
					// 记录已经更新
					return false;
				}else{
					// 更新乐观锁
					$save_version = $data[$this->optimLock];
					if($save_version != $lock_version+1) {
						$data[$this->optimLock]	 =	 $lock_version+1;
					}
					Session::set($identify,$lock_version+1);
				}
			}
		}
		//unset($data[$pk]);
		return true;
	}

	/**
     +----------------------------------------------------------
     * 获取返回数据的关联记录
	 * relation['name'] 关联名称 relation['type'] 关联类型
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $result  返回数据
     * @param array $relation  关联信息
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getRelation(&$result,$relation=array())
	{
		if(!empty($this->_link)) {
			foreach($this->_link as $val) {
				if(empty($relation['type']) || $val['mapping_type']==$relation['type']) {
					$mappingName =  !empty($val['mapping_name'])?$val['mapping_name']:$val['class_name'];	// 映射名称
					if(empty($relation['name']) || $mappingName == $relation['name']) {
						$mappingType = $val['mapping_type'];	//  关联类型
						$mappingClass  = $val['class_name'];			//  关联类名
						$mappingFk   =  !empty($val['foreign_key'])?$val['foreign_key']:$this->name.'_id';		//  关联外键
						$mappingFields = !empty($val['mapping_fields'])?$val['mapping_fields']:'*';		// 映射字段
						$mappingCondition = !empty($val['condition'])?$val['condition']:'1';			// 关联条件
						if(strtoupper($mappingClass)==strtoupper($this->name)) {
							// 自引用关联 获取父键名
							$mappingParentKey = !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
						}
						// 获取关联模型对象
						$model = D($mappingClass);
						switch($mappingType) {
							case HAS_ONE:
								$pk   =  is_array($result)?$result[$this->getPk()]:$result->{$this->getPk()};
								$mappingCondition .= " AND {$mappingFk}={$pk}";
								$relationData   =  $model->find($mappingCondition,$mappingFields,false,false);
								break;
							case BELONGS_TO:
								$fk   =  is_array($result)?$result[$mappingFk]:$result->{$mappingFk};
								$mappingCondition .= " AND {$model->getPk()}={$fk}";
								$relationData   =  $model->find($mappingCondition,$mappingFields,false,false);
								if(isset($val['as_fields'])) {
									// 支持直接把关联的字段值映射成数据对象中的某个字段
									$fields	=	explode(',',$val['as_fields']);
									foreach ($fields as $field){
										$fieldAs = explode(':',$field);
										if(count($fieldAs)>1) {
											$fieldFrom = $fieldAs[0];
											$fieldTo		=	$fieldAs[1];
										}else{
											$fieldFrom	 =	 $field;
											$fieldTo		=	$field;
										}
										$fieldVal	 =	 is_array($relationData)?$relationData[$fieldFrom]:$relationData->$fieldFrom;
										if(isset($fieldVal)) {
											if(is_array($result)) {
												$result[$fieldTo]	=	$fieldVal;
											}else{
												$result->$fieldTo	=	$fieldVal;
											}
										}
									}
								}
								break;
							case HAS_MANY:
								$pk   =  is_array($result)?$result[$this->getPk()]:$result->{$this->getPk()};
								$mappingCondition = "{$mappingFk}={$pk}";
								$mappingOrder =  !empty($val['mapping_order'])?$val['mapping_order']:'';
								$mappingLimit =  !empty($val['mapping_limit'])?$val['mapping_limit']:'';
								// 延时获取关联记录
								$relationData   =  $model->findAll($mappingCondition,$mappingFields,$mappingOrder,$mappingLimit,null,null,true,false,true);
								break;
							case MANY_TO_MANY:
								if(empty($mappingCondition)) {
									$pk   =  is_array($result)?$result[$this->getPk()]:$result->{$this->getPk()};
									$mappingCondition = "{$mappingFk}={$pk}";
								}
								$mappingOrder =  $val['mapping_order'];
								$mappingLimit =  $val['mapping_limit'];
								$mappingRelationFk = $val['relation_foreign_key']?$val['relation_foreign_key']:$model->name.'_id';
								$mappingRelationTable  =  $val['relation_table']?$val['relation_table']:$this->getRelationTableName($model);
								$sql = "SELECT b.{$mappingFields} FROM {$mappingRelationTable} AS a, ".$model->getTableName()." AS b WHERE a.{$mappingRelationFk} = b.{$model->getPk()} AND a.{$mappingCondition}";
								if(!empty($mappingOrder)) {
									$sql .= ' ORDER BY '.$mappingOrder;
								}
								if(!empty($mappingLimit)) {
									$sql .= ' LIMIT '.$mappingLimit;
								}
								$relationData	=	$this->_query($sql,false,true);
								break;
						}
						if(is_array($result)) {
							$result[$mappingName] = $relationData;
						}else{
							$result->$mappingName = $relationData;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
     +----------------------------------------------------------
     * 获取返回数据集的关联记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $result  返回数据
     * @param array $relation  关联信息
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function getRelations(&$resultSet,$relation=array()) {
		// 获取记录集的主键列表
		/*
		$pk = array();
		foreach($resultSet as $key=>$val) {
			$val	=	(array)$val;
			$pk[$key]	=	$val[$this->getPk()];
		}
		$pks	=	implode(',',array_unique($pk));*/
		foreach($resultSet as $key=>$val) {
			$val  = $this->getRelation($val,$relation);
			$resultSet->offsetSet($key,$val);
		}
	}

	/**
     +----------------------------------------------------------
     * 操作关联数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $opType  操作方式 ADD SAVE DEL
     * @param mixed $data  数据对象
     * @param array $relation 关联信息
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function opRelation($opType,$data,$relation=array())
	{
		$result	=	false;
		// 把HashMap对象转换成数组
		if(is_instance_of($data,'HashMap')){
			$data = $data->toArray();
		}elseif(is_object($data)){
			$data	 =	 get_object_vars($data);
		}elseif(!is_array($data)){
			// 数据无效返回
			return false;
		}
		if(!empty($this->_link)) {
			// 遍历关联定义
			foreach($this->_link as $val) {
				if(empty($relation['type']) || $val['mapping_type']==$relation['type']) {
					// 操作制定关联类型
					$mappingName =  $val['mapping_name']?$val['mapping_name']:$val['class_name'];	// 映射名称
					if(empty($relation['name']) || $mappingName == $relation['name']) {
						// 操作制定的关联
						$mappingType = $val['mapping_type'];	//  关联类型
						$mappingClass  = $val['class_name'];			//  关联类名
						$mappingFk   =  $val['foreign_key']?$val['foreign_key']:$this->name.'_id';		//  关联外键
						$mappingFields = $val['mapping_fields'];		// 映射字段
						//$mappingCondition = $val['condition'];			// 关联条件
						// 当前数据对象主键值
						$pk	=	$data[$this->getPk()];
						$mappingCondition = "{$mappingFk}={$pk}";
						if(strtoupper($mappingClass)==strtoupper($this->name)) {
							// 自引用关联 获取父键名
							$mappingParentKey = !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
						}
						// 获取关联model对象
						$model = D($mappingClass);
						$mappingData	=	isset($relation['data'])?$relation['data']:$data[$mappingName];
						if(is_instance_of($mappingData,'HashMap') || is_instance_of($mappingData,'ResultSet')){
							$mappingData = $mappingData->toArray();
						}elseif(is_object($mappingData)){
							$mappingData =	 get_object_vars($mappingData);
						}
						if(!empty($mappingData)) {
							switch($mappingType) {
								case HAS_ONE:
									switch (strtoupper($opType)){
										case 'ADD':	// 增加关联数据
										$mappingData[$mappingFk]	=	$pk;
										$result   =  $model->add($mappingData,false);
										break;
										case 'SAVE':	// 更新关联数据
										$result   =  $model->save($mappingData,$mappingCondition,false);
										break;
										case 'DEL':	// 根据外键删除关联数据
										$result   =  $model->delete($mappingCondition,'','',false);
										break;
									}
									break;
								case BELONGS_TO:
									break;
								case HAS_MANY:
									switch (strtoupper($opType)){
										case 'ADD'	 :	// 增加关联数据
										$model->startTrans();
										foreach ($mappingData as $val){
											$val[$mappingFk]	=	$pk;
											$result   =  $model->add($val,false);
										}
										$model->commit();
										break;
										case 'SAVE' :	// 更新关联数据
										//$mappingOrder =  $val['mapping_order'];
										//$mappingLimit =  $val['mapping_limit'];
										$model->startTrans();
										foreach ($mappingData as $vo){
											//$result   =  $model->save($vo,$mappingCondition,false,$mappingLimit,$mapppingOrder);
											$result   =  $model->save($vo,$mappingCondition,false);
										}
										$model->commit();
										break;
										case 'DEL' :	// 删除关联数据
										$result   =  $model->delete($mappingCondition,'','',false);
										break;
									}
									break;
								case MANY_TO_MANY:
									$mappingRelationFk = $val['relation_foreign_key']?$val['relation_foreign_key']:$model->name.'_id';// 关联
									$mappingRelationTable  =  $val['relation_table']?$val['relation_table']:$this->getRelationTableName($model);
									foreach ($mappingData as $vo){
										$relationId[]	=	$vo[$model->getPk()];
									}
									$relationId	=	implode(',',$relationId);
									switch (strtoupper($opType)){
										case 'ADD':	// 增加关联数据
										case 'SAVE':	// 更新关联数据
										$this->startTrans();
										// 删除关联表数据
										$this->db->remove($mappingCondition,$mappingRelationTable);
										// 插入关联表数据
										$sql  = 'INSERT INTO '.$mappingRelationTable.' ('.$mappingFk.','.$mappingRelationFk.') SELECT a.'.$this->getPk().',b.'.$model->getPk().' FROM '.$this->getTableName().' AS a ,'.$model->getTableName()." AS b where a.".$this->getPk().' ='. $pk.' AND  b.'.$model->getPk().' IN ('.$relationData.") ";
										$result	=	$model->execute($sql);
										if($result) {
											// 提交事务
											$this->commit();
										}else {
											// 事务回滚
											$this->rollback();
										}
										break;
										case 'DEL':	// 根据外键删除中间表关联数据
										$result	=	$this->db->remove($mappingCondition,$mappingRelationTable);
										break;
									}
									break;
							}
						}
					}
				}
			}
		}
		return $result;
	}

	/**
     +----------------------------------------------------------
     * 根据主键删除数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param integer $id 主键值
     * @param boolean $autoLink  是否关联删除
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
	public function deleteById($id,$autoLink=false)
	{
		$pk	=	$this->getPk();
		return $this->_delete(array($pk=>$id),$pk."='$id'",0,'',$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 根据多个主键删除数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param integer $ids 多个主键值
     * @param integer $limit 要删除的记录数
     * @param string $order  删除的顺序
     * @param boolean $autoLink  是否关联删除
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
	public function deleteByIds($ids,$limit='',$order='',$autoLink=false)
	{
		return $this->_delete(false,$this->getPk()." IN ($ids)",$limit,$order,$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 根据某个字段删除数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 字段名称
     * @param mixed $value 字段值
     * @param integer $limit 要删除的记录数
     * @param string $order  删除的顺序
     * @param boolean $autoLink  是否关联删除
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
	// 根据某个字段的值删除记录
	public function deleteBy($field,$value,$limit='',$order='',$autoLink=false) {
		return $this->_delete(false,$field."='$value'",$limit,$order,$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 根据条件删除表数据
     * 如果成功返回删除记录个数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 删除条件
     * @param integer $limit 要删除的记录数
     * @param string $order  删除的顺序
     * @param boolean $autoLink  是否关联删除
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function delete($data=null,$limit='',$order='',$autoLink=false)
	{
		if(preg_match('/^\d+(\,\d+)*$/',$data)) {
			// 如果是数字 直接使用deleteByIds
			return $this->deleteByIds($data,$limit,$order,$autoLink);
		}
		if(empty($data)) {
			$data	 =	 $this->data;
		}
		if(is_array($data) && isset($data[$this->getPk()])) {
			$data	=	$this->_facade($data);
			$where  = $this->getPk()."=".$data[$this->getPk()];
		}else {
			$where  =   $data;
		}
		return $this->_delete($data,$where,$limit,$order,$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 根据条件删除数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition 删除条件
     * @param boolean $autoLink  是否关联删除
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function deleteAll($condition='',$autoLink=false)
	{
		if(is_instance_of($condition,'HashMap')) {
			$condition    = $condition->toArray();
		}elseif(empty($condition) && !empty($this->dataList)){
			$id = array();
			foreach ($this->dataList as $data){
				$data = (array)$data;
				$id[]	 =	 $data[$this->getPk()];
			}
			$ids = implode(',',$id);
			$condition = $this->getPk().' IN ('.$ids.')';
		}
		return $this->_delete(false,$condition,0,'',$autoLink);
	}

	/**
     +----------------------------------------------------------
     * 清空表数据
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function clear()
	{
		if(false === $this->db->clear($this->getTableName())){
			$this->error =  L('_OPERATION_WRONG_');
			return false;
		}else {
			return true;
		}
	}

	/**
     +----------------------------------------------------------
     * 根据主键得到一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param int $id 主键的值
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否缓存
     * @param mixed $relation 是否关联读取
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getById($id,$fields='*',$cache=false,$relation=false,$lazy=false)
	{
		return $this->_read($this->getPk()."='{$id}'",$fields,false,null,null,null,null,null,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 根据主键范围得到多个记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $ids 主键的范围 如 1,3,4,7 array(1,2,3)
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否缓存
     * @param mixed $relation 是否关联读取
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getByIds($ids,$fields='*',$order='',$limit='',$cache=false,$relation=false,$lazy=false)
	{
		if(is_array($ids)) {
			$ids	=	implode(',',$ids);
		}
		return $this->_read($this->getPk()." IN ({$ids})",$fields,true,$order,$limit,null,null,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 根据某个字段得到一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 字段名称
     * @param mixed $value 字段的值
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否缓存查询
     * @param mixed $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getBy($field,$value,$fields='*',$cache=false,$relation=false,$lazy=false)
	{
		return $this->_read($field."='{$value}'",$fields,false,null,null,null,null,null,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 根据某个字段获取全部记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 字段名称
     * @param mixed $value 字段的值
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否缓存查询
     * @param mixed $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getByAll($field,$value,$fields='*',$cache=false,$relation=false,$lazy=true)
	{
		return $this->_read($field."='{$value}'",$fields,true,null,null,null,null,null,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 根据条件得到一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition 条件
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否读取缓存
     * @param mixed $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function find($condition='',$fields='*',$cache=false,$relation=false,$lazy=false)
	{
		if(is_numeric($condition)) {
			// 如果是数字 直接使用getById
			return $this->getById($condition,$fields,$cache,$relation,$lazy);
		}
		return $this->_read($condition,$fields,false,null,1,null,null,null,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 根据条件得到一条记录
     * 并且返回关联记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition 条件
     * @param string $fields 字段名，默认为*
     * @param boolean $cache 是否读取缓存
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function xFind($condition='',$fields='*',$cache=false,$lazy=false)
	{
		return $this->find($condition,$fields,$cache,true,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 查找记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition  条件
     * @param string $fields  查询字段
     * @param string $order  排序
     * @param string $limit  
     * @param string $group  
     * @param string $having 
     * @param string $join
     * @param boolean $cache 是否读取缓存
     * @param mixed $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return ArrayObject|ResultIterator
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function findAll($condition='',$fields='*',$order='',$limit='',$group='',$having='',$join='',$cache=false,$relation=false,$lazy=false)
	{
		if($this->staticModel) {
			return $this->dataList;
		}
		if(is_string($condition) && preg_match('/^\d+(\,\d+)+$/',$condition)) {
			return $this->getByIds($condition,$fields,$order,$limit,$cache,$relation,$lazy);
		}
		return $this->_read($condition,$fields,true,$order,$limit,$group,$having,$join,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 查询记录并返回相应的关联记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition  条件
     * @param string $fields  查询字段
     * @param string $order  排序
     * @param string $limit  
     * @param string $group  
     * @param string $having 
     * @param string $join
     * @param boolean $cache 是否读取缓存
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return ArrayObject|ResultIterator
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function xFindAll($condition='',$fields='*',$order='',$limit='',$group='',$having='',$join='',$cache=false)
	{
		return $this->findAll($condition,$fields,$order,$limit,$group,$having,$join,$cache,true,false);
	}

	/**
     +----------------------------------------------------------
     * 查找前N个记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param integer $count 记录个数 
     * @param mixed $condition  条件
     * @param string $fields  查询字段
     * @param string $order  排序
     * @param string $group  
     * @param string $having 
     * @param string $join
     * @param boolean $cache 是否读取缓存
     * @param mixed $relation 是否关联查询
     * @param boolean $lazy 是否惰性查询
     +----------------------------------------------------------
     * @return ArrayObject|ResultIterator
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function topN($count,$condition='',$fields='*',$order='',$group='',$having='',$join='',$cache=false,$relation=false,$lazy=false) {
		return $this->findAll($condition,$fields,$order,$count,$group,$having,$join,$cache,$relation,$lazy);
	}

	/**
     +----------------------------------------------------------
     * SQL查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql  SQL指令
     * @param boolean $cache  是否缓存
     * @param boolean $lazy  是否惰性查询
     +----------------------------------------------------------
     * @return ArrayObject|ResultIterator
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function query($sql,$cache=false,$lazy=false)
	{
		return $this->_query($sql,$cache,$lazy);
	}

	/**
     +----------------------------------------------------------
     * 执行SQL语句
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql  SQL指令
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function execute($sql)
	{
		if(!empty($sql)) {
			$result =   $this->db->execute($sql);
			return $result;
		}else {
			return false;
		}
	}

	/**
     +----------------------------------------------------------
     * 获取一条记录的某个字段值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  查询条件
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getField($field,$condition='')
	{
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$field);
		return $this->getCol($rs,$field);
	}

	/**
     +----------------------------------------------------------
     * 获取数据集的个别字段值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 字段名称
     * @param mixed $condition  条件
     * @param string $spea  多字段分割符号
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getFields($field,$condition='',$sepa=' ')
	{
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$field);
		return $this->getCols($rs,$field,$sepa);
	}

	/**
     +----------------------------------------------------------
     * 设置记录的某个字段值
	 * 支持使用数据库字段和方法
	 * 例如 setField('score','(score+1)','id=5');
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param string $value  字段值
     * @param mixed $condition  条件
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setField($field,$value,$condition='') {
		$condition = $this->checkCondition($condition);
		return $this->db->setField($field,$value,$this->getTableName(),$condition);
	}

	/**
     +----------------------------------------------------------
     * 字段值增长
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     * @param integer $step  增长值
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setInc($field,$condition='',$step=1) {
		$condition = $this->checkCondition($condition);
		return $this->db->setInc($field,$this->getTableName(),$condition,$step);
	}

	/**
     +----------------------------------------------------------
     * 字段值减少
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     * @param integer $step  减少值
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setDec($field,$condition='',$step=1) {
		$condition = $this->checkCondition($condition);
		return $this->db->setDec($field,$this->getTableName(),$condition,$step);
	}

	/**
     +----------------------------------------------------------
     * 获取查询结果中的某个字段值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param ArrayObject $rs  查询结果
     * @param string $field  字段名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getCol($rs,$field)
	{
		if(!empty($rs) && $rs->count()>0) {
			$result =   $rs->offsetGet(0);
			$field  =   is_array($result)?$result[$field]:$result->$field;
			return $field;
		}else {
			return null;
		}
	}

	/**
     +----------------------------------------------------------
     * 获取查询结果中的多个字段值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param ArrayObject $rs  查询结果
     * @param string $field  字段名用逗号分割多个
     * @param string $spea  多字段分割符号
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function getCols($rs,$field,$sepa=' ') {
		if(!empty($rs)) {
			$field	=	explode(',',$field);
			$cols	 =	 array();
			$length	 = count($field);
			foreach ($rs as $result){
				if(is_object($result)) $result	=	get_object_vars($result);
				if($length>1) {
					$cols[$result[$field[0]]]	=	'';
					for($i=1; $i<$length; $i++) {
						$cols[$result[$field[0]]] .= $result[$field[$i]].$sepa;
					}
				}else{
					$cols[]	 =	 $result[$field];
				}
			}
			return $cols;
		}
		return null;
	}

	/**
     +----------------------------------------------------------
     * 统计满足条件的记录个数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition  条件
     * @param string $field  要统计的字段 默认为*
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function count($condition='',$field='*')
	{
		$fields = 'count('.$field.') as count';
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$fields);
		return $this->getCol($rs,'count');
	}

	/**
     +----------------------------------------------------------
     * 取得某个字段的最大值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function max($field,$condition='')
	{
		$fields = 'MAX('.$field.') as max';
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$fields);
		return $this->getCol($rs,'max')|0;
	}

	/**
     +----------------------------------------------------------
     * 取得某个字段的最小值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function min($field,$condition='')
	{
		$fields = 'MIN('.$field.') as min';
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$fields);
		return $this->getCol($rs,'min')|0;
	}

	/**
     +----------------------------------------------------------
     * 统计某个字段的总和
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function sum($field,$condition='')
	{
		$fields = 'SUM('.$field.') as sum';
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$fields);
		return $this->getCol($rs,'sum') | 0;
	}

	/**
     +----------------------------------------------------------
     * 统计某个字段的平均值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field  字段名
     * @param mixed $condition  条件
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function avg($field,$condition='')
	{
		$fields = 'AVG('.$field.') as avg';
		$condition = $this->checkCondition($condition);
		$rs = $this->db->find($condition,$this->getTableName(),$fields);
		return $this->getCol($rs,'avg')|0;
	}

	/**
     +----------------------------------------------------------
     * 查询符合条件的第N条记录
     * 0 表示第一条记录 -1 表示最后一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param integer $position 记录位置
     * @param mixed $condition 条件
     * @param string $order 排序
     * @param string $fields 字段名，默认为*
     * @param boolean $relation 是否读取关联
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function getN($position=0,$condition='',$order='',$fields='*',$relation=false)
	{
		$condition = $this->checkCondition($condition);
		if($position>=0) {
			$rs = $this->db->find($condition,$this->getTableName(),$fields,$order,$position.',1');
			return $this->rsToVo($rs,false,0,$relation);
		}else{
			$rs = $this->db->find($condition,$this->getTableName(),$fields,$order);
			return $this->rsToVo($rs,false,$position,$relation);
		}
	}

	/**
     +----------------------------------------------------------
     * 获取满足条件的第一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition 条件
     * @param string $fields 字段名，默认为*
     * @param string $order 排序
     * @param boolean $relation 是否读取关联
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function first($condition='',$order='',$fields='*',$relation=false) {
		return $this->getN(0,$condition,$order,$fields,$relation);
	}

	/**
     +----------------------------------------------------------
     * 获取满足条件的第后一条记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $condition 条件
     * @param string $fields 字段名，默认为*
     * @param string $order 排序
     * @param boolean $relation 是否读取关联
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	public function last($condition='',$order='',$fields='*',$relation=false) {
		return $this->getN(-1,$condition,$order,$fields,$relation);
	}

	/**
     +----------------------------------------------------------
     * 记录乐观锁
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 数据对象
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function cacheLockVersion($data) {
		if($this->optimLock) {
			if(is_object($data))	$data	=	get_object_vars($data);
			if(isset($data[$this->optimLock]) && isset($data[$this->getPk()])) {
				// 只有当存在乐观锁字段和主键有值的时候才记录乐观锁
				Session::set($this->name.'_'.$data[$this->getPk()].'_lock_version',$data[$this->optimLock]);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 把查询结果转换为数据（集）对象
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $resultSet 查询结果记录集
     * @param Boolean $returnList 是否返回记录集
     * @param Integer $position 定位的记录集位置
     * @param boolean $relation 是否获取关联
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function rsToVo($resultSet,$returnList=false,$position=0,$relation='')
	{
		if($resultSet ) {
			if(!$returnList) {
				if(is_instance_of($resultSet,'ResultIterator')) {
					// 如果是延时查询返回的是ResultIterator对象
					$resultSet	=	$resultSet->getIterator();
				}
				// 返回数据对象
				if($position<0) {
					// 逆序查找
					$position = $resultSet->count()-abs($position);
				}
				if($resultSet->count()<= $position) {
					// 记录集位置不存在
					$this->error = L('_SELECT_NOT_EXIST_');
					return false;
				}
				$result  =  $resultSet->offsetGet($position);
				// 取出数据对象的时候记录乐观锁
				$this->cacheLockVersion($result);
				// 获取Blob数据
				$this->getBlobFields($result);
				// 获取关联记录
				if( $this->autoReadRelations || $relation ) {
					$result  =  $this->getRelation($result,$relation);
				}
				// 对数据对象自动编码转换
				$result	 =	 auto_charset($result,C('DB_CHARSET'),C('TEMPLATE_CHARSET'));
				// 记录当前数据对象
				$this->data	 =	 (array)$result;
				return $result;
			}else{
				if(is_instance_of($resultSet,'ResultIterator')) {
					// 如果是延时查询返回的是ResultIterator对象
					return $resultSet;
				}
				// 获取Blob数据
				$this->getListBlobFields($resultSet);

				// 返回数据集对象
				if( $this->autoReadRelations || $relation ) {
					// 获取数据集的关联记录
					$this->getRelations($resultSet,$relation);
				}
				// 对数据集对象自动编码转换
				$resultSet	=	auto_charset($resultSet,C('DB_CHARSET'),C('TEMPLATE_CHARSET'));
				// 记录数据列表
				$this->dataList	=	$resultSet;
				return $resultSet;
			}
		}else {
			return false;
		}
	}

	/**
     +----------------------------------------------------------
     * 创建数据对象 但不保存到数据库
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 创建数据
     * @param string $type 创建类型
     * @param boolean $batch 批量创建
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function create($data='',$type='add',$batch=false)
	{
		if($batch) {
			// 批量创建
			return $this->createAll($data,$type);
		}
		// 如果没有传值默认取POST数据
		if(empty($data)) {
			$data	 =	 $_POST;
		}
		elseif(is_instance_of($data,'HashMap')){
			$data = $data->toArray();
		}
		elseif(is_instance_of($data,'Model')){
			$data = $data->getIterator();
		}
		elseif(is_object($data)){
			$data	=	get_object_vars($data);
		}
		elseif(!is_array($data)){
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		$vo	=	$this->_createData($data,$type);
		return $vo;
	}

	/**
     +----------------------------------------------------------
     * 创建数据列表对象 但不保存到数据库
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $dataList 数据列表
     * @param string $type 创建类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function createAll($dataList='',$type='add')
	{
		// 如果没有传值默认取POST数据
		if(empty($dataList)) {
			$dataList	 =	 $_POST;
		}
		elseif(!is_array($dataList)){
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		foreach ($dataList as $data){
			$vo	=	$this->_createData($data,$type);
			if(false === $vo) {
				return false;
			}else{
				$this->dataList[] = $vo;
			}
		}
		return $this->dataList;
	}

	/**
     +----------------------------------------------------------
     * 创建数据对象 但不保存到数据库
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $data 创建数据
     * @param string $type 创建类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	function _createData($data,$type) {
		if ( strtolower($type) == "add" ) { //新增
			$vo = array();
		} else { //编辑
			// 获取数据库的对象
			$value   = $data[$this->getPk()];
			$rs		= $this->db->find($this->getPk()."='{$value}'",$this->getTableName());
			if($rs && $rs->count()>0) {
				$vo = $rs->offsetGet(0);
				if(DATA_TYPE_OBJ == C('DATA_RESULT_TYPE')) {
					// 对象模式
					$vo	=	get_object_vars($vo);
				}
			}else {
				$this->error	=	L('_SELECT_NOT_EXIST_');
				return false;
			}
		}

		// 对提交数据执行自动验证
		if(!$this->_before_validation($data,$type)) {
			return false;
		}
		if(!$this->autoValidation($data,$type)) {
			return false;
		}
		if(!$this->_after_validation($data,$type)) {
			return false;
		}

		// 验证完成生成数据对象
		foreach ( $this->fields as $key=>$name){
			if(substr($key,0,1)=='_') continue;
			$val = isset($data[$name])?$data[$name]:null;
			//保证赋值有效
			if(!is_null($val) ){
				// 首先保证表单赋值
				$vo[$name] = $val;
			}elseif(	(strtolower($type) == "add" && in_array($name,$this->autoCreateTimestamps,true)) ||
			(strtolower($type) == "edit" && in_array($name,$this->autoUpdateTimestamps,true)) ){
				// 自动保存时间戳
				if(!empty($this->autoTimeFormat)) {
					// 用指定日期格式记录时间戳
					$vo[$name] =	date($this->autoTimeFormat);
				}else{
					// 默认记录时间戳
					$vo[$name] = time();
				}
			}
		}

		// 执行自动处理
		$this->_before_operation($vo);
		$this->autoOperation($vo,$type);
		$this->_after_operation($vo);

		// 赋值当前数据对象
		$this->data	=	$vo;

		if(DATA_TYPE_OBJ == C('DATA_RESULT_TYPE')) {
			// 对象模式 强制转换为stdClass对象实例
			$vo	=	(object) $vo;
		}
		return $vo;
	}

	/**
     +----------------------------------------------------------
     * 自动表单处理
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $data 创建数据
     * @param string $type 创建类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	private function autoOperation(&$data,$type) {
		// 自动填充
		if(!empty($this->_auto)) {
			foreach ($this->_auto as $auto){
				// 填充因子定义格式
				// array('field','填充内容','填充条件','附加规则')
				if(in_array($auto[0],$this->fields,true)) {
					if(empty($auto[2])) $auto[2] = 'ADD';// 默认为新增的时候自动填充
					else $auto[2]	=	strtoupper($auto[2]);
					if( (strtolower($type) == "add"  && $auto[2] == 'ADD') || 	(strtolower($type) == "edit"  && $auto[2] == 'UPDATE') || $auto[2] == 'ALL')
					{
						switch($auto[3]) {
							case 'function':	//	使用函数进行填充 字段的值作为参数
							if(function_exists($auto[1])) {
								// 如果定义为函数则调用
								$data[$auto[0]] = $auto[1]($data[$auto[0]]);
							}
							break;
							case 'field':	 // 用其它字段的值进行填充
							$data[$auto[0]] = $data[$auto[1]];
							break;
							case 'callback': // 使用回调方法
							$data[$auto[0]]	 =	 $this->{$auto[1]}($data[$auto[0]]);
							break;
							case 'string':
							default: // 默认作为字符串填充
							$data[$auto[0]] = $auto[1];
						}
					}
				}
			}
		}
		return $data;
	}

	/**
     +----------------------------------------------------------
     * 自动表单验证
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $data 创建数据
     * @param string $type 创建类型
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	private function autoValidation($data,$type) {
		// 属性验证
		if(!empty($this->_validate)) {
			// 如果设置了Vo验证
			// 则进行数据验证
			import("ORG.Text.Validation");
			foreach($this->_validate as $key=>$val) {
				// 验证因子定义格式
				// array(field,rule,message,condition,append,when)
				// field rule message 必须
				// condition 验证条件：0 存在字段就验证 1 必须验证 2 值不为空的时候验证 默认为0
				// append 附加规则 :function confirm regex equal in unique 默认为regex
				// when 验证时间: all add edit 默认为all
				// 判断是否需要执行验证
				if(empty($val[5]) || $val[5]=='all' || strtolower($val[5])==strtolower($type) ) {
					// 判断验证条件
					switch($val[3]) {
						case MUST_TO_VALIDATE:	 // 必须验证 不管表单是否有设置该字段
							if(!$this->_validationField($data,$val)){
								$this->error	=	$val[2];
								return false;
							}
							break;
						case VALUE_TO_VAILIDATE:	// 值不为空的时候才验证
							if('' != trim($data[$val[0]])){
								if(!$this->_validationField($data,$val)){
									$this->error	=	$val[2];
									return false;
								}
							}
							break;
						default:	// 默认表单存在该字段就验证
							if(isset($data[$val[0]])){
								if(!$this->_validationField($data,$val)){
									$this->error	=	$val[2];
									return false;
								}
							}
					}
				}
			}
		}
		// TODO 数据类型验证
		//  判断数据类型是否符合
		return true;
	}

	/**
     +----------------------------------------------------------
     * 根据验证因子验证字段
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $data 创建数据
     * @param string $val 验证规则
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	private function _validationField($data,$val) {
		// 检查附加规则
		switch($val[4]) {
			case 'function':// 使用函数进行验证
				if(function_exists($val[1]) && !$val[1]($data[$val[0]])) {
					return false;
				}
				break;
			case 'callback':// 调用方法进行验证
				if(!$this->{$val[1]}($data[$val[0]])) {
					return false;
				}
				break;
			case 'confirm': // 验证两个字段是否相同
				if($data[$val[0]] != $data[$val[1]] ) {
					return false;
				}
				break;
			case 'in': // 验证是否在某个数组范围之内
				if(!in_array($data[$val[0]] ,$data[$val[1]]) ) {
					return false;
				}
				break;
			case 'equal': // 验证是否等于某个值
				if($data[$val[0]] != $val[1]) {
					return false;
				}
				break;
			case 'unique': // 验证某个值是否唯一
				if($this->getBy($val[0],$data[$val[0]])) {
					return false;
				}
				break;
			case 'regex':
				default:	// 默认使用正则验证 可以使用验证类中定义的验证名称
				if( !Validation::check($data[$val[0]],$val[1])) {
					return false;
				}
		}
		return true;
	}

	// 表单验证回调方法
	protected function _before_validation(&$data,$type) {return true;}
	protected function _after_validation(&$data,$type) {return true;}

	// 表单处理回调方法
	protected function _before_operation(&$data) {}
	protected function _after_operation(&$data) {}

	/**
     +----------------------------------------------------------
     * 得到当前的数据对象名称
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getModelName()
	{
		if(empty($this->name)) {
			$prefix	=	C('MODEL_CLASS_PREFIX');
			$suffix	=	C('MODEL_CLASS_SUFFIX');
			$this->name	=	substr(substr(get_class($this),strlen($prefix)),0,-strlen($suffix));
		}
		return $this->name;
	}

	/**
     +----------------------------------------------------------
     * 得到完整的数据表名
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getTableName()
	{
		if(empty($this->trueTableName)) {
			if($this->viewModel) {
				$tableName = '';
				foreach ($this->viewFields as $key=>$view){
					$Model	=	D($key);
					if($Model) {
						$tableName .= $Model->getTableName().' AS '.$key.',';
					}else{
						$viewTable  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
						$viewTable .= $key;
						$viewTable .= !empty($this->tableSuffix) ? $this->tableSuffix : '';
						$tableName .= strtolower($viewTable).' AS '.$key.',';
					}
				}
				$tableName = substr($tableName,0,-1);
				$this->trueTableName    =   $tableName;
			}else{
				$tableName  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
				$tableName .= $this->tableName?$this->tableName:$this->name;
				$tableName .= !empty($this->tableSuffix) ? $this->tableSuffix : '';
				$this->trueTableName    =   strtolower($tableName);
			}
		}
		return $this->trueTableName;
	}

	/**
     +----------------------------------------------------------
     * 得到关联的数据表名
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $relation 关联对象
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getRelationTableName($relation)
	{
		$relationTable  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
		$relationTable .= $this->tableName?$this->tableName:$this->name;
		$relationTable .= '_'.$relation->getModelName();
		$relationTable .= !empty($this->tableSuffix) ? $this->tableSuffix : '';
		return strtolower($relationTable);
	}

	/**
     +----------------------------------------------------------
     * 开启惰性查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function startLazy()
	{
		$this->lazyQuery = true;
		return ;
	}

	/**
     +----------------------------------------------------------
     * 关闭惰性查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function stopLazy()
	{
		$this->lazyQuery = false;
		return ;
	}

	/**
     +----------------------------------------------------------
     * 开启惰性查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function startLock()
	{
		$this->pessimisticLock = true;
		return ;
	}

	/**
     +----------------------------------------------------------
     * 关闭惰性查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function stopLock()
	{
		$this->pessimisticLock = false;
		return ;
	}

	/**
     +----------------------------------------------------------
     * 启动事务
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function startTrans()
	{
		$this->commit();
		$this->db->startTrans();
		return ;
	}

	/**
     +----------------------------------------------------------
     * 提交事务
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function commit()
	{
		return $this->db->commit();
	}

	/**
     +----------------------------------------------------------
     * 事务回滚
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function rollback()
	{
		return $this->db->rollback();
	}

	/**
     +----------------------------------------------------------
     * 得到主键名称
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getPk() {
		return $this->fields['_pk'];
	}

	/**
     +----------------------------------------------------------
     * 返回当前错误信息
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getError(){
		return $this->error;
	}

	/**
     +----------------------------------------------------------
     * 返回数据库字段信息
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getDbFields(){
		return $this->fields;
	}

	/**
     +----------------------------------------------------------
     * 返回最后插入的ID
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getLastInsID() {
		return $this->db->lastInsID;
	}
};
?>