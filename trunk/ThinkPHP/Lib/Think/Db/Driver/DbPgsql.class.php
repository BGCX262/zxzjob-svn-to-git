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
 * Pgsql数据库驱动类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
Class DbPgsql extends Db{

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
        if ( !extension_loaded('pgsql') ) {    
            throw_exception(L('_NOT_SUPPERT_').':pgsql');
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
            $conn = $this->pconnect ? 'pg_pconnect':'pg_connect';
            $this->linkID[$linkNum] =  $conn(
                'host='			. $config['hostname'] .
                ' port='			. $config['hostport'] .
                ' dbname='	. $config['database'] .
                ' user='			. $config['username'] .
                ' password='	. $config['password']
            );

             if (pg_connection_status($this->linkID[$linkNum]) !== 0){
                throw_exception($this->error(false));
                return false;
            }
            $pgInfo = pg_version($this->linkID[$linkNum]);
            $this->dbVersion = $pgInfo['server'];
            @pg_query( $this->linkID[$linkNum],"SET NAMES '".C('DB_CHARSET')."'");
			// 标记连接成功
			$this->connected	=	true;
            //注销数据库安全信息
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
        @pg_free_result($this->queryID);
        $this->queryID = 0;
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
			$this->startTrans();
        }else {
            //释放前次的查询结果
            if ( $this->queryID ) {    $this->free();    }
        }
        $this->queryTimes ++;
		$this->Q(1);
        $this->queryID = pg_query($this->_linkID,$this->queryStr );
		$this->debug();
        if ( !$this->queryID ) {
            throw_exception($this->error());
            return false;
        } else {
            $this->numRows = pg_num_rows($this->queryID);
            $this->numCols = pg_num_fields($this->queryID);
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
            if ( $this->queryID ) {    $this->free();    }
        }

        $this->writeTimes ++;
		$this->W(1);
		$this->debug();
		$result	=	pg_query($this->_linkID,$this->queryStr);
		$this->debug();
        if ( false === $result ) {
            //throw_exception($this->error());
            return false;
        } else {
            $this->numRows = pg_affected_rows($this->queryID);
            $this->lastInsID = pg_last_oid($this->queryID);
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
			pg_exec($this->_linkID,'begin;');
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
            $result = pg_exec($this->_linkID,'end;');
            if(!$result){
                throw_exception($this->error());
                return false;
            }
            $this->transTimes = 0;
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
            $result = pg_exec($this->_linkID,'abort;');
            if(!$result){
                throw_exception($this->error());
                return false;
            }
            $this->transTimes = 0;
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

        if ( !$this->queryID ) {
            throw_exception($this->error());
            return false;
        }
        // 查询结果
        if($this->resultType== DATA_TYPE_OBJ){
            $this->result = pg_fetch_object($this->queryID);
            $stat = is_object($this->result);
        }else{
            $this->result = pg_fetch_assoc($this->queryID);
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
     * @param index $seek 指针位置
     * @param string $str  SQL指令
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
     public function getRow($sql = null,$seek=0) 
        {
            if (!empty($sql)) $this->_query($sql);
            if ( !$this->queryID ) {
                throw_exception($this->error());
                return false;
            }
            if($this->numRows >0) {
                if(pg_result_seek($this->queryID,$seek)){
                    if($this->resultType== DATA_TYPE_OBJ){
                        //返回对象集
                        $result = pg_fetch_object($this->queryID);
                    }else{
                        // 返回数组集
                        $result = pg_fetch_assoc($this->queryID);
                    }
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
     * @return resultSet
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getAll($sql = null,$resultType=null) {
        if (!empty($sql)) $this->_query($sql);
        if ( !$this->queryID ) {
            throw_exception($this->error());
            return false;
        }
        //返回数据集
        $result = array();
        if($this->numRows >0) {
            if(is_null($resultType)){ $resultType   =  $this->resultType ; }
            for($i=0;$i<$this->numRows ;$i++ ){
                if($resultType== DATA_TYPE_OBJ){
                    //返回对象集
                    $result[$i] = pg_fetch_object($this->queryID);
                }else{
                    // 返回数组集
                    $result[$i] = pg_fetch_assoc($this->queryID);
                }
            }
            pg_result_seek($this->queryID,0);
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
        $this->_query('SHOW COLUMNS FROM '.$tableName);
        $result =   $this->getAll();
        $info   =   array();
        foreach ($result as $key => $val) {
			if(is_object($val)) {
				$val	=	get_object_vars($val);
			}
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
		$result = $result->toArray();
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
        if (!empty($this->queryID))
            pg_free_result($this->queryID);
        if(!pg_close($this->_linkID)){
            throw_exception($this->error(false));
        }
        $this->_linkID = 0;
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
    public function error($result = true) {
        if($result){
            $this->error = pg_result_error($this->queryID);
        }else{
            $this->error = pg_last_error($this->_linkID);
        }
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
        return pg_escape_string($str); 
    } 

}//类定义结束
?>