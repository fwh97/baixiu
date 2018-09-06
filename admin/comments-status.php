<?php 
	require_once '../functions.php';

	header('Content-Type: application/json');

	if (empty($_POST['id']) && empty($_POST['status'])) {
		exit(json_encode( array(
			'success' => false,
    		'prompts' => '缺少必要参数'
		)));
	}
	xiu_mysqli();

	$status_update=xiu_mysqli_execute("update comments set status ='{$_POST['status']}' where id in ({$_POST['id']}) ;");

	mysqli_close($conn);
	echo json_encode(
		array('success' => $status_update >0)
	);