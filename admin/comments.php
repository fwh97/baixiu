<?php 
    require_once '../functions.php';

    xiu_get_current_user();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
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
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul class="pagination pagination-sm pull-right">
            <!-- 分页插件渲染 -->
        </ul>
        
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input id="all_checkbox" type="checkbox"></th>
            <th>作者</th>
            <th>评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="140">操作</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>

  <?php $current_page='comments' ?>
  <?php include_once 'inc/sidebar.php' ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>

  <script id="comments_tmpl" type="text/html">

    {{for data}}
      <tr class="{{:status == 'held' ? 'warning' : status === 'rejected' ? 'danger' : 'success' }} " data-id="{{:id}}">
        <td class="text-center"><input type="checkbox"></td>
        <td width="80">{{:author}}</td>
        <td width="500">{{:content}}</td>
        <td width="100">《{{:post_title}}》</td>
        <td width="150">{{:created}}</td>
        <td width="50">{{: status === 'held' ? '待审' : status === 'rejected' ? '拒绝' : '准许' }}</td>
        <td class="text-center">
          {{if status === 'held'}}
            <a class="btn btn-info btn-xs btn-edit" href="javascript:;" data-status="approved">批准</a>
            <a class="btn btn-warning btn-xs btn-edit" href="javascript:;" data-status="rejected">拒绝</a>
          {{/if}}
          {{if status === 'rejected'}}
            <a class="btn btn-info btn-xs btn-edit" href="javascript:;" data-status="approved">批准</a>
          {{/if}}
            <a class="btn btn-danger btn-xs btn-delete" href="javascript:;">删除</a>
        </td>
      </tr>
    {{/for}}
  </script>
  <script>

    $(function ($) {

      $(document)
      .ajaxStart(function () {  //ajax请求开始
        NProgress.start()       //第三方加载进度条插件
      })
      .ajaxStop(function () {   //ajax响应结束
        NProgress.done()
      })

      $tmpl=$('#comments_tmpl');
      var $pagination = $('.pagination');  //分页对象
      var $tbody=$('tbody');
      var size=10;         //一页显示十条数据
      var currentPage=1;  //分页默认显示第一页
      //var currentPage = parseInt(window.localStorage.getItem('last_comments_page')) || 1;
      var oBtnBatch=$('.btn-batch');    //批量处理按钮
      var oAllBox=$('#all_checkbox');  //全选框
      var aCheckboxId=[];// 定义一个数组记录被选中的

      function loadData () {
        $.get('/admin/comments-list.php', { page: currentPage, size: size }, function (data) {
          // 通过模板引擎渲染数据
          $tbody.fadeOut();  
          var html = $tmpl.render(data)  //第三方库：jsrender 模板引擎渲染数据
          
          $tbody.html(html); // 添加到页面中
          $tbody.fadeIn();
          oAllBox.prop('checked',false);
          oBtnBatch.fadeOut();

          if (currentPage > Math.ceil(data.total_count / size)) {
            currentPage=Math.ceil(data.total_count / size);   //避免显示分页数大于总页数
          }
          $pagination.twbsPagination('destroy');  //允许刷新或重绘
          $pagination.twbsPagination({   //jq-分页ui插件：twbsPagination
            first:'第一页',
            last: '最后一页',
            prev: '&lt;',
            next: '&gt;',
            startPage: currentPage,      //显示的那一页
            totalPages: Math.ceil(data.total_count / size),   //总页数（最大页数）
            visiblePages:5,    //展示可见页数
            initiateStartPageClick: false, // 否则 onPageClick 第一次就会触发
            onPageClick: function (event, page) {
              // page 点击的那一页的页数
              currentPage = page;
              //window.localStorage.setItem('last_comments_page', currentPage); //
              loadData();
              oBtnBatch.fadeOut();
            }
          })//end pagination
        })
      } //loadData end

      loadData();

      $tbody.on('click','.btn-delete', function(){         //删除 
        var id=parseInt($(this).parent().parent().data('id'));
        //var $tr=$(this).parent().parent();
        $.get('/admin/delete-data.php',{id: id,from: 'comments'}, function(data){
          data && loadData();         //删除成功后重新渲染页面
          //data && $tr.remove();
        });
      });

      $tbody.on('click','.btn-edit', function(){       //状态改变（批准&拒绝）
        var id=parseInt($(this).parent().parent().data('id')); 
        var status=$(this).data('status');
        $.post('/admin/comments-status.php',{id :id ,status: status}, function(data){
          data.success && loadData();   
        });
      });

      oAllBox.on('change', function(){            //全选
          var checked=$(this).prop('checked');
          $('td > input[type=checkbox]').prop('checked',checked).trigger('change');  //trigger 调用对象的事件方法
          if (!checked ) aCheckboxId.length=0 ; 
      });

      //ajax异步方式渲染页面 用事件源方式绑定事件  直接绑定事件会造成页面还没加载进来，绑定空的对象元素
      $tbody.on('change','td > input[type=checkbox]',function() {  

        var id=parseInt($(this).parent().parent().data('id'));  //data 操作由data-开头自定义的属性
        if ($(this).prop("checked")) {
          //aCheckboxId.indexOf(id) === -1||aCheckboxId.push(id);    //把勾选中的对应的数据的id值添加进这个数
          aCheckboxId.includes(id) || aCheckboxId.push(id)  
          // includes  判断一个数组是否包含一个指定的值，如果是返回 true，否则false
          if (aCheckboxId.length == $('tbody tr').length) {
            oAllBox.prop('checked',true);         //页面复选框全选完，全选按钮也选中
          }
        }
        else{
          aCheckboxId.splice(aCheckboxId.indexOf(id),1);  //删除取消掉勾选存进这个数组里的这条数据的id值
          oAllBox.prop('checked',false);
        }
        console.log($('td > input[type=checkbox]:checked').length)
        if ($('td > input[type=checkbox]:checked').length <=0) {
          aCheckboxId.length=0;
        }
        // 有任意一个 checkbox 选中就显示批量删除，反之隐藏
        aCheckboxId.length ? oBtnBatch.fadeIn() : oBtnBatch.fadeOut();
        //console.log(aCheckboxId)
      });
                //批量操作
      oBtnBatch
      .on('click', '.btn-info',function(){    //批量状态改变
          $.post('/admin/comments-status.php', {id:aCheckboxId.join(','), status:'approved'}, function(data) {
            data.success && loadData();
            //aCheckboxId.length=0;         //操作完清空数组里的id
          });
        })
      .on('click', '.btn-warning', function(){
          $.post('/admin/comments-status.php', {id:aCheckboxId.join(','), status:'rejected'}, function(data) {
            data.success && loadData();
            //aCheckboxId.length=0;
          });
      })
      .on('click', '.btn-danger', function(){     //批量删除
          $.get('/admin/delete-data.php', {id:aCheckboxId.join(','), from:'comments'}, function(data) {
            data && loadData();
            //aCheckboxId.length=0;
          });
      })

    });

  </script>

  <script>NProgress.done()</script>
</body>
</html>
