<?php 
namespace App\Body\Db;
/**
* mysqli 
* 
* @author Jason
* @time 2015-12-1
*/
use App\Body\Interfaces\IDb;

class Mysqli implements IDb {
	/**
	 * 连接标识符
	 *
	 * @var mysqli
	 */
	protected $mysqli;
	
	//用于存放实例化的对象
	protected static $instance = null;
	
	//存放当前操作的错误信息
	protected $error           = null;
	
	protected $result;
	 
	/**
	 * 公共静态方法获取实例化的对象
	 */
	public static function getInstance(array $config) {
		if (is_null(static::$instance)) {
			static::$instance = new static($config);
		}
		return static::$instance;
	}
	 
	//私有克隆
	protected function __clone() {}
	
	
	/**
	 * 数据库的配置信息
	 *
	 * @var string
	 */
	protected $host;
	protected $username;
	protected $password;
	protected $db;
	protected $port;
	protected $charset;
	
	/**
	 * 公有构造
	 *
	 * @access public
	 *
	 * @internal param array|string $config_path 数据库的配置信息.
	 */
	private function __construct($config) {
		$this->host     = $config['host'];
		$this->username = $config['user'];
		$this->password = $config['password'];
		$this->db       = $config['database'];
		$this->charset  = $config['encoding'];
		$this->port     = $config['port'];
		$this->connect();
	}
	
	/**
	 * 连接数据库
	 *
	 */
	private function connect() {
		if (empty($this->host)) {
			die ('Mysql host is not set');
		}
		$this->mysqli = new \mysqli ($this->host, $this->username, $this->password, $this->db, $this->port)
		or die('There was a problem connecting to the database');
		/* check connection */
		/*if (mysqli_connect_errno()) {
		 printf("Connect failed: %s\n", mysqli_connect_error());
		 exit();
		}*/
		if ($this->charset) {
			$this->mysqli->set_charset($this->charset);
		}
	}
	
	/**
	 * 返回连接符，能使用原生语法
	 */
	public function mysqli () {
		if (!$this->mysqli) {
			$this->connect();
		}
		return $this->mysqli;
	}
	
	
	/**
	 * 查询
	 * @param string $sql
	 * @return array
	 */
	public function select($sql) {
		return $this->getArray($sql);
	}
	
	/**
	 * 插入
	 * @param string $sql
	 * @return integer id
	 */
	public function insert($sql) {
		$this->execute($sql);
		return $this->lastInsertId();
	}
	
	/**
	 * 修改
	 * @param string $sql
	 * @return integer 改变的行数
	 */
	public function update($sql){
		$this->execute($sql);
		return  $this->rows();
	}
	
	/**
	 * 删除
	 * @param string $sql
	 * @return integer 删除的行数
	 */
	public function delete($sql) {
		$this->execute($sql);
		return $this->rows();
	}
	
	/**
	 * 预处理
	 * @param unknown $sql
	 */
	public function prepare($sql) {
		$this->result = $this->pdo->prepare($sql);
	}
	
	/**
	 * 绑定值
	 * @param unknown $param
	 */
	public function bind($param) {
		foreach ($param as $key => $value) {
			$this->result->bindParam($key, $value);
		}
	}
	
	/**
	 * 得到当前执行语句的错误信息
	 *
	 * @access public
	 *
	 * @return string 返回错误信息,
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * 获取Object结果集
	 * @param string $sql
	 * @return multitype:mixed
	 */
	public function getObject($sql = null) {
		$this->execute($sql);
		$result = array();
		while (!!$objs = mysqli_fetch_object($this->result) ) {
			$result[] = $objs;
		}
		return $result;
	}
	
	/**
	 * 获取关联数组
	 * @param string $sql
	 */
	public function getArray($sql = null) {
		$this->execute($sql);
		$result = array();
		while (!!$objs = mysqli_fetch_assoc($this->result) ) {
			$result[] = $objs;
		}
		return $result;
	}
	
	/**
	 * 返回上一步执行受影响的行数
	 *
	 * @access public
	 *
	 */
	public function rows($end = TRUE) {
		$rows = mysqli_affected_rows($this->mysqli);
		if ($end) {
			$this->close();
		}
		return $rows;
	}
	
	/**
	 * 返回上一步执行INSERT生成的id
	 *
	 * @access public
	 *
	 */
	public function lastInsertId($end = TRUE) {
		$id = mysqli_insert_id($this->mysqli);
		if($end) {
			$this->close();
		}
		return $id;
	}
	/**
	 * 返回结果集的行数
	 *
	 * @access public
	 *
	 */
	public function rowCount($end = TRUE) {
		$count = mysqli_num_rows($this->result);
		if($end) {
			$this->close();
		}
		return $count;
	}
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param string $sql 多行查询语句
	 */
	public function execute($sql)
	{
		if (empty($sql)) {
			return;
		}
		$this->result = $this->_mysqli->query($sql);
		return $this->result;
	}
	
	/**
	 * 预执行SQL语句，并绑定值  ？
	 *
	 * @access public
	 *
	 * @param string $sql SQL语句
	 * @param array $param 参数
	 */
	public function prepare($sql, $param) {
		$this->result = mysqli_prepare($this->mysqli, $sql);
		mysqli_stmt_bind_param($this->result, $param );
		mysqli_stmt_execute($this->result);
		mysqli_stmt_bind_result($this->result, $district);
		mysqli_stmt_fetch($this->result);
		printf("%s is in district %s\n", $city, $district);
		mysqli_stmt_close($this->result);
		$this->close();
	}
	
	/**
	 * 执行多行SQL语句
	 *
	 * @access public
	 *
	 * @param string $query 多行查询语句
	 */
	public function multi_query($query)  {
		$result = array();
		if (mysqli_multi_query($this->mysqli, $query)) {                                           //执行多个查询
			do {
				if ($this->result = mysqli_store_result($this->mysqli)) {
					$result[] = $this->getList();
					mysqli_free_result($this->result);
				}
				/*if (mysqli_more_results($this_mysqli)) {
				 echo ("-----------------<br>");                   //连个查询之间的分割线
				 }*/
			} while (mysqli_next_result($this->mysqli));
		}
		$this->close();
		return $result;
	}
	
	/**
	 * 关闭和清理
	 *
	 * @access public
	 *
	 *
	 */
	public function close() {
		if (!empty($this->result) && !is_bool($this->result)) {
			mysqli_free_result($this->result);
		}
		mysqli_close($this->mysqli);
	}
	
	public function getError() {
		return mysqli_error($this->mysqli);
	}
	
	public function __destruct() {
		$this->close();
	}
}