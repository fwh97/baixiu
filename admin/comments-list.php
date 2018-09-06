<?php 

	require_once '../functions.php';

	xiu_mysqli();

	$total_count=xiu_mysqli_fetch("select count(1) as num from comments inner join posts on comments.post_id=posts.id ")[0]['num'];  //查询返回值：array 这方法返回的也是array 嵌套了
	$size=isset($_GET['size']) && is_numeric($_GET['size']) ? $_GET['size'] : 10 ;
   	$page=isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

   	if ($page <= 0) {
	  header('Location: /admin/comments-list.php?page=1&size=' . $size);
	  exit;
	}

   	$total_pages = ceil($total_count / $size);

   	if ($page > $total_pages) {
	  // 跳转到最后一页
	  header('Location: /admin/comments-list.php?page=' . $total_pages . '&size=' .$size);
	  exit;
	}
								//sprintf 把百分号（%）符号替换成一个作为参数进行传递的变量 返回返回值
   								//printf  把百分号（%）符号替换成一个作为参数进行传递 输出格式化的字符串
	$comments=xiu_mysqli_fetch(sprintf(
		"select comments.*,
		posts.title as post_title
		from comments
		inner join posts on comments.post_id=posts.id
		order by comments.created desc 
		limit %d ,%d ",($page-1)*$size,$size  
	));

	//var_dump($comments);
	xiu_close_mysqli();
	header('Content-Type: application/json');

	echo json_encode(array(
		'data' => $comments,
		'total_count' => $total_count
	));
	