<?php 
	require_once 'config.php';
	session_start();

	function xiu_get_current_user(){
		if (empty($_SESSION['current_login_user'])) {
      	// 没有当前登录用户信息，意味着没有登录
	      header('Location: /admin/login.php');
	      exit();
	    }

	    return $_SESSION['current_login_user'];
	}

	$conn;
	$query;

	function xiu_mysqli(){  //连接数据库函数  避免连续连接数据库
		global $conn;
		$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if (!$conn) {
	   		exit('连接失败');
		}
	}

	function xiu_mysqli_fetch($sql){
		global $query,$conn;
		//xiu_mysqli($sql);
		$query = mysqli_query($conn, $sql);
	  	if (!$query) {
		    return false;// 查询失败
		}

		while ($row = mysqli_fetch_assoc($query)) {
			$result[] = $row;  
		}
		return isset($result) ? $result : false  ;
	}

	function xiu_mysqli_fetch_one($sql){
		$res=xiu_mysqli_fetch($sql);

		return isset($res[0]) ? $res[0] : null ;
	}
	
	function xiu_mysqli_execute($sql){
		global $query,$conn,$affected_rows;
		//xiu_mysqli($sql);
		$query = mysqli_query($conn, $sql);
	  	if (!$query) {
		    return false;
		}
		
		// 对于增删修改类的操作都是获取受影响行数
		$affected_rows=mysqli_affected_rows($conn);

		return $affected_rows;
	}

	function xiu_close_mysqli(){  //关闭数据库函数
		global $query,$conn,$affected_rows;
		mysqli_free_result($query) ;
		mysqli_close($conn);
	}

	function xiu_jsonp($json){
		if (empty($_GET['callback'])) {           //判断是否传入get['callback'] 这个参数 
		  header('Content-Type: application/json'); //传了就明确是script标签发送的请求
		  echo $json;
		  exit();                               // 停止往下执行
		}

		header('Content-Type: application/javascript');  //把这个响应体以js类型去解析 作用调用这个预定好的函数

		$fn=$_GET['callback'];
		echo "typeof {$fn} === 'function' && {$fn}({$json})";
	}