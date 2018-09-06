<?php
   require_once '../functions.php';

    xiu_get_current_user();

    function add_category(){

      if (empty($_POST['name']) || empty($_POST['slug'])) {
        $GLOBALS['prompt'] = '请完整填写表单！';
        return ;
      }
      $GLOBALS['name']=$_POST['name'];
      $GLOBALS['slug']=$_POST['slug'];

      xiu_mysqli();
      
      $row=xiu_mysqli_fetch_one("select * from categories where slug = '{$GLOBALS['slug']}' limit 1 ");
      if ($row >0  ) {
        $GLOBALS['prompt']='这个别名重复';
        return ;
      }
      $rows=xiu_mysqli_execute("insert into categories values (null, '". $GLOBALS['slug'] ."', '" . $GLOBALS['name'] ."'); ");

      $GLOBALS['prompt'] =$rows > 0 ?'添加成功' : '添加失败' ;
    }

    function edit_category(){
      global $current_edit_category;

      if (empty($_POST['name']) || empty($_POST['slug'])) {
        $GLOBALS['prompt'] = '请完整填写表单！';
        return ;
      }

      $id=$current_edit_category['id'] ;  //需要编辑的数据的对应id 
      $name=empty($_POST['name'] )? $current_edit_category['name'] : $_POST['name'] ;
      $slug = empty($_POST['slug']) ? $current_edit_category['slug'] : $_POST['slug'];

      if ( ($_POST['slug'] === $current_edit_category['slug'] && $_POST['name'] === $current_edit_category['name'] )  ) {
        //header('Location: /admin/categories.php');
        $GLOBALS['prompt'] = '未修改';
        return ;
      }

      $rows= xiu_mysqli_execute("update categories set slug = '{$slug}', name= '{$name}' where id ={$id} ;");
      $current_edit_category['name'] = $name;
      $current_edit_category['slug'] = $slug;

      $GLOBALS['prompt'] = $rows <= 0 ? '修改失败！' : '更新成功';
    }

    if (empty($_GET['id'])) {  //检验是否get请求传id值  判断是编辑主线还是添加主线
      if ($_SERVER['REQUEST_METHOD']=== 'POST') {
        add_category();
      }
    }
    else{
      //连接数据库 
      xiu_mysqli();                  // 获取  点击编辑的id那条数据的 
      $current_edit_category = xiu_mysqli_fetch_one('select * from categories where id = ' . $_GET['id']);
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        edit_category();
      }
    }
    // // 如果修改操作与查询操作在一起，一定是先做修改，再查询

    // 查询全部的分类数据
  xiu_mysqli();
  $categories = xiu_mysqli_fetch('select * from categories;');
  xiu_close_mysqli();
 ?>
 
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Categories &laquo; Admin</title>
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
        <h1>分类目录</h1>
      </div>
      <!-- 提示信息时展示 -->
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
        <?php if (isset($current_edit_category)): ?>
          
        <div class="col-md-4">
          <form action="<?php echo $_SERVER['PHP_SELF'] ;?>?id=<?php echo $current_edit_category['id']; ?>" method="post" >
            <h2>编辑《<?php echo $current_edit_category['name']; ?>》</h2>
            <div class="form-group">
              <label for="name">名称</label>
              <input id="name" class="form-control" name="name" type="text" placeholder="分类名称" value="<?php echo $current_edit_category['name']; ?>">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo $current_edit_category['slug']; ?>" >
              <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">保存</button>
              <a href="/admin/categories.php" class="btn btn-primary" >取消修改</a>
            </div>
          </form>
        </div>
        <?php else: ?>
        <div class="col-md-4">
          <form action="<?php echo $_SERVER['PHP_SELF'] ;?>" method="post" >
            <h2>添加新分类目录</h2>
            <div class="form-group">
              <label for="name">名称</label>
              <input id="name" class="form-control" name="name" type="text" placeholder="分类名称" value="<?php  echo isset($name) ? $name : '' ; ?>">
            </div>
            <div class="form-group">
              <label for="slug">别名</label>
              <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php   echo isset($slug) ? $slug : ''  ; ?>">
              <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">添加</button>
            </div>
          </form>
        </div>
        <?php endif ?>

        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a id="btn_delete" class="btn btn-danger btn-sm" href="/admin/delete-data.php" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center" width="80"><input id="all_checkbox"  type="checkbox">全选</th>
                <th>名称</th>
                <th>Slug</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php if (isset($categories)): ?>
              <?php foreach ($categories as  $value): ?>
                <tr>
                  <td class="text-center"><input type="checkbox"  data-id='<?php echo $value['id'] ;?>'></td>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['slug']; ?></td>
                  <td class="text-center">
                    <a href="/admin/categories.php?id=<?php echo $value['id']; ?>" class="btn btn-info btn-xs">编辑</a>
                    <a href="/admin/delete-data.php?id=<?php echo $value['id']; ?>&from=<?php echo 'categories' ?>" class="btn btn-danger btn-xs">删除</a>
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

  <?php $current_page='categories' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>

  <script>
    $(function($){
      /*var aBtn=$(".btn.btn-danger.btn-xs");
      for(var i=0; i<aBtn.length; i++){

        $(aBtn[i]).click(function (){
          
          $.ajax({
            url: $(this).attr('href'),
            //dataType: 'jsonp',
            success:function(data){
              console.log(data);
            }
          });
          return false;
        });
      }*/

      // 1. 不要重复使用无意义的选择操作，应该采用变量去本地化
      //     // - attr 访问的是 元素属性
      //     // - prop 访问的是 元素对应的DOM对象的属性

      var aCheckbox=$('tbody input');

      var oBtnDelete=$('#btn_delete');
      var oAllBox=$('#all_checkbox');
      // 定义一个数组记录被选中的
      var aCheckboxId=[];

      oAllBox.on('change', function(){
          if ($(this).prop('checked')) {
            aCheckbox.prop( 'checked' ,true );
            aCheckboxId.length=0;
            aCheckbox.each(function(index, el) {

              aCheckboxId.push($(el).data('id'));
            });
            console.log(aCheckboxId);
            oBtnDelete.fadeIn();
          }
          else{
            aCheckbox.prop( 'checked',false );

            aCheckbox.each(function(index, el) {
              aCheckboxId.length=0;
            });
            console.log(aCheckboxId);
            oBtnDelete.fadeOut();
          }
          oBtnDelete.prop('search', '?id=' + aCheckboxId +'&from=categories' );
      });
      
      
      aCheckbox.on('change',function() {
        
        var id=$(this).data('id');  //data 操作由data-开头自定义的属性

        if ($(this).prop("checked")) {

          aCheckboxId.push(id);    //把勾选中的对应的数据的id值添加进这个数
        }
        else{
          aCheckboxId.splice(aCheckboxId.indexOf(id),1);  //删除取消掉勾选存进这个数组里的这条数据的id值
        }

        if (aCheckboxId.length == aCheckbox.length) {
          console.log("a");
          oAllBox.prop('checked',true);
        }
        else{
          oAllBox.prop('checked',false);
        }
        // 有任意一个 checkbox 选中就显示批量删除，反之隐藏
        aCheckboxId.length>0 ? oBtnDelete.fadeIn() : oBtnDelete.fadeOut();
        oBtnDelete.prop('search', '?id=' + aCheckboxId +'&from=categories' );

      });


    });

  </script>


  <script>NProgress.done()</script>
</body>
</html>
