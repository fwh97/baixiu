<?php 

    require_once '../functions.php';

    $current_user=xiu_get_current_user();

    xiu_mysqli();

    function edit_users(){
      global $current_user,$current_edit_user;

      if (empty($_POST['email']) || $_POST['email'] !== $current_user['email']) {
        $GLOBALS['prompt'] = '请输入邮箱';
        return ;
      }
      if (empty($_POST['slug']) || $_POST['slug'] === $current_edit_post['slug']) {
        $_POST['slug']= $current_edit_user['slug'];
      }
      if (empty($_POST['nickname'])) {
        $GLOBALS['prompt'] = '请输入昵称';
        return;
      }

      if($_FILES['avatar']['error'] == UPLOAD_ERR_OK){ //pathinfo  返回文件路径的信息
        $images = $_FILES['avatar'];
        if ($images['size'] > 1 * 1024 * 1024) {
          $GLOBALS['prompt'] = '图片文件过大';
          return;
        }
        // 校验类型
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($images['type'], $allowed_types)) {
          $GLOBALS['prompt'] = '这是不支持的图片格式';
          return;
        }

        $img_url='../static/uploads/user-'.uniqid().'-.'.pathinfo($images['name'], PATHINFO_EXTENSION);                        //PATHINFO_EXTENSION 文件后缀名；
        if(!move_uploaded_file($images['tmp_name'], $img_url)){
          $GLOBALS['prompt']='上传头像失败';
          return;
        }
        $avatar=substr($img_url, 2);
      }

      $avatars = empty($avatar) ? $current_edit_user['avatar'] :  $avatar;
      //$email=$_POST['email'] ;
      $slug=$_POST['slug'] ;
      $nickname=$_POST['nickname'];
      $bio=$_POST['bio'];

      $row_slug=xiu_mysqli_fetch_one("select * from users where slug = '{$slug}' limit 1 ");
      if ($row_slug['slug'] != $slug && $row_slug>0) {
          $GLOBALS['prompt'] = '别名重复';
          return;
      }
      $id=$current_user['id'] ;  //需要编辑的数据的对应id 
      
      $rows= xiu_mysqli_execute("update users set slug = '{$slug}', bio= '{$bio}', nickname='{$nickname}', avatar='{$avatars}' where id ={$id} ;");
      $current_edit_user['nickname'] = $nickname;
      $current_edit_user['slug'] = $slug;
      //$current_edit_user['email'] = $email;
      $current_edit_user['bio'] = $bio;

      $GLOBALS['prompt'] = $rows <= 0 ? '修改失败！' : '更新成功';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      edit_users();
    }
    $current_edit_user=xiu_mysqli_fetch_one('select * from users where id= '.$current_user['id']);

    //xiu_mysqli_fetch_one('select * from users where id= '.$_GET['id']);
    mysqli_close($conn);
    //xiu_close_mysqli();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Dashboard &laquo; Admin</title>
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
        <h1>我的个人资料</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <?php if (isset($prompt) && ($prompt === '添加成功' || $prompt === '更新成功') ): ?>
        <div class="alert alert-success">
          <?php echo $prompt ?>
        </div>
        <?php elseif(isset($prompt)): ?>
        <div class="alert alert-danger">
          <?php echo $prompt ?>
        </div>
      <?php endif ?>

      <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ;?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label class="col-sm-3 control-label">头像</label>
          <div class="col-sm-6">
            <label class="form-image">
              <input id="upload" name="avatar" type="file">
              <img src="<?php echo isset($current_edit_user['avatar']) ? $current_edit_user['avatar'] : '/static/assets/img/default.png' ;?>">
              <i class="mask fa fa-upload"></i>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="email" class="col-sm-3 control-label">邮箱</label>
          <div class="col-sm-6">
            <input id="email" class="form-control" name="email" type="type" value="<?php echo $current_edit_user['email'] ;?>" placeholder="邮箱" readonly>
            <p class="help-block">登录邮箱不允许修改</p>
            <!-- readonly 禁止修改文本 -->
          </div>
        </div>
        <div class="form-group">
          <label for="slug" class="col-sm-3 control-label">别名</label>
          <div class="col-sm-6">
            <input id="slug" class="form-control" name="slug" type="type" value="<?php echo $current_edit_user['slug'] ;?>" placeholder="slug">
            <p class="help-block">https://zce.me/author/<strong>zce</strong></p>
          </div>
        </div>
        <div class="form-group">
          <label for="nickname" class="col-sm-3 control-label">昵称</label>
          <div class="col-sm-6">
            <input id="nickname" class="form-control" name="nickname" type="type" value="<?php echo $current_edit_user['nickname'] ;?>" placeholder="昵称">
            <p class="help-block">限制在 2-16 个字符</p>
          </div>
        </div>
        <div class="form-group">
          <label for="bio" class="col-sm-3 control-label">简介</label>
          <div class="col-sm-6">
            <textarea id="bio" class="form-control" name="bio" placeholder="说的什么。。。" cols="30" rows="6"><?php echo $current_edit_user['bio'] ;?></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-6">
            <button type="submit" class="btn btn-primary">更新</button>
            <a class="btn btn-link" href="/admin/password-reset.php">修改密码</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php $current_page='profile' ?>
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
