<?php
include "../include/db.php";
// get task
if(isset($_GET['task'])) { $task = $_GET['task']; } 
else if(isset($_POST['task'])) { $task = $_POST['task']; }

// get view
if(isset($_GET['view'])) { $view = $_GET['view']; } 
else if(isset($_POST['view'])) { $view = $_POST['view']; }
else { $view = ""; }

// get page
if(isset($_GET['p'])) { $p = $_GET['p']; } 
else if(isset($_POST['p'])) { $p = $_POST['p']; }
else { $p = 1; }

// get search
if(isset($_GET['search'])) { $search = $_GET['search']; } 
else if(isset($_POST['search'])) { $search = $_POST['search']; }
else { $search = ""; }

// make sure admin is logged in
if($page != "login") {
  if($_COOKIE["representmap_user"] != crypt($admin_user, $admin_user) OR $_COOKIE["representmap_pass"] != crypt($admin_pass, $admin_pass)) {
    header("Location: login.php");
    exit;
  }
}

// connect to db
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// get marker totals
$total_approved = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='1'"));
$total_rejected = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='0'"));
$total_pending = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved IS null"));
$total_all = mysql_num_rows(mysql_query("SELECT id FROM places"));

// admin header
$admin_head = "
  <html>
  <head>
  	<meta charset='utf-8'>
  	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>管理面板——中国海洋大学校友地图</title>
    <link href='../bootstrap/css/bootstrap.min.css' rel='stylesheet' type='text/css'/>
    <link rel='stylesheet' href='flat-ui.css' type='text/css'/>
    <link rel='SHORTCUT ICON' href='../images/icons/favicon.ico'/>
    <script src='../bootstrap/js/bootstrap.min.js' type='text/javascript' charset='utf-8'></script>
    <script src='../scripts/jquery-1.7.1.js' type='text/javascript' charset='utf-8'></script>
    <script src='http://ditu.google.cn/maps/api/js?key=AIzaSyC7a2MMoG2nkxwY6bmxjoULoiz2YTp43iI&sensor=false&language=cn' type='text/javascript' charset='utf-8'></script>
  </head>
  <body>
";
if($page != "login") {
  $admin_head .= "
    <nav class='navbar navbar-default navbar-fixed-top' role='navigation'>
    <div class='navbar-header'>
      <a class='navbar-brand' href='index.php'>
            校友地图
      </a>
    </div>
    <div class='collapse navbar-collapse navbar-ex1-collapse'>
      <ul class='nav navbar-nav'>
        <li"; if($view == "") { $admin_head .= " class='active'"; } $admin_head .= ">
          <a href='index.php'>全部</a>
        </li>
        <li"; if($view == "approved") { $admin_head .= " class='active'"; } $admin_head .= ">
          <a href='index.php?view=approved'>
           已通过
          <span class='badge badge-info'>$total_approved</span>
          </a>
        </li>
        <li"; if($view == "pending") { $admin_head .= " class='active'"; } $admin_head .= ">
          <a href='index.php?view=pending'>
                待定
          <span class='badge badge-info'>$total_pending</span>
          </a>
        </li>
        <li"; if($view == "rejected") { $admin_head .= " class='active'"; } $admin_head .= ">
          <a href='index.php?view=rejected'>
                已拒绝
          <span class='badge badge-info'>$total_rejected</span>
          </a>
        </li>
      </ul>
      <form class='navbar-form navbar-left' action='index.php' method='get' role='search'>
        <input type='text' name='search' class='search-query' placeholder='搜索' autocomplete='off' value='$search'>
      </form>
      <ul class='nav pull-right'>
        <li><a href='login.php?task=logout'>登 出</a></li>
      </ul>
    </div>
</nav>
  ";
}
$admin_head .= "
  <div class='container'>
";
// admin footer 
$admin_foot = "
    </div>
  </body>
</html>
";
?>
