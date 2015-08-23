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
 * PDO数据库驱动类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
Class DbPdo extends Db{

	protected $PDOStatement = null;

    /**
     +----------------------------------------------------------
     * 架构函数 读取数据库配置信息
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $config 数据库配置数组
     +----------------------------------------------------------
     */
    public function __construct($config=''){    
        if ( !class_exists('PDO') ) {    
            throw_exception(L('_NOT_SUPPERT_').':PDO');
        }
		if(!empty($config)) {
			$this->config	=	$config;
		}
    }

    /**
     +----------------------------------------------------------
     * 连接数据库方法
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
			if(empty($config))	$config	=	$this->config;
			if($this->pconnect) {
				$config['params'][constant('PDO::ATTR_PERSISTENT')] = true;
			}
            $this->linkID[$linkNum] = new PDO( $config['dsn'], $config['username'], $config['password'],$config['params']);
            if ( !$this->linkID[$linkNum]) {
                throw_exception('PDO CONNECT ERROR');
                return false;
            }
			$this->linkID[$linkNum]->exec('SET NAMES '.C('DB_CHARSET'));  
            $this->dbVersion = $this->linkID[$linkNum]->getAttribute(constant("PDO::ATTR_SERVER_INFO"));
			// 标记连接成功
			$this->connected	=	true;
            // 注销数据库连接配置信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     +----------------------------------------------------------
     * 释放查询结果
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     +----------------------------------------------------------
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * 返回数据集
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return ArrayObject
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function _query($str='') {
		$this->initConnect(false);
        if ( !$this->_linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        if (!$this->autoCommit && $this->isMainIps($this->queryStr)) {
			//$this->startTrans();
        }else {
            //释放前次的查询结果
            if ( !empty($this->PDOStatement) ) {    $this->free();    }
        }
        $this->queryTimes++;
		$this->Q(1);
        $this->PDOStatement = $this->_linkID->prepare($this->queryStr);
		$result	=	$this->PDOStatement->execute();
		$this->debug();
        if ( !$result ) {
            //if ( $this->debug ) throw_exception($this->error());
            return false;
        } else {
            $this->numRows = $this->PDOStatement->rowCount();
            $this->numCols = $this->PDOStatement->columnCount();
            $this->resultSet = $this->getAll();
            return new ArrayObject($this->resultSet);              	
        }
    }

    /**
     +----------------------------------------------------------
     * 执行语句 针对 INSERT, UPDATE 以及DELETE
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function _execute($str='') {
		$this->initConnect(true);
        if ( !$this->_linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        if (!$this->autoCommit && $this->isMainIps($this->queryStr)) {
			$this->startTrans();
        }else {
            //释放前次的查询结果
            if ( !empty($this->PDOStatement) ) {    $this->free();    }
        }
        $this->writeTimes++;
		$this->W(1);
		$result	=	$this->_linkID->exec($this->queryStr);
		$this->debug();
        if ( false === $result) {
            //if ( $this->debug ) throw_exception($this->error());
            return false;
        } else {
			$this->numRows = $result;
            $this->lastInsID = $this->_linkID->lastInsertId();
            return $this->numRows;            	
        }
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
	public function startTrans() {
		//数据rollback 支持
		if ($this->transTimes == 0) {
			$this->_linkID->beginTransaction();
		}
		$this->transTimes++;
		return ;
	}

    /**
     +----------------------------------------------------------
     * 用于非自动提交状态下面的查询提交
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function commit()
    {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->commit();
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
                return false;
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 事务回滚
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function rollback()
    {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->rollback();
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
                return false;
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 获得下一条查询结果 简易数据集获取方法
     * 查询结果放到 result 数组中
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function next() {
        if ( !$this->PDOStatement ) {
            throw_exception($this->error());
            return false;
        }
        if($this->resultType== DATA_TYPE_OBJ){
            // 返回对象集
            $this->result = $this->PDOStatement->fetch(constant('PDO::FETCH_OBJ'));
            $stat = is_object($this->result);
        }else{
            // 返回数组集
            $this->result = $this->PDOStatement->fetch(constant('PDO::FETCH_ASSOC'));
            $stat = is_array($this->result);
        }
        return $stat;
    }

    /**
     +----------------------------------------------------------
     * 获得一条查询结果
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param integer $seek 指针位置
     * @param string $str  SQL指令
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getRow($sql = null,$seek=0) {
        if (!empty($sql)) $this->_query($sql);
        if ( empty($this->PDOStatement) ) {
            throw_exception($this->error());
            return false;
        }
        if($this->numRows >0) {
			if($this->resultType== DATA_TYPE_OBJ){
				//返回对象集
				$result = $this->PDOStatement->fetch(constant('PDO::FETCH_OBJ'),constant('PDO::FETCH_ORI_NEXT'),$seek);
			}else{
				// 返回数组集
				$result = $this->PDOStatement->fetch(constant('PDO::FETCH_ASSOC'),constant('PDO::FETCH_ORI_NEXT'),$seek);
			}
            return $result;
        }else {
        	return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 获得所有的查询数据
     * 查询结果放到 resultSet 数组中
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $resultType  数据集类型
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getAll($sql = null,$resultType=null) {
        if (!empty($sql)) $this->_query($sql);
        if ( empty($this->PDOStatement) ) {
            throw_exception($this->error());
            return false;
        }
        //返回数据集
        $result = array();
        if($this->numRows >0) {
            if(is_null($resultType)){ $resultType   =  $this->resultType ; }
			if($resultType== DATA_TYPE_ARRAY){
				$result	=	$this->PDOStatement->fetchAll(constant('PDO::FETCH_ASSOC'));
			}else{
				$result	=	$this->PDOStatement->fetchAll(constant('PDO::FETCH_OBJ'));
			}
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * 取得数据表的字段信息
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getFields($tableName) { 
		$this->initConnect(true);
        $sth	=	$this->_linkID->prepare('SHOW COLUMNS FROM '.$tableName);
		$sth->execute();
		$result = $sth->fetchAll(constant('PDO::FETCH_ASSOC'));
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$val['Field']] = array(
                'name'    => $val['Field'],
                'type'    => $val['Type'],
                'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                'default' => $val['Default'],
                'primary' => (strtolower($val['Key']) == 'pri'),
                'autoInc' => (strtolower($val['Extra']) == 'auto_increment'),
            );
        }
        return $info;
    } 

    /**
     +----------------------------------------------------------
     * 取得数据库的表信息
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getTables($dbName='') { 
        $result = $this->_query('SHOW TABLES');
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    } 

    /**
     +----------------------------------------------------------
     * 关闭数据库
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function close() { 
        $this->_linkID = null;
    } 

    /**
     +----------------------------------------------------------
     * 数据库错误信息
     * 并显示当前的SQL语句
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function error() {
        $error = $this->PDOStatement->errorInfo();
		$this->error = $error[2];
        if($this->queryStr!=''){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * SQL指令安全过滤
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $str  SQL指令
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function escape_string($str) { 
		return addslashes($str);
    } 

}//类定义结束
?>