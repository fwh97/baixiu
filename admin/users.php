<?php 

    require_once '../functions.php';

    xiu_get_current_user();

    function add_users(){
      if (empty($_POST['email'])) {
        $GLOBALS['prompt'] = '请输入邮箱';
        return ;
      }
      if (empty($_POST['slug'])) {
        $GLOBALS['prompt'] = '请输入别名';
        return;
      }
      if (empty($_POST['nickname'])) {
        $GLOBALS['prompt'] = '请输入昵称';
        return;
      }
      if (empty($_POST['password'])) {
        $GLOBALS['prompt'] = '请输入密码';
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
                              
      $avatars = $avatar ? $avatar : '/static/assets/img/default.png';
      $email=$_POST['email'];
      $slug=$_POST['slug'];
      $password=$_POST['password'];
      $nickname=$_POST['nickname'];
      
      $status='activated';

      xiu_mysqli();

      $row_email=xiu_mysqli_fetch("select * from users where email = '{$email}' limit 1 ");
      if ($row_email>0) {
          $GLOBALS['prompt'] = '你的邮箱以注册';
          return;
      }
      $row_slug=xiu_mysqli_fetch("select * from users where slug = '{$slug}' limit 1 ");
      if ($row_slug>0) {
          $GLOBALS['prompt'] = '别名重复';
          return;
      }

      $rows=xiu_mysqli_execute("insert into users values (null, '{$slug}', '{$email}', '{$password}', '{$nickname}', '{$avatars}', null, '{$status}'); ");

      $GLOBALS['prompt'] = $rows>0 ? '添加成功' :'添加失败' ;
    }

    function edit_users(){
      global $current_edit_users;

      if (empty($_POST['email'])) {
        $GLOBALS['prompt'] = '请输入邮箱';
        return ;
      }
      if (empty($_POST['slug'])) {
        $GLOBALS['prompt'] = '请输入别名';
        return;
      }
      if (empty($_POST['nickname'])) {
        $GLOBALS['prompt'] = '请输入昵称';
        return;
      }
      if (empty($_POST['password'])) {
        $GLOBALS['prompt'] = '请输入密码';
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

      $avatars = empty($avatar) ? $current_edit_users['avatar'] :  $avatar;
      $email=$_POST['email'] ;
      $slug=$_POST['slug'] ;
      $password=$_POST['password'];
      $nickname=$_POST['nickname'];

      $id=$current_edit_users['id'] ;  //需要编辑的数据的对应id 
      
      $rows= xiu_mysqli_execute("update users set slug = '{$slug}', email= '{$email}', password= '{$password}', nickname='{$nickname}', avatar='{$avatars}' where id ={$id} ;");
      $current_edit_users['nickname'] = $nickname;
      $current_edit_users['slug'] = $slug;
      $current_edit_users['email'] = $email;
      $current_edit_users['password'] = $password;

      $GLOBALS['prompt'] = $rows <= 0 ? '修改失败！' : '更新成功';
    }


    if (empty($_GET['id'])) {
      if ($_SERVER['REQUEST_METHOD']=== 'POST') {
        add_users();
      }
    }
    else{
      xiu_mysqli();
      $current_edit_users=xiu_mysqli_fetch_one("select * from users where id= ".$_GET['id']);

      if ($_SERVER['REQUEST_METHOD'] ==='POST') {
        edit_users();
      }
    }

    xiu_mysqli();
    $users=xiu_mysqli_fetch('select * from users;');
    xiu_close_mysqli();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Users &laquo; Admin</title>
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
        <h1>用户</h1>
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

      <div class="row">
        <div class="col-md-4">
          <?php if (isset($current_edit_users)): ?>
            
            <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $current_edit_users['id'] ?> " method="post" enctype="multipart/form-data">
            <h2>编辑《<?php echo $current_edit_users['nickname']; ?>》</h2>
            <div class="form-group">
              <label class="col-sm-3 control-label">头像</label>
              <div class="col-sm-6">
                <label class="form-image">
                  <input id="upload" name="avatar" accept="image/*" type="file">
                  <img src="<?php echo $current_edit_users['avatar'] ?>">
                  <i class="mask fa fa-upload"></i>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="email">更改邮箱</label>
              <input id="email" class="form-control" name="email" type="email" placeholder="更改邮箱">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
              <p class="help-block">https://zce.me/author/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <label for="nickname">昵称</label>
              <input id="nickname" class="form-control" name="nickname" type="text" placeholder="昵称">
            </div>
            <div class="form-group">
              <label for="password">新密码</label>
              <input id="password" class="form-control" name="password" type="password" placeholder="新密码">
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">修改</button>
              <a href="/admin/users.php" class="btn btn-primary">取消修改</a>
            </div>
          </form>         
          <?php else: ?>
          <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?> " method="post" enctype="multipart/form-data">
            <h2>添加新用户</h2>
            <div class="form-group">
              <label class="col-sm-3 control-label">头像</label>
              <div class="col-sm-6">
                <label class="form-image">
                  <input id="upload" name="avatar" type="file" accept="image/*">
                  <img src="<?php echo '/static/assets/img/default.png' ?>">
                  <i class="mask fa fa-upload"></i>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="email">邮箱</label>
              <input id="email" class="form-control" name="email" type="email" placeholder="邮箱">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
              <p class="help-block">https://zce.me/author/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <label for="nickname">昵称</label>
              <input id="nickname" class="form-control" name="nickname" type="text" placeholder="昵称">
            </div>
            <div class="form-group">
              <label for="password">密码</label>
              <input id="password" class="form-control" name="password" type="text" placeholder="密码">
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">添加</button>
            </div>
          </form>
          <?php endif ?>

        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a id="btn_delete" class="btn btn-danger btn-sm" href="/admin/delete-data.php" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
               <tr>
                <th class="text-center" width="60"><input id="all_checkbox" type="checkbox">全选</th>
                <th class="text-center" width="80">头像</th>
                <th>邮箱</th>
                <th>别名</th>
                <th>昵称</th>
                <th>状态</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php if (isset($users)): ?>
              <?php foreach ($users as $value): ?>
                <tr>
                  <td class="text-center"><input type="checkbox" data-id='<?php echo $value['id'] ;?>'></td>
                  <td class="text-center"><img class="avatar" src="<?php echo $value['avatar'] ?>"></td>
                  <td><?php echo $value['email'] ?></td>
                  <td><?php echo $value['slug'] ?></td>
                  <td><?php echo $value['nickname'] ?></td>
                  <td><?php echo $value['status'] ?></td>
                  <td class="text-center">
                    <a href="/admin/users.php?id=<?php echo $value['id']; ?>" class="btn btn-default btn-xs">编辑</a>
                    <a href="/admin/delete-data.php?id=<?php echo $value['id']; ?>&from=<?php echo 'users' ?>" class="btn btn-danger btn-xs">删除</a>
                  </td>
                </tr>
              <?php endforeach ?>
              <?php endif ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php $current_page='users' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function($){

      var aCheckbox=$('tbody input');

      var oBtnDelete=$('#btn_delete');
      var oAllBox=$('#all_checkbox');
      // 定义一个数组记录被选中的
      var aCheckboxId=[];

      oAllBox.on('change', function(){
          var checked=$(this).prop('checked');
          aCheckbox.prop('checked',checked).trigger('change');  //trigger 调用对象的事件方法
      });
      
      aCheckbox.on('change',function() {
        
        var id=$(this).data('id');  //data 操作由data-开头自定义的属性

        if ($(this).prop("checked")) {
          //aCheckboxId.indexOf(id) === -1||aCheckboxId.push(id);    //把勾选中的对应的数据的id值添加进这个数
          aCheckboxId.includes(id) || aCheckboxId.push(id)  
          // includes  判断一个数组是否包含一个指定的值，如果是返回 true，否则false
        }
        else{
          aCheckboxId.splice(aCheckboxId.indexOf(id),1);  //删除取消掉勾选存进这个数组里的这条数据的id值
        }

        // 有任意一个 checkbox 选中就显示批量删除，反之隐藏
        aCheckboxId.length ? oBtnDelete.fadeIn() : oBtnDelete.fadeOut();
        oBtnDelete.prop('search', '?id=' + aCheckboxId +'&from=users' );

      });

      $('#upload').on('change', function () {
        // 选择文件后异步上传文件
        var formData = new FormData()
        formData.append('file', $(this).prop('files')[0])
        console.log( $(this).prop('files'))
        console.log( $(this).prop('files')[0])
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
              console.log(res.data)
              $('#upload').siblings('img').attr('src', res.data).fadeIn()
            } else {
              alert('上传文件失败')
            }
          }
        })
      })


    });

  </script>

  <script>NProgress.done()</script>
</body>
</html>
