<?php 

    require_once '../functions.php';

    $current_user=xiu_get_current_user();
    xiu_mysqli();
    $add_content='';
    $add_title='';
    $add_slug='';
    function add_posts(){
      global $current_user,$add_content,$add_title,$add_slug;

      $add_title = isset($_POST['title']) ? $_POST['title'] : '' ;
      $add_content = isset($_POST['content']) ? $_POST['content'] : '' ;
      $add_slug = isset($_POST['slug']) ? $_POST['slug'] : '' ;

      if (empty($_POST['slug'])) {
        $GLOBALS['prompt'] = '请输入别名';
        return;
      }
      if (empty($_POST['title'])) {
        $GLOBALS['prompt'] = '请输入标题';
        return;
      }
      if (empty($_POST['content'])) {
        $GLOBALS['prompt'] = '请输入内容';
        return;
      }
      if (empty($_POST['category']) || $_POST['category'] === 'all') {
        $GLOBALS['prompt'] = '请选择分类';
        return;
      }
      if (empty($_POST['status']) || $_POST['status'] === 'all') {
        $GLOBALS['prompt'] = '请选择状态';
        return;
      }

      $title=$_POST['title'];
      $slug=$_POST['slug'];
      $content=$_POST['content'];
      $category=$_POST['category'];
      $status=$_POST['status'];
      $date=date('Y-m-d H:i:s');
      $row_slug=xiu_mysqli_fetch_one("select * from posts where slug = '{$slug}' limit 1 ");
      if ($row_slug>0) {
          $GLOBALS['prompt'] = '别名重复';
          return;
      }

      $rows=xiu_mysqli_execute("insert into posts values (null, '{$slug}', '{$title}','' , '{$date}' ,'{$content}', 0, 0 ,'{$status}', '{$current_user['id']}', '{$category}'); ");

      $GLOBALS['prompt'] = $rows>0 ? '发布成功' :'添加失败' ;
    }

    function edit_posts(){
      global $current_user,$current_edit_post;

      if (empty($_POST['category']) || $_POST['category'] === 'all') {
        $GLOBALS['prompt'] = '请选择分类';
        return;
      }
      if (empty($_POST['status']) || $_POST['status'] === 'all') {
        $GLOBALS['prompt'] = '请选择状态';
        return;
      }
      
      if (empty($_POST['slug']) || $_POST['slug'] === $current_edit_post['slug']) {
        $_POST['slug']= $current_edit_post['slug'];
      }
      if (empty($_POST['title']) || $_POST['title'] === $current_edit_post['title']) {
        $_POST['title']= $current_edit_post['title'];
      }

      $title=$_POST['title'];
      $slug=$_POST['slug'];
      $content=$_POST['content'];
      $category=$_POST['category'];
      $status=$_POST['status'];
      $date=empty($_POST['created'])? date('Y-m-d H:i:s') : $_POST['created'] ;
      $id=$current_edit_post['id'] ;  //需要编辑的数据的对应id 

      $row_slug=xiu_mysqli_fetch_one("select * from posts where slug = '{$slug}' limit 1 ");
      if ($row_slug['slug'] != $slug && $row_slug>0) {
          $GLOBALS['prompt'] = '别名重复';
          return;
      }

      $rows= xiu_mysqli_execute("update posts set slug = '{$slug}', title= '{$title}', created= '{$date}', content='{$content}', status='{$status}', user_id='{$current_user['id']}', category_id='{$category}' where id ={$id} ;");

      $current_edit_post['title'] = $title;
      $current_edit_post['slug'] = $slug;
      $current_edit_post['content'] = $content;
      $_GET['category'] = $category;
      $_GET['status'] = $status;
      $current_edit_post['created'] = $date;
      $GLOBALS['prompt'] = $rows>0 ? '更新成功' :'更新失败' ;
    }

    if (empty($_GET['id'])) {
      if ($_SERVER['REQUEST_METHOD']=== 'POST') {
        add_posts();
      }
    }
    else{
      $current_edit_post=xiu_mysqli_fetch_one("select * from posts where id= {$_GET['id']} ");
      if ($_SERVER['REQUEST_METHOD'] ==='POST') {
        edit_posts();
      }
    }

    $categories=xiu_mysqli_fetch('select * from categories');

    xiu_close_mysqli();

    function convert_created($created){   //转换时间
      //date_default_timezone_set('PRC');
      $timp=strtotime($created);
      return date('Y-m-d\TH:i:s',$timp);
      //2016-01-11T16:00:00
    }
    function echo_ifelse($show_data){
      return $show_data =='' ? '' : $show_data ;
    }
    
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Add new post &laquo; Admin</title>
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
      
      <!-- 有错误信息时展示 -->
      <?php if (isset($prompt) && ($prompt === '发布成功' || $prompt === '更新成功') ): ?>
        <div class="alert alert-success">
          <?php echo $prompt ?>
        </div>
        <?php elseif(isset($prompt)): ?>
        <div class="alert alert-danger">
          <?php echo $prompt ?>
        </div>
      <?php endif ?>
      <div class="page-title">
        <?php if (isset($current_edit_post)): ?>
          <h1>编辑《<?php echo $current_edit_post['title'] ?>》</h1>
        <?php endif ?>
      </div>

      <?php if (isset($current_edit_post)): ?>
      <form class="row" action="<?php echo $_SERVER['PHP_SELF'] ;?>?id=<?php echo $current_edit_post['id'];?>" method='post' >
          <div class="col-md-9">
          <div class="form-group">
            <label for="title">标题</label>
            <input id="title" class="form-control input-lg" name="title" type="text" placeholder="<?php echo $current_edit_post['title']; ?>">
          </div>
          <div class="form-group">
            <label for="content">内容</label>
            <textarea id="content" name="content" cols="30" rows="10" placeholder="内容">
              <?php echo isset($current_edit_post['content']) ? $current_edit_post['content'] : '' ;?>
            </textarea>
          </div>
        </div>
        
        <div class="col-md-3">
            <div class="form-group">
            <label for="slug">slug</label>
            <input id="slug" class="form-control" name="slug" type="text" placeholder="<?php echo $current_edit_post['slug'] ?>">
            <p class="help-block">https://zce.me/post/<strong>slug</strong></p>
          </div>
          <!-- <div class="form-group">
            <label for="feature">头像</label>
            show when image chose
            <img class="help-block thumbnail" style="display: none">
            <input id="feature" class="form-control" name="feature" type="file">
          </div> -->
          <div class="form-group">
            <label for="category">所属分类</label>
            <select name="category" class="form-control input-sm">
              <option value="all">所有分类</option>
              <?php foreach ($categories as $value): ?>
                <option value="<?php echo $value['id']; ?>"<?php echo isset($_GET['category']) && $_GET['category']== $value['id'] ? ' selected' : '' ?>>
                  <?php echo $value['name'] ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="form-group">
            <label for="created">发布时间</label>
            <input id="created" class="form-control" name="created" type="datetime-local" value="<?php echo convert_created($current_edit_post['created']) ;?>">
          </div>
          <div class="form-group">
            <label for="status">状态</label>
            <select name="status" class="form-control input-sm">
              <option value="all">所有状态</option>
              <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status']== 'drafted' ? ' selected' : '' ?>>草稿</option>
              <option value="published"<?php echo isset($_GET['status']) && $_GET['status']== 'published' ? ' selected' : '' ?>>已发布</option>
              <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status']== 'trashed' ? ' selected' : '' ?>>回收站</option>
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">保存</button>
            <a href="/admin/post-add.php">取消编辑</a>
          </div>
        </div>
      </form>
      <?php else: ?><!-- --------------------------------------------------------------------------- -->

      <form class="row" action="<?php echo $_SERVER['PHP_SELF'] ;?>" method='post' >
          <div class="col-md-9">
          <div class="form-group">
            <label for="title">标题</label>
            <input id="title" class="form-control input-lg" name="title" type="text" placeholder="文章标题" value="<?php echo echo_ifelse($add_title); ?>">
          </div>
          <div class="form-group">
            <label for="content">内容</label>
            <textarea id="content" name="content" cols="30" rows="10" placeholder="内容">
              <?php echo echo_ifelse($add_content); ?>
            </textarea>
          </div>
        </div>
        
        <div class="col-md-3">
            <div class="form-group">
            <label for="slug">slug</label>
            <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo echo_ifelse($add_slug); ?>">
            <p class="help-block">https://zce.me/post/<strong>slug</strong></p>
          </div>
          <!-- <div class="form-group">
            <label for="feature">头像</label>
            show when image chose
            <img class="help-block thumbnail" style="display: none">
            <input id="feature" class="form-control" name="feature" type="file">
          </div> -->
          <div class="form-group">
            <label for="category">所属分类</label>
            <select name="category" class="form-control input-sm">
              <option value="all">所有分类</option>
              <?php foreach ($categories as $value): ?>
                <option value="<?php echo $value['id']; ?>"<?php echo isset($_GET['category']) && $_GET['category']== $value['id'] ? ' selected' : '' ?>>
                  <?php echo $value['name'] ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="form-group">
            <label for="status">状态</label>
            <select name="status" class="form-control input-sm">
              <option value="all">所有状态</option>
              <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status']== 'drafted' ? ' selected' : '' ?>>草稿</option>
              <option value="published"<?php echo isset($_GET['status']) && $_GET['status']== 'published' ? ' selected' : '' ?>>已发布</option>
              <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status']== 'trashed' ? ' selected' : '' ?>>回收站</option>
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">发布</button>
          </div>
        </div>
      </form>
      <?php endif ?>
    </div>
  </div>

  <?php $current_page='post-add' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/ueditor/ueditor.config.js"></script>
  <script src="/static/assets/vendors/ueditor/ueditor.all.js"></script>
  <script>
    UE.getEditor('content', {
      initialFrameHeight: 400,
      autoHeight: false
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
