<?
$page = "login";
include "header.php";

$is_loggedin = false;
$alert = "";

// logout
if($task == "logout") {
  setcookie("representmap_user", "", time()+3600000);
  setcookie("representmap_pass", "", time()+3600000);
  header("Location: login.php");
  exit;
}
// attempt login
if($task == "dologin") {
  $input_user = htmlspecialchars($_POST['user']);
  $input_pass = htmlspecialchars($_POST['pass']);
  if(trim($input_user) == "" || trim($input_pass) == "") {
    $alert = "未输入用户名与密码，要不再试试？";
  } else {
    if(crypt($input_user, $admin_user) == crypt($admin_user, $admin_user) && crypt($input_pass, $admin_pass) == crypt($admin_pass,$admin_pass)) {
      setcookie("representmap_user", crypt($input_user, $admin_user), time()+3600000);
      setcookie("representmap_pass", crypt($input_pass, $admin_pass), time()+3600000);
      header("Location: index.php");
      exit;
    } else {
      $alert = "验证失败，用户名或密码错误.:(";
    }
  }
}

?>
<? echo $admin_head; ?>
<div class="login_icon col-xs-6 col-sm-6 col-md-4">
  <img src="../images/icons/login-map.png" class="img-responsive" alt="校友地图，欢迎.">
  <h4><b>校友地图管理面板</b> 欢迎.</h4>
</div>
<form class="login-form col-xs-12 col-sm-6 col-md-8" action="login.php" id="login" method="post" role="form">
  <div class="form-group">
  <input type="text" name="user" class="form-control" placeholder="用户名">
  </div>
  <div class="form-group">
  <input type="password" name="pass" class="form-control" placeholder="密码">
  </div>
  <button type="submit" class="btn btn-primary btn-large btn-block">登 录</button>
  <input type="hidden" name="task" value="dologin"/>
   <?
    if($alert != "") {
      echo "
        <div class='alert alert-danger'>
          $alert
        </div>
      ";
    }
  ?>
  <a class="login-link" href="/">返回首页.</a>
</form>
<? echo $admin_foot; ?>