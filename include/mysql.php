<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

# 文件名称:mysql.php 2009-11-1 16:18:23
# 数据库操作类
class dbmysql {
	var $querynum = 0;
	var $link;
	function  dbconn($con_db_host,$con_db_id,$con_db_pass, $con_db_name = '',$db_charset='utf8',$pconnect = 0) {
                if (!$this->link=new mysqli($con_db_host, $con_db_id, $con_db_pass, $con_db_name)) {
                     $this->halt('Can not connect to MySQL server');
                }
                if ($db_charset!='latin1') {
                   $this->link->set_charset($db_charset);
                }
        }	
	function move_first($query) {
		$query->data_seek(0);
	}

	function set_autocommit($flag=TRUE) {
		return $this->link->autocommit($flag);
	}
	function begin_trans($flag = MYSQLI_TRANS_START_READ_WRITE) {
		return $this->link->begin_transaction($flag);
	}

	function rollback($flag = MYSQLI_TRANS_START_READ_WRITE) {
		return $this->link->rollback($flag);
	}

	function commit($flag = MYSQLI_TRANS_START_READ_WRITE) {
		return $this->link->commit($flag);
	}

	function select_db($dbname) {
		return $this->link->select_db($dbname);
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		if(!$query)
		{
			return "";
		}
		else
		{
			return $query->fetch_array($result_type);
		}
	}
	
	function update($table, $bind=array(),$where = '')
	{
	    $set = array();
	    foreach ($bind as $col => $val) {
	        $set[] = "$col = '$val'";
	        unset($set[$col]);
	    }
	    $sql = "UPDATE "
             . $table
             . ' SET ' . implode(',', $set)
             . (($where) ? " WHERE $where" : '');
            $this->query($sql);
	}
	
	
	function insert($table, $bind=array())
	{
	    $set = array();
	    foreach ($bind as $col => $val) {
	        $set[] = "`$col`";
	        $vals[] = "'$val'";
	    }
	   $sql = "INSERT INTO "
             . $table
             . ' (' . implode(', ', $set).') '
             . 'VALUES (' . implode(', ', $vals).')';
           $this->query($sql);
           return $this->insert_id();
	}
	
	/**
	* 执行sql语句，只得到一条记录
	* @param string sql语句
	* @return array
	*/
	function get_one($sql, $type = '')
	{
		$query = $this->query($sql, $type);
		$rs = $this->fetch_array($query);
		$this->free_result($query);
                return $rs ;
	}
	


	function query($sql, $type = '') {
	   /*$func = $type == 'UNBUFFERED' && @function_exists('mysqli_unbuffered_query') ?
			'mysqli_unbuffered_query' : 'mysqli_query';*/
		if(!($query = $this->link->query($sql))) {
                     error_log( "query failed " . $sql . " error no " . $this->errno() . " error " . $this->error());
                     if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();
				global $config_db;
				$db_settings = parse_ini_file("$config_db");
	            @extract($db_settings);
				$this->dbconn($con_db_host,$con_db_id,$con_db_pass, $con_db_name = '',$pconnect);
				$this->query($sql, 'RETRY'.$type);
			} 
		}
		$this->querynum++;
		return $query;
	}
	
	function counter($table_name,$where_str="", $field_name="*")
	{
	    $where_str = trim($where_str);
	    if(strtolower(substr($where_str,0,5))!='where' && $where_str) $where_str = "WHERE ".$where_str;
	    $query = " SELECT COUNT($field_name) FROM $table_name $where_str ";
            $result = $this->link->query($query);
	    $fetch_row = $result->fetch_row();
	    return $fetch_row[0];
	}

	function affected_rows() {
		return $this->link->affected_rows;
	}

	function error() {
		return (($this->link) ? $this->link->error : mysqli_error());
	}

	function errno() {
		return intval(($this->link) ? $this->link->errno : mysqli_errno());
	}

	function resulta($query, $row) {
		$query->data_seek($row);
                return $query->fetch_row()[0];
	}

	function num_rows($query) {
		return $query->num_rows;
	}

	function num_fields($query) {
		return $query->field_count;
	}

	function free_result($query) {
		return $query->free_result();
	}

	function insert_id() {
		return ($id = $this->link->insert_id()) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		return $query->fetch_row();
	}

	function fetch_fields($query) {
		return $query->fetch_field();
	}

	function version() {
		return $this->link->get_server_info();
	}

	function close() {
		return $this->link->close();
	}

	function halt($message = '',$sql) {
	     $sqlerror = $this->link->error();
		 $sqlerrno = mysqli_errno();
		 $sqlerror = str_replace($dbhost,'dbhost',$sqlerror);
		 echo"<html><head><title>CSSInfo</title><style type='text/css'>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:10px;}A { TEXT-DECORATION: none;}a:hover{ text-decoration: underline;}TD { BORDER-RIGHT: 1px; BORDER-TOP: 0px; FONT-SIZE: 16pt; COLOR: #000000;}</style><body>\n\n";
		echo"<table style='TABLE-LAYOUT:fixed;WORD-WRAP: break-word'><tr><td>";
		echo"<br><br><b>The URL Is</b>:<br>http://$_SERVER[HTTP_HOST]$REQUEST_URI";
		echo"<br><br><b>MySQL Server Error</b>:<br>$sqlerror  ( $sqlerrno )";
		echo"</td></tr></table>";
		exit;
	}
}
?>
