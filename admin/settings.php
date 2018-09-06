<?php 

    require_once '../functions.php';

    xiu_get_current_user();

    xiu_mysqli();
    // 处理表单请求
     $data = xiu_mysqli_fetch('select * from options');
      $options = array();

      foreach ($data as $item) {
        $options[$item['key']] = $item['value'];
      }
// ========================================
    function edit_settings(){
      global $options;
      if($_FILES['site_logo']['error'] == UPLOAD_ERR_OK){ 
        var_dump($_FILES['site_logo']);
        $images = $_FILES['site_logo'];
        if ($images['size'] > 2 * 1024 * 1024) {
          $GLOBALS['prompt'] = '图片文件过大';
          return;
        }
        // 校验类型
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($images['type'], $allowed_types)) {
          $GLOBALS['prompt'] = '这是不支持的图片格式 ,支持的格式：jpeg，png，gif';
          return;
        }
            //pathinfo  返回文件路径的信息  //PATHINFO_EXTENSION 文件后缀名；
        $img_url='../static/uploads/user-'.uniqid().'-.'.pathinfo($images['name'], PATHINFO_EXTENSION);                        
        if(!move_uploaded_file($images['tmp_name'], $img_url)){
          $GLOBALS['prompt']='上传头像失败';
          return;
        }
        $site_logos=substr($img_url, 2);
      }

      $site_logo = empty($site_logos) ? $options['site_logo'] :  $site_logos;
      var_dump($site_logo);
      xiu_mysqli_execute(sprintf('update `options` set `value` = \'%s\' where `key` = \'site_logo\'', $site_logo));

      if (!empty($_POST['site_name'])) {
        xiu_mysqli_execute(sprintf('update `options` set `value` = "%s" where `key` = \'site_name\'', $_POST['site_name']));
      }
      if (!empty($_POST['site_description'])) {
        xiu_mysqli_execute(sprintf('update `options` set `value` = "%s" where `key` = \'site_description\'', $_POST['site_description']));
      }
      if (!empty($_POST['site_keywords'])) {
        xiu_mysqli_execute(sprintf('update `options` set `value` = \'%s\' where `key` = \'site_keywords\'', $_POST['site_keywords']));
      }

      xiu_mysqli_execute(sprintf('update `options` set `value` = \'%s\' where `key` = \'comment_status\'', !empty($_POST['comment_status'])));

      xiu_mysqli_execute(sprintf('update `options` set `value` = \'%s\' where `key` = \'comment_reviewed\'', !empty($_POST['comment_reviewed'])));

      $options['site_logo']=$site_logo;
      $options['site_name']=$_POST['site_name'];
      $options['site_keywords']=$_POST['site_keywords'];
      $options['site_description']=$_POST['site_description'];

    }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {  //   \'转译 \'
     
        edit_settings();
      }
// 查询数据
// ========================================
  mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Settings &laquo; Admin</title>
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
        <h1>网站设置</h1>
      </div>
      <!-- 有错误信息时展示 -->
      
      <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ;?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="site_logo" class="col-sm-2 control-label">网站图标</label>
          <div class="col-sm-6">
            <!-- <input id="site_logo" name="site_logo" type="hidden"> -->
            <label class="form-image">
              <input id="upload" name="site_logo" type="file">
              <img src="<?php echo $options['site_logo'] !== ''? $options['site_logo'] : '/static/assets/img/logo.png' ?>">
              <i class="mask fa fa-upload"></i>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="site_name" class="col-sm-2 control-label">站点名称</label>
          <div class="col-sm-6">
            <input id="site_name" name="site_name" class="form-control" type="type" placeholder="站点名称" value="<?php echo $options['site_name'] ;?>">
          </div>
        </div>
        <div class="form-group">
          <label for="site_description" class="col-sm-2 control-label">站点描述</label>
          <div class="col-sm-6">
            <textarea id="site_description" name="site_description" class="form-control" placeholder="站点描述" cols="30" rows="6"><?php echo $options['site_description'] ?> </textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="site_keywords" class="col-sm-2 control-label">站点关键词</label>
          <div class="col-sm-6">
            <input id="site_keywords" name="site_keywords" class="form-control" type="type" placeholder="站点关键词" value="<?php echo $options['site_keywords']; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">评论</label>
          <div class="col-sm-6">
            <div class="checkbox">
              <label><input id="comment_status" name="comment_status" type="checkbox" checked>开启评论功能</label>
            </div>
            <div class="checkbox">
              <label><input id="comment_reviewed" name="comment_reviewed" type="checkbox" checked>评论必须经人工批准</label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-6">
            <button type="submit" class="btn btn-primary">保存设置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php $current_page='settings' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function () {
      // 异步上传文件
      $('#upload').on('change', function () {
        // 选择文件后异步上传文件
        var formData = new FormData()
        formData.append('file', $(this).prop('files')[0])

        // 上传图片
        $.ajax({
          url: '/admin/upload.php',
          cache: false,
          contentType: false,
          processData: false,
          data: formData,
          type: 'post',
          success: function (res) {
            if (res.success) {
              //$('#site_logo').val(res.data)
              $('#upload').siblings('img').attr('src', res.data).fadeIn()
            } else {
              alert('上传文件失败')
            }
          }
        })
      })
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
