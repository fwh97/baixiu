<?php 

    require_once '../functions.php';

    xiu_get_current_user();
    xiu_mysqli();

    $where='1=1';  // where查询后面要带条件 后面查询用到这变量
    $search='';

    if (isset($_GET['category']) && $_GET['category'] !== 'all') {  //筛选分类 !all 有选择类型  all代表所有
      if ((int)$_GET['category']>4 || (int)$_GET['category']<0) {
        $_GET['category']='1';
      }
      $where.= ' and posts.category_id='.$_GET['category'];
      $search .= '&category=' . $_GET['category'];
    }

    if (isset($_GET['status']) && $_GET['status'] !== 'all') {   //筛选状态

      $where.=" and posts.status='".$_GET['status']."'";
      //$where .= " and posts.status = '{$_GET['status']}'";    //where 查询条件为 get传过来的那个值
      $search .= '&status=' . $_GET['status'];
    }

    $total_count = (int)xiu_mysqli_fetch_one("select count(1) as count from posts
    inner join categories on posts.category_id = categories.id
    inner join users on posts.user_id = users.id
    where {$where};")['count'];  //查询获取到数据总行数

    $size=10;
    $max_pages = (int)ceil($total_count / $size);  //获取最大页码数 ceil 向上取整

    $get_page=empty($_GET['page'])||$_GET['page']<1 ? 1 : (int)$_GET['page'] ;
    $page=$get_page>$max_pages ? $max_pages : $get_page;

    $offset= ($page-1)*$size ;

    $visiables=5;                   //需要展示最多的页码数
    $begin=$page-($visiables-1)/2;  //得到左边最开始页码 最小数值

    $begin=$begin<1 ? 1 : $begin ;  //确保了 begin 不会小于 1
    $end=$begin+$visiables-1;    //得到右边最后页码 最大数值

    $end=$end>= $max_pages ? $max_pages : $end ;

    //关联查询 减少服务端不必要多余的查询 ，同时也方便写
    $posts=xiu_mysqli_fetch("   
        select 
        posts.id,
        posts.title,
        users.nickname as user_name,
        categories.`name` as category_name,
        posts.created,
        posts.`status`,
        posts.category_id
        from posts 
        inner join categories 
        on posts.category_id = categories.id
        inner join users 
        on posts.user_id = users.id
        where {$where}
        order by posts.created desc 
        limit {$offset},{$size};
      ");

    $categories = xiu_mysqli_fetch('select * from categories;');
    xiu_close_mysqli();

    function convert_status($status){  //通过英文值状态转换成中文
      $dict=array(
        'published' => '已发布',
        'drafted'=> '草稿',
        'trashed' => '回收站'
      );
      return isset($status) ? $dict[$status] : '未知';
    }

    function convert_created($created){   //转换时间
      //date_default_timezone_set('PRC');
      $timp=strtotime($created);
      return date('Y年m月d日<b\r>H:i:s',$timp);
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include_once 'inc/navbar.php' ?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.html" class="btn btn-primary btn-xs">写文章</a>
      </div>
      
      <div class="page-action">
        <!-- show when multiple checked -->
        <a id="btn_delete" class="btn btn-danger btn-sm" href="/admin/delete-data.php" style="display: none">批量删除</a>
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $value): ?>
              <option value="<?php echo $value['id']; ?>"<?php echo isset($_GET['category']) && $_GET['category']== $value['id'] ? ' selected' : '' ?>>
                <?php echo $value['name'] ?>
              </option>
            <?php endforeach ?>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status']== 'drafted' ? ' selected' : '' ?>>草稿</option>
            <option value="published"<?php echo isset($_GET['status']) && $_GET['status']== 'published' ? ' selected' : '' ?>>已发布</option>
            <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status']== 'trashed' ? ' selected' : '' ?>>回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="?page=<?php echo $page-1 .$search ;?>">上一页</a></li>
          <?php for ($i=$begin; $i <= $end ; $i++) :?> <!--循环初始值与结束值由?page传参的值进行计算后得到-->
          <li<?php echo $i === $page ? ' class="active"' : '' ?>><a href="?page=<?php echo $i.$search; ?>"><?php echo $i; ?></a></li>
          <?php endfor ?>
          <li><a href="?page=<?php echo $page+1 .$search;?>">下一页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="60"><input id='all_checkbox' type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($posts)): ?>
          <?php foreach ($posts as $value): ?>
            <tr>
            <td class="text-center"><input type="checkbox" data-id='<?php echo $value['id'] ;?>'></td>
            <td><?php echo $value['title'] ?></td>
            <td><?php echo $value['user_name'] ?></td>
            <td><?php echo $value['category_name'] ?></td>
            <td class="text-center"><?php echo convert_created($value['created']) ;?></td>
            <td class="text-center"><?php echo convert_status($value['status']); ?></td>
            <td class="text-center">
              <a href="/admin/post-add.php?id=<?php echo $value['id']?>&category=<?php echo $value['category_id'] ?>&status=<?php echo $value['status'] ?>" class="btn btn-default btn-xs">编辑</a>
              <a href="/admin/delete-data.php?id=<?php echo $value['id']; ?>&from=<?php echo 'posts' ?>" class="btn btn-danger btn-xs">删除</a>
            </td>
          </tr>
          <?php endforeach ?>
          <?php endif ?>
          
        </tbody>
      </table>
    </div>
  </div>
  <?php $current_page='posts' ?>

  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function($){

      var aCheckbox=$('tbody input');

      var oBtnDelete=$('#btn_delete');
      //var oAllBox=$('#all_checkbox');
      // 定义一个数组记录被选中的
      var aCheckboxId=[];

      $('thead input').on('change', function(){
          var checked=$(this).prop('checked');
          aCheckbox.prop('checked',checked).trigger('change');  //trigger 调用对象的事件方法
      });
      
      aCheckbox.on('change',function() {
        
        var id=$(this).data('id');  //data 操作由data-开头自定义的属性

        if ($(this).prop("checked")) {
          //aCheckboxId.indexOf(id) === -1||aCheckboxId.push(id);    //把勾选中的对应的数据的id值添加进这个数
          aCheckboxId.includes(id) || aCheckboxId.push(id)  //重复的id不添加
          // includes  判断一个数组是否包含一个指定的值，如果是返回 true，否则false
        }
        else{
          aCheckboxId.splice(aCheckboxId.indexOf(id),1);  //删除取消掉勾选存进这个数组里的这条数据的id值
          if (aCheckboxId.length == 0) {
            $('thead input').prop('checked',false);
          }
        }
        console.log(aCheckboxId);
        // 有任意一个 checkbox 选中就显示批量删除，反之隐藏
        aCheckboxId.length ? oBtnDelete.fadeIn() : oBtnDelete.fadeOut();
        oBtnDelete.prop('search', '?id=' + aCheckboxId +'&from=posts' );

      });
    });
  </script>
  <script>NProgress.done()</script>
</body>
</html>
