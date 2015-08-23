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
// $Id: Db.class.php 53 2007-03-17 15:15:42Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP 数据库中间层实现类
 * 支持Mysql、MsSQL、Sqlite、Oracle、PgSQL等多种数据库
 * 还可以使用PDO
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Db extends Base
{

	// 数据库类型
    protected $dbType        = null;

    // 数据库版本
    protected $dbVersion    = null;

    // 是否自动释放查询结果
    protected $autoFree    = false;

    // 是否自动提交查询
    protected $autoCommit  = true;

    // 是否显示调试信息 如果启用会在日志文件记录sql语句
    protected $debug       = false;

    // 是否使用永久连接
    protected $pconnect     = false;

    // 当前SQL指令
    protected $queryStr = '';

    // 当前结果
    protected $result = null;

    // 当前查询的结果数据集
    protected $resultSet = null;

    // 数据集返回类型 0 返回数组 1 返回对象
    protected $resultType = DATA_TYPE_ARRAY;

    // 当前查询返回的字段集
    protected $fields = null;

    // 最后插入ID
    protected $lastInsID = null;

    // 返回或者影响记录数
    protected $numRows = 0;

    // 返回字段数
    protected $numCols = 0;

    // 查询次数
    protected $queryTimes = 0;

    // 写入次数
    protected $writeTimes = 0;

    // 事务指令数
    protected $transTimes = 0;

    // 错误信息
    protected $error = '';

    // 数据库连接ID 支持多个连接
    protected $linkID  = array();

	// 当前连接ID
	protected $_linkID	=	null;

    // 当前查询ID
    protected $queryID = null;

	// 是否已经连接数据库
	protected $connected = false;

	// 数据库连接参数配置
	protected $config = '';

	// 数据库表达式
    protected $comparison = array('eq'=>'=','neq'=>'!=','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','like'=>'LIKE','between'=>'BETWEEN','notnull'=>'IS NOT NULL','null'=>'IS NULL');

	// SQL 执行时间记录
	protected $beginTime;

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param array $config 数据库配置数组
     +----------------------------------------------------------
     */
    function __construct($config=''){    
		return $this->factory($config);
	}

    /**
     +----------------------------------------------------------
     * 取得数据库类实例
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @return mixed 返回数据库驱动类
     +----------------------------------------------------------
     */
    public static function getInstance() 
    {
        $args = func_get_args();
        return get_instance_of(__CLASS__,'factory',$args);
    }

    /**
     +----------------------------------------------------------
     * 加载数据库 支持配置文件或者 DSN
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $db_config 数据库配置信息
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function &factory($db_config='')
    {
        // 读取数据库配置
        if ( is_string($db_config) && !empty($db_config) ) {
            // 如果DSN字符串则进行解析
            $db_config = $this->parseDSN($db_config);
        }else if(empty($db_config)){
            // 如果配置为空，读取配置文件设置
            $db_config = array (
                'dbms'     => C('DB_TYPE'), 
                'username' => C('DB_USER'), 
                'password' => C('DB_PWD'), 
                'hostname' => C('DB_HOST'), 
                'hostport' => C('DB_PORT'), 
                'database' => C('DB_NAME'),
				'dsn'		=>	C('DB_DSN'),
				'params'=> C('DB_PARAMS'),
            );
        }
		if(empty($db_config['dbms'])) {
			throw_exception(L('_NO_DB_CONFIG_'));
		}
        // 数据库类型
		$this->dbType = ucwords(strtolower($db_config['dbms']));
		// 读取系统数据库驱动目录
		$dbClass = 'Db'. $this->dbType;
		$dbDriverPath = dirname(__FILE__).'/Driver/';      
		require_cache( $dbDriverPath . $dbClass . '.class.php');     	

		// 检查驱动类
		if(class_exists($dbClass)) {
			$db = new $dbClass($db_config);
			$db->dbType = $this->dbType.'  '.$db_config['dsn'];
		}else {
			// 类没有定义
			throw_exception(L('_NOT_SUPPORT_DB_').': ' . $db_config['dbms']);
		}
		return $db;
    }

    /**
     +----------------------------------------------------------
     * 初始化数据库连接
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param boolean $master 主服务器
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function initConnect($master=true) {
		if(1 == C('DB_DEPLOY_TYPE')) {
			// 采用分布式数据库
			$this->_linkID = $this->multiConnect($master);
		}else{
			// 默认单数据库
			if ( !$this->connected ) $this->_linkID = $this->connect();
		}
	}

    /**
     +----------------------------------------------------------
     * 连接分布式服务器
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param boolean $master 主服务器
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function multiConnect($master=false) {
		static $_config = array();
		if(empty($_config)) {
			// 缓存分布式数据库配置解析
			foreach ($this->config as $key=>$val){
				$_config[$key]	 	=	explode(',',$val);
			}
		}
		// 默认是连接第一个数据库配置 主服务器
		$r	=	0;
		if(!$master) {
			// 连接从服务器
			$r = floor(mt_rand(1,count($_config['hostname'])-1));	// 每次随机连接的数据库 不包括主服务器
		}
		$db_config = array(
			'username'=>	 isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
			'password' => isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0], 
			'hostname' => isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0], 
			'hostport' =>	 isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0], 
			'database' =>	 isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
			'dsn'	=>	isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
			'params'	=>	isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
		);	
		return $this->connect($db_config,$r);
	}

    /**
     +----------------------------------------------------------
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName
     +----------------------------------------------------------
     * @static
     * @access public 
     +----------------------------------------------------------
     * @param string $dsnStr  
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function parseDSN($dsnStr) 
    {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
            'dbms'     => $info['scheme'], 
            'username' => $info['user'] ? $info['user'] : '', 
            'password' => $info['pass'] ? $info['pass'] : '', 
            'hostname' => $info['host'] ? $info['host'] : '', 
            'hostport' => $info['port'] ? $info['port'] : '', 
            'database' => $info['path'] ? substr($info['path'],1) : ''
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
            'dbms'     => $matches[1], 
            'username' => $matches[2], 
            'password' => $matches[3], 
            'hostname' => $matches[4], 
            'hostport' => $matches[5], 
            'database' => $matches[6]
            );
        }
        return $dsn;
     }

    /**
     +----------------------------------------------------------
     * 数据库调试 记录当前SQL
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     */
	protected function debug() {
		// 记录操作结束时间
		$runtime	=	number_format((array_sum(explode(' ', microtime())) - $this->beginTime), 6);
        if ( $this->debug || C('SQL_DEBUG_LOG')) 	Log::Write(" RunTime:".$runtime."s SQL = ".$this->queryStr,SQL_LOG_DEBUG);
	}

    /**
     +----------------------------------------------------------
     * table分析
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $tables  数据表名
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseTables($tables)
    {
        if(is_array($tables)) $tablesStr = implode(',', $tables);
        else if(is_string($tables)) $tablesStr = $tables;
        return empty($tablesStr)?'':$tablesStr;
    }

    /**
     +----------------------------------------------------------
     * 返回数据库的类型 如果使用PDO 会进一步分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function getDbType() {
		$array	=	explode(' ',$this->dbType);
		if('PDO' != strtoupper($array[0])) {
			return strtoupper($array[0]);
		}else{
			if(false !== strpos(strtoupper($this->dbType),'MYSQL')) {
				return 'MYSQL';
			}elseif(false !== strpos(strtoupper($this->dbType),'MSSQL')){
				return 'MSSQL';
			}elseif(false !== strpos(strtoupper($this->dbType),'PGSQL')){
				return 'PGSQL';
			}elseif(false !== strpos(strtoupper($this->dbType),'SQLITE')){
				return 'SQLITE';
			}elseif(false !== strpos(strtoupper($this->dbType),'OCI')){
				return 'ORACLE';
			}elseif(false !== strpos(strtoupper($this->dbType),'IBM')){
				return 'DB2';
			}elseif(false !== strpos(strtoupper($this->dbType),'ODBC')){
				return 'ODBC';
			}else{
				return $this->dbType;
			}
		}
	}

    /**
     +----------------------------------------------------------
     * where分析
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $where 查询条件
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseWhere($where)
    {
        $whereStr = '';
		if(is_string($where) || is_null($where)) { 
            //支持String作为条件 如使用 > like 等
            $whereStr = $where; 
        }else{
            if(is_instance_of($where,'HashMap')){
				$where	=	$where->toArray();
			}elseif(is_object($where)){
                $where = get_object_vars($where);
            }
			if(array_key_exists('_logic',$where)) {
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$operate	=	' '.strtoupper($where['_logic']).' ';
				unset($where['_logic']);
			}else{
				// 默认进行 AND 运算
				$operate	=	' AND ';
			}
			foreach ($where as $key=>$val){
				if(strpos($key,',')) {
					$key	=	explode(',',$key);
					if('MYSQL' == $this->getDbType()) {
						array_walk($key, array($this, 'addSpecialChar'));
					}
				}elseif('MYSQL' == $this->getDbType()) {
					$key = $this->addSpecialChar($key);
				}
				$whereStr .= "( ";
				if(is_array($val)) {
						if(is_array($key)) {
							// 多字段组合查询
							$num	=	count($key);
							if(empty($val[$num])) $val[$num]	=	'AND'; // 运算规则默认为AND
							for ($i=0;$i<$num;$i++){
								if(is_array($val[$i])) {
									// 判断条件
									if(is_string($val[$i][0]) && preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|LIKE)F$/i',$val[$i][0])) {
										$op	=	$this->comparison[strtolower(substr($val[$i][0],0,-1))];
										$str[] = ' ('.$key[$i].' '.$op.' '.$val[$i][1].') ';
									}elseif(is_string($val[$i][0]) && preg_match('/IN/i',$val[$i][0])){
										$zone	=	is_array($val[$i][1])? implode(',',$val[$i][1]):$val[$i][1];
										$str[] =  ' ('.$key[$i].' '.strtoupper($val[$i][0]).' ('.$zone.') ';
									}elseif(is_string($val[$i][0]) && preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|LIKE)$/i',$val[$i][0])){
										$op	=	$this->comparison[strtolower($val[$i][0])];
										$str[] = ' ('.$key[$i].' '.$op.' '.$this->fieldFormat($val[$i][1]).') ';
									}else{
										$str[] = ' ('.$key[$i].' '.$val[$i][0].' '.$this->fieldFormat($val[$i][1]).') ';
									}
								}else{
									// 默认为 ＝
									$str[] = ' ('.$key[$i].' = '.$this->fieldFormat($val[$i]).') ';
								}
							}
							$whereStr .= implode(strtoupper($val[$num]),$str);
						}else{
							if(is_string($val[0]) && preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|LIKE|BETWEEN)$/i',$val[0])) {
								// 是否存在比较运算
								$whereStr .= $key.$this->comparison[strtolower($val[0])].' '.$this->fieldFormat($val[1]);
							}elseif(is_string($val[0]) && preg_match('/IN/i',$val[0])){
								// 支持 IN 运算
								$zone	=	is_array($val[1])? implode(',',$val[1]):$val[1];
								$whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
							}elseif(is_string($val[0]) && preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|LIKE)F$/i',$val[0])){
								$whereStr .= $key.' '.$this->comparison[strtolower(substr($val[0],0,-1))].' '.$val[1];
							}else {
								// 区间比较只有两个段
								if(is_array($val[0])) {
									// 给出运算符
									$operate1	=	$this->comparison[strtolower($val[0][0])];
									$data1	=	$val[0][1];
								}else{
									// 默认的运算符
									$operate1	=	'>=';
									$data1	=	$val[0];
								}
								if(is_array($val[1])) {
									$operate2	=	$this->comparison[strtolower($val[1][0])];
									$data2	=	$val[1][1];
								}else{
									$operate2	=	'<=';
									$data2	=	$val[1];
								}
								if(empty($val[2])) $val[2]	=	'AND'; // 运算规则默认为AND
								if(in_array(strtoupper(trim($val[2])),array('AND','OR','XOR'))) {
									$whereStr .= $key.' '.$operate1.' '.$this->fieldFormat($data1).' '.$val[2].' '.$key.' '.$operate2.' '.$this->fieldFormat($data2);
								}
							}
						}
				}else {
					//对字符串类型字段采用模糊匹配
					if(preg_match('/(\w*)('.C('LIKE_MATCH_FIELDS').')(\w*)/i',$key)) {
						$val = '%'.$val.'%';
						$whereStr .= $key." LIKE ".$this->fieldFormat($val);
					}else {
						$whereStr .= $key." = ".$this->fieldFormat($val);
					}                    	
				}
				$whereStr .= ' )'.$operate;
			}
			$whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    /**
     +----------------------------------------------------------
     * order分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $order 排序
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseOrder($order)
    {
        $orderStr = '';
        if(is_array($order))     
            $orderStr .= ' ORDER BY '.implode(',', $order);
        else if(is_string($order) && !empty($order)) 
            $orderStr .= ' ORDER BY '.$order;
        return $orderStr;
    }

    /**
     +----------------------------------------------------------
     * join分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $join Join表达式
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseJoin($join)
    {
		$joinStr = '';
		if(!empty($join)) {
			if(is_array($join)) {
				foreach ($join as $key=>$val)
				{
					$joinStr .= ' LEFT JOIN ' . key($val['FROM']) . ' AS ' . current($val['FROM']) . ' ON (' . $val['ON'] . ') ';
				}
			}else{
				$joinStr .= ' LEFT JOIN ' .$join;
			}
		}
        return $joinStr;
    }

    /**
     +----------------------------------------------------------
     * limit分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param string $limit  
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseLimit($limit)
    {
        $limitStr    = '';
        if(!empty($limit)) {
			if(in_array(strtoupper(C('DB_TYPE')),array('PGSQL','SQLITE'))) {
				// PgSQL
				$limit	=	explode(',',$limit);
				if(count($limit)>1) {
					$limitStr .= ' LIMIT '.$limit[1].' OFFSET '.$limit[0];
				}else{
					$limitStr .= ' LIMIT '.$limit[0];
				}
				
			}elseif('MSSQL'== strtoupper(C('DB_TYPE'))){
				// MsSQL
				$limitStr = ' TOP '.$limit;
			}else{
				// 其它数据库
	            $limitStr .= ' LIMIT '.$limit;
			}
		}
        return $limitStr;
    }

    /**
     +----------------------------------------------------------
     * group分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $group  
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseGroup($group)
    {
        $groupStr = '';
        if(is_array($group))     
            $groupStr .= ' GROUP BY '.implode(',', $group);
        else if(is_string($group) && !empty($group)) 
            $groupStr .= ' GROUP BY '.$group;
        return empty($groupStr)?'':$groupStr;
    }

    /**
     +----------------------------------------------------------
     * having分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param string $having  
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseHaving($having)
    {
        $havingStr = '';
        if(is_string($having) && !empty($having))     
            $havingStr .= ' HAVING '.$having;
        return $havingStr;
    }

    /**
     +----------------------------------------------------------
     * fields分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $fields
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseFields($fields)
    {
        if(is_array($fields)) {
            if('MYSQL' == $this->getDbType()) {
                array_walk($fields, array($this, 'addSpecialChar'));
            }
            $fieldsStr = implode(',', $fields);
        }else if(is_string($fields) && !empty($fields)) {
            if( false === strpos($fields,'`') ) {
                $fields = explode(',',$fields);
                if('MYSQL' == $this->getDbType()) {
            	    array_walk($fields, array($this, 'addSpecialChar'));
                }
                $fieldsStr = implode(',', $fields);
            }else {
            	$fieldsStr = $fields;
            }
        }else 
            $fieldsStr = '*';
        return $fieldsStr;
    }

    /**
     +----------------------------------------------------------
     * value分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $values
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseValues($values)
    {
        if(is_array($values)) {
            array_walk($values, array($this, 'fieldFormat'));
            $valuesStr = implode(',', $values);
        }
        else if(is_string($values)) 
            $valuesStr = $values;
        return $valuesStr;
    }

    /**
     +----------------------------------------------------------
     * set分析
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $sets
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function parseSets($sets)
    {
        $setsStr  = '';
        if(is_object($sets) && !empty($sets)){
            if(is_instance_of($sets,'HashMap')){
                $sets = $sets->toArray();
            }
        }
        $sets    = auto_charset($sets,C('OUTPUT_CHARSET'),C('DB_CHARSET'));
        if(is_array($sets)){
            foreach ($sets as $key=>$val){
                if(!is_null($val) && is_scalar($val)){
					// 过滤空值元素 和复合元素
					if('MYSQL' == $this->getDbType()) {
	                    $setsStr .= $this->addSpecialChar($key)." = ".$this->fieldFormat($val).",";
					}else {
	                    $setsStr .= "$key = ".$this->fieldFormat($val).",";
					}
                }
            }
            $setsStr = substr($setsStr,0,-1);
        }else if(is_string($sets)) { 
            $setsStr = $sets; 
        }
        return $setsStr;
    }

    /**
     +----------------------------------------------------------
     * 设置锁机制 
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	protected function setLockMode() {
		if('ORACLE' == $this->getDbType()) {
			return ' FOR UPDATE NOWAIT ';
		}
		return ' FOR UPDATE ';
	}

    /**
     +----------------------------------------------------------
     * 字段格式化
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $value  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function fieldFormat(&$value) 
    {
        if(is_int($value)) {
            $value = intval($value);
        } else if(is_float($value)) {
            $value = floatval($value);
        } elseif(preg_match('/^\(\w*(\+|\-|\*|\/)?\w*\)$/i',$value)){
			// 支持在字段的值里面直接使用其它字段
			// 例如 (score+1) (name) 必须包含括号
			$value = $value;
		}else if(is_string($value)) {
            $value = '"'.$this->escape_string($value).'"';
        }
        return $value;
    }

    /**
     +----------------------------------------------------------
     * 字段和表名添加` 符合
     * 保证指令中使用关键字不出错 针对mysql
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param mixed $value  
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    protected function addSpecialChar(&$value) 
    {
        if( '*' == $value ||  false !== strpos($value,'(') || false !== strpos($value,'.') || false !== strpos($value,'`')) {
            //如果包含* 或者 使用了sql方法 则不作处理
        }
        elseif(false === strpos($value,'`') ) {
            $value = '`'.$value.'`';
        }
        return $value;
    }

    /**
     +----------------------------------------------------------
     * 是否为数据库更改操作
     +----------------------------------------------------------
     * @access protected 
     +----------------------------------------------------------
     * @param string $query  SQL指令
     +----------------------------------------------------------
     * @return boolen 如果是查询操作返回false
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function isMainIps($query)
    {
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|'
                . 'CREATE|DROP|'
                . 'LOAD DATA|SELECT .* INTO|COPY|'
                . 'ALTER|GRANT|REVOKE|'
                . 'LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $query)) {
            return true;
        }
        return false;
    }

    /**
     +----------------------------------------------------------
     * 查询数据方法，支持动态缓存
     * 动态缓存方式为可配置，默认为文件方式
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql  查询语句
     * @param boolean $cache  是否缓存查询
     * @param boolean $lazy  是否惰性加载
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($sql='',$cache=true,$lazy=false,$lock=false)
    {
        if(!empty($sql)) {
            $this->queryStr = $sql;
        }
		if($lock) {
			$this->queryStr .= $this->setLockMode();
		}
		if($lazy) {
			// 延时读取数据库
			return $this->lazyQuery($this->queryStr);
		}
        if(C('DB_CACHE_ON') && $cache) {// 启用数据库缓存
            $guid   =   md5($this->queryStr);
            //获取缓存数据
			$length = S($guid.'_count');
			if(!$length) {
				$data = S($guid);
			}else{
				$data =   new ResultSet();
				for($i=0; $i<$length; $i++) {
					$array   =   S($guid.'_'.$i);
					foreach($array as $key=>$val) {
						$data->add($val);
					}
				} 				
            } 
            if(!empty($data)){
                return $data;
            }
        }
        // 进行查询
        $data = $this->_query();
        if(C('DB_CACHE_ON')){
            //如果启用数据库缓存则重新缓存
            $rowNums    =   $this->numRows;  //总的记录数
            if($rowNums > C('DB_CACHE_MAX')) {
                //如果记录数超过设置范围，多文件缓存，
                //避免serialize超时
                $length =   ceil($rowNums / C('DB_CACHE_MAX'));   //缓存文件数
                for($i=0; $i<$length; $i++) {
                    //依次缓存
                    S($guid.'_'.$i,$data->range(C('DB_CACHE_MAX') * $i,C('DB_CACHE_MAX')));
                }
                //记录缓存文件数目
                S($guid.'_count',$length);                        
            }else {
                //全部数据缓存
                S($guid,$data);                    	
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * 延时查询方法
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql  查询语句
     +----------------------------------------------------------
     * @return ResultIterator
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function lazyQuery($sql='') {
		// 返回ResultIterator对象 在操作数据的时候再进行读取
		import("Think.Db.ResultIterator");
		return new ResultIterator($sql);
	}

    /**
     +----------------------------------------------------------
     * 数据库操作方法
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql  执行语句
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($sql='',$lock=false)
    {
        if(empty($sql)) {
        	$sql  = $this->queryStr;
        }
		if($lock) {
			$sql .= $this->setLockMode();
		}
        return $this->_execute($sql);
    }

    /**
     +----------------------------------------------------------
     * 自动判断进行查询或者执行操作
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sql SQL指令
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function autoExec($sql='',$lazy=false,$lock=false,$cache=false) 
    {
        if(empty($sql)) {
        	$sql  = $this->queryStr;
        }
        if($this->isMainIps($this->queryStr)) {
        	$this->execute($sql,$lock);
        }else {
        	$this->query($sql,$cache,$lazy,$lock);
        }
    }

    /**
     +----------------------------------------------------------
     * 查找记录 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $where 数据
     * @param string $tables  数据表名
     * @param string $fields  字段名
     * @param string $order  排序
     * @param string $limit  
     * @param string $group
     * @param string $having
     * @param boolean $cache 是否缓存
     * @param boolean $lazy 是否惰性加载
     * @param boolean $lock 是否加锁
     +----------------------------------------------------------
     * @return ArrayObject
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function find($where,$tables,$fields='*',$order=null,$limit=null,$group=null,$having=null,$join=null,$cache=true,$lazy=false,$lock=false)
    {
		if('MSSQL' == strtoupper(C('DB_TYPE'))) {
			$this->queryStr = 'SELECT '.$this->parseLimit($limit)
							.$this->parseFields($fields)
							.' FROM '.$tables
							.$this->parseJoin($join)
							.$this->parseWhere($where)
							.$this->parseGroup($group)
							.$this->parseHaving($having)
							.$this->parseOrder($order);
		}else{
			$this->queryStr = 'SELECT '.$this->parseFields($fields)
							.' FROM '.$tables
							.$this->parseJoin($join)
							.$this->parseWhere($where)
							.$this->parseGroup($group)
							.$this->parseHaving($having)
							.$this->parseOrder($order)
							.$this->parseLimit($limit);
		}
        return $this->query('',$cache,$lazy,$lock);
    }

    /**
     +----------------------------------------------------------
     * 插入记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $map 数据
     * @param string $table  数据表名
     * @param mixed $multi  是否插入多条记录
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function add($map,$table,$multi=false)
    {
		if($multi) {
			return $this->addAll($map,$table);
		}
        if(!is_array($map)) {
            if(!is_instance_of($map,'HashMap')){
                throw_exception(L('_DATA_TYPE_INVALID_'));
            }
        }
		// 去掉复合对象 保证关联数据属性不会被保存导致错误
		foreach ($map as $key=>$val){
			if(is_scalar($val)) {
				$data[$key]	=	$val;
			}
		}
        //转换数据库编码
        $data    = auto_charset($data,C('OUTPUT_CHARSET'),C('DB_CHARSET'));
        $fields = array_keys($data);
        if('MYSQL' == $this->getDbType()) {
        	array_walk($fields, array($this, 'addSpecialChar'));
        }
        $fieldsStr = implode(',', $fields);
        $values = array_values($data);
        array_walk($values, array($this, 'fieldFormat'));

        $valuesStr = implode(',', $values);
        $this->queryStr =    'INSERT INTO '.$table.' ('.$fieldsStr.') VALUES ('.$valuesStr.')';
        return $this->execute();
    }

    /**
     +----------------------------------------------------------
     * 插入多个记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $map 数据列表
     * @param string $table  数据表名
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function addAll($map,$table)
    {
		if('MYSQL'==$this->getDbType()) {
			//转换数据库编码
			$fields = array_keys((array)$map[0]);
			array_walk($fields, array($this, 'addSpecialChar'));
			$fieldsStr = implode(',', $fields);	
			$values = array();
			foreach ($map as $data){
				// 去掉复合对象 保证关联数据属性不会被保存导致错误
				foreach ($data as $key=>$val){
					if(is_scalar($val)) {
						$_data[$key]	=	$val;
					}
				}
				$_data    = auto_charset($_data,C('OUTPUT_CHARSET'),C('DB_CHARSET'));
				$_values = array_values($_data);
				array_walk($_values, array($this, 'fieldFormat'));
				$values[] = '( '.implode(',', $_values).' )';			
			}
			$valuesStr = implode(',',$values);
			$this->queryStr =    'INSERT INTO '.$table.' ('.$fieldsStr.') VALUES '.$valuesStr;
			return $this->execute();
		}else{
			$this->startTrans();
			foreach ($map as $data){
				$this->add($data,$table);
			}
			$this->commit();
		}
    }

    /**
     +----------------------------------------------------------
     * 删除记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $where 为条件Map、Array或者String
     * @param string $table  数据表名
     * @param string $limit  
     * @param string $order
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function remove($where,$table,$limit='',$order='')
    {
        $this->queryStr = 'DELETE FROM '.$table.$this->parseWhere($where).$this->parseOrder($order).$this->parseLimit($limit);;
        return $this->execute();
    }

    /**
     +----------------------------------------------------------
     * 清空表
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $table  数据表名
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function clear($table)
    {
		return $this->execute( 'TRUNCATE TABLE '.$this->parseTables($table));        	
    }

    /**
     +----------------------------------------------------------
     * 更新记录
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $sets 数据
     * @param string $table  数据表名
     * @param string $where  更新条件
     * @param string $limit  
     * @param string $order
     * @param boolean $lock 是否加锁
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function save($sets,$table,$where,$limit=0,$order='',$lock=false)
    {
        $this->queryStr = 'UPDATE '.$table.' SET '.$this->parseSets($sets).$this->parseWhere($where).$this->parseOrder($order).$this->parseLimit($limit);
        return $this->execute('',$lock);
    }

    /**
     +----------------------------------------------------------
     * 保存某个字段的值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 要保存的字段名
     * @param string $value  字段值
     * @param string $table  数据表
     * @param string $where 保存条件  
     * @param boolean $asString 字段值是否为字符串
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setField($field,$value,$table,$condition,$asString=false) {
		if(false === strpos($value,'(') || $asString) {
			// 更新字段内容为纯字符串
			// 如果有'(' 视为 SQL指令更新
			$value	=	'"'.$value.'"';
		}
		$this->queryStr	=	'UPDATE '.$table.' SET '.$field.'='.$value.$this->parseWhere($condition);
		return $this->execute();
	}

    /**
     +----------------------------------------------------------
     * 增加某个字段的值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 要保存的字段名
     * @param string $table  数据表
     * @param string $where 保存条件  
     * @param integer $step 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setInc($field,$table,$condition,$step=1) {
		return $this->setField($field,'('.$field.'+'.$step.')',$table,$condition);
	}

    /**
     +----------------------------------------------------------
     * 减少某个字段的值
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $field 要保存的字段名
     * @param string $table  数据表
     * @param string $where 保存条件  
     * @param integer $step 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function setDec($field,$table,$condition,$step=1) {
		return $this->setField($field,'('.$field.'-'.$step.')',$table,$condition);
	}

    /**
     +----------------------------------------------------------
     * 查询次数更新或者查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $times 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function Q($times='') {
		static $_times = 0;
		if(empty($times)) {
			return $_times;
		}else{
			$_times++;
			// 记录开始执行时间
			$this->beginTime = array_sum(explode(' ', microtime()));
		}
	}

    /**
     +----------------------------------------------------------
     * 写入次数更新或者查询
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param mixed $times 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	public function W($times='') {
		static $_times = 0;
		if(empty($times)) {
			return $_times;
		}else{
			$_times++;
			// 记录开始执行时间
			$this->beginTime = array_sum(explode(' ', microtime()));
		}
	}

}//类定义结束
?>