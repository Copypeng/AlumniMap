<?php
include_once "header.php";
mysql_query("SET NAMES 'utf8'");

$employer_name = mysql_real_escape_string(parseInput($_POST['employer_name']));
$position = mysql_real_escape_string(parseInput($_POST['position']));
$student_work = mysql_real_escape_string(parseInput($_POST['student_work']));
$email = mysql_real_escape_string(parseInput($_POST['email']));
$phone_number = mysql_real_escape_string(parseInput($_POST['phone_number']));
$name = mysql_real_escape_string(parseInput($_POST['name']));
$type = mysql_real_escape_string(parseInput($_POST['type']));
$address = mysql_real_escape_string(parseInput($_POST['address']));
$uri = mysql_real_escape_string(parseInput($_POST['uri']));
$description = mysql_real_escape_string(parseInput($_POST['description']));

// validate fields
$exist = mysql_query("SELECT * FROM places WHERE name = '$name' LIMIT 1");
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

else if (empty($name) || empty($type) || empty($address) || empty($uri) || empty($description) || empty($employer_name) || empty($student_work) || empty($email)) {
  echo "填入的信息不完整，请核对后重新提交."; 
  exit;
  
} else {
  // insert into db, wait for approval
  $insert = mysql_query("INSERT INTO places (approved, name, type, address, uri, description, employer_name, student_work, phone_number, position, email) VALUES (null, '$name', '$type', '$address', '$uri', '$description', '$employer_name', '$student_work', '$phone_number', '$position', '$email')") or die(mysql_error());

  // geocode new submission
  $hide_geocode_output = true;
  include "geocode.php";
  
  // if we got here, let the user know everything's OK
  echo "success";
  exit;
  
}
?>