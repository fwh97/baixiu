<?php 

    require_once '../functions.php';
    $current_password=xiu_get_current_user();

    xiu_mysqli();

    function edit_password(){
      global $current_password;
     
      if (empty($_POST['old'])) {
        $GLOBALS['prompt'] ='密码未输入';
        return;
      }
      if (empty($_POST['password'])) {
        $GLOBALS['prompt'] ='新密码未输入';
        return;
      }
      if (empty($_POST['old'])) {
        $GLOBALS['prompt'] ='确认密码未输入';
        return;
      }

      $id=$current_password['id']; 

      $password=xiu_mysqli_fetch_one("select * from users where id = {$id}");

      if ($_POST['old'] !== $password['password'] ) {
        $GLOBALS['prompt'] = '输入旧密码错误';
        return;
      }

      if ($_POST['password'] !== $_POST['confirm']) {
        $GLOBALS['prompt'] = '两次密码不一';
        return;
      }

      $rows=xiu_mysqli_execute("update users set password ='{$_POST['password']}' where id ={$id} ");
      $GLOBALS['prompt'] = $rows>0 ? '修改成功' :'修改失败' ;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      edit_password();
    }

    mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Password reset &laquo; Admin</title>
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
        <h1>修改密码</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <?php if (isset($prompt) && ($prompt === '修改成功') ): ?>
        <div class="alert alert-success">
          <?php echo $prompt ?>
        </div>
        <?php elseif(isset($prompt)): ?>
        <div class="alert alert-danger">
          <?php echo $prompt ?>
        </div>
      <?php endif ?>

      <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="form-group">
          <label for="old" class="col-sm-3 control-label">旧密码</label>
          <div class="col-sm-7">
            <input id="old" name="old" class="form-control" type="password" placeholder="旧密码">
          </div>
        </div>
        <div class="form-group">
          <label for="password" class="col-sm-3 control-label">新密码</label>
          <div class="col-sm-7">
            <input id="password" name="password" class="form-control" type="password" placeholder="新密码">
          </div>
        </div>
        <div class="form-group">
          <label for="confirm" class="col-sm-3 control-label">确认新密码</label>
          <div class="col-sm-7">
            <input id="confirm" name="confirm" class="form-control" type="password" placeholder="确认新密码">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-7">
            <button type="submit" class="btn btn-primary">修改密码</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <?php $current_page='password-reset' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
