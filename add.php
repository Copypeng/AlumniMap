<?php
include_once "header.php";
// This is used to submit new markers for review.
// Markers won't appear on the map until they are approved.
$owner_name = mysql_real_escape_string(parseInput($_POST['owner_name']));
$owner_email = mysql_real_escape_string(parseInput($_POST['owner_email']));
$title = mysql_real_escape_string(parseInput($_POST['title']));
$type = mysql_real_escape_string(parseInput($_POST['type']));
$address = mysql_real_escape_string(parseInput($_POST['address']));
$uri = mysql_real_escape_string(parseInput($_POST['uri']));
$description = mysql_real_escape_string(parseInput($_POST['description']));

// validate fields
$exist = mysql_query("SELECT * FROM places WHERE title = '$title' LIMIT 1");
if(mysql_num_rows($exist) == 1) { 
  $existing = mysql_fetch_assoc($exist);
  if ($existing[id] == 0){
    echo "您的信息已经提交，正在审核中，请耐心等待.";
  }
  else{
    echo "您的信息已经已经存在了，您可以通过右侧面板搜索快速找到.";
  }
  
  exit;
}

else if (empty($title) || empty($type) || empty($address) || empty($uri) || empty($description) || empty($owner_name) || empty($owner_email)) {
  echo "填入的信息不完整，请核对后重新提交."; 
  exit;
  
} else {

  //separate logic for editing startup information:


  // insert into db, wait for approval
  $insert = mysql_query("INSERT INTO places (approved, title, type, address, uri, description, owner_name, owner_email) VALUES (null, '$title', '$type', '$address', '$uri', '$description', '$owner_name', '$owner_email')") or die(mysql_error());

  // geocode new submission
  $hide_geocode_output = true;
  include "geocode.php";
  
  // if we got here, let the user know everything's OK
  echo "success";
  exit;
  
}
?>