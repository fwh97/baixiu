<?php 

	if (empty($_FILES['file']['error'])) {  //error 错误
		// PHP 在会自动接收客户端上传的文件到一个临时的目录
		$temp_file = $_FILES['file']['tmp_name'];
		// 我们只需要把文件保存到我们指定上传目录
		$Suffix_name= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

		$target_file = '../static/uploads/temp-img/'.'temp'. uniqid().'.' .$Suffix_name;

		if (move_uploaded_file($temp_file, $target_file)) {
		  $image_file =substr($target_file ,2);
		} 
	}

header('Content-Type: application/json');

if (empty($image_file)) {
  echo json_encode(array(
    'success' => false
  ));
} else {
  echo json_encode(array(
    'success' => true,
    'data' => $image_file
  ));
}