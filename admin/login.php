<?php 
  require_once '../config.php';
  session_start();

  function login(){

    if (empty($_POST['email'])) {
      $GLOBALS['promtp'] = '用戶名沒填';
      return ;
    }
    if (empty($_POST['password'])) {
      $GLOBALS['promtp'] = '密碼沒填';
      return ;
    }

    $email=$_POST['email'];
    $password=$_POST['password'];

    $conn=mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
      exit('<h1>連接錯誤</h1>');
    }

    $query = mysqli_query($conn, "select * from users where email = '{$email}' limit 1;");


    if (!$query) {
      $GLOBALS['promtp'] = '登录失败，请重试！';
      return;
    }

    $user = mysqli_fetch_assoc($query);

    if (!$user) {
      // 用户名不存在
      $GLOBALS['promtp'] = '邮箱与密码不匹配';
      return;
    }
    // 一般密码是加密存储的
    if ($user['password'] !== $password) {
      // 密码不正确
      $GLOBALS['promtp'] = '邮箱与密码不匹配';
      return;
    }

    $_SESSION['current_login_user'] = $user;

    // 一切OK 可以跳转
    header('Location: /admin/');
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    login();
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['out']) && $_GET['out']=='out') {
    unset($_SESSION['current_login_user']); //删除session 这个标识 退出账号
  }
 ?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
</head>
<body>
  <div class="login">
    <form class="login-wrap" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" novalidate autocomplete="off">
      <img class="avatar" src="/static/assets/img/default.png">
      <!-- 有错误信息时展示 -->
      <?php if (isset($promtp)): ?>
        <div class="alert alert-danger">
          <strong>错误！</strong> <?php echo $promtp ?>
        </div>
      <?php endif ?>
      <!--  -->
      
      <div class="form-group">
        <label for="email" class="sr-only">邮箱</label>
        <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus>
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">密码</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="密码">
      </div>
      <button class="btn btn-primary btn-block">登 录</button>
    </form>
  </div>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script>
    $(function ($){
      console.log("a");

      var reEmail=/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;

      $('#email').on('blur', function() {
        var value=$(this).val();
        if (!value || !reEmail.test(value)) return;

        $.get('/admin/api/avatar.php', {email:value } , function(data) {

          if (!data)return ;

          $('.avatar').fadeOut( function() {
            $(this).on('load', function (){ $(this).fadeIn(); }) .attr('src' ,data);
          });

        });

      });
      
    });
  </script>
</body>
</html>
