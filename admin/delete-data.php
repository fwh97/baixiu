<?php 
	require_once '../functions.php';

	if (empty($_GET['id'])) {
	  exit('缺少必要参数');
	}
	if (empty($_GET['from'])) {
	  exit('缺少必要参数');
	}
	$get_id = $_GET['id'];
	$get_from = $_GET['from'];

	//$get_data=explode("_", $get_id);
	xiu_mysqli();

	$delete=xiu_mysqli_execute("delete from " .$get_from." where id in ({$get_id}) ;");
	//xiu_jsonp($delete);

	mysqli_close($conn);
	// http 中的 referer 用来标识当前请求的来源
	
	if($get_from !== 'comments'){                 //comments 用ajax方式请求的  其它传统同步请求的
		header("Location: ". $_SERVER['HTTP_REFERER']);
	}
	else{
		echo json_encode( $delete >0 );
		header('Content-Type: appliction/json');
	}
	