<?php
include "header.php";


if(isset($_GET['place_id'])) {
  $place_id = htmlspecialchars($_GET['place_id']); 
} else if(isset($_POST['place_id'])) {
  $place_id = htmlspecialchars($_POST['place_id']);
} else {
  exit; 
}


// get place info
$place_query = mysql_query("SELECT * FROM places WHERE id='$place_id' LIMIT 1");
if(mysql_num_rows($place_query) != 1) { exit; }
$place = mysql_fetch_assoc($place_query);


// do place edit if requested
if($task == "doedit") {
  $title = str_replace( "'", "\\'", str_replace( "\\", "\\\\", $_POST['title'] ) );
  $type = $_POST['type'];
  $address = str_replace( "'", "\\'", str_replace( "\\", "\\\\", $_POST['address'] ) );
  $uri = $_POST['uri'];
  $description = str_replace( "'", "\\'", str_replace( "\\", "\\\\", $_POST['description'] ) );
  $owner_name = str_replace( "'", "\\'", str_replace( "\\", "\\\\", $_POST['owner_name'] ) );
  $owner_email = $_POST['owner_email'];
  $lat = (float) $_POST['lat'];
  $lng = (float) $_POST['lng'];
  
  mysql_query("UPDATE places SET title='$title', type='$type', address='$address', uri='$uri', lat='$lat', lng='$lng', description='$description', owner_name='$owner_name', owner_email='$owner_email' WHERE id='$place_id' LIMIT 1") or die(mysql_error());
  
  // geocode
  //$hide_geocode_output = true;
  //include "../geocode.php";
  
  header("Location: index.php?view=$view&search=$search&p=$p");
  exit;
}

?>



<? echo $admin_head; ?>

<form id="admin" class="form-horizontal" action="edit.php" method="post" role="form">
  <h1>
    编辑
  </h1>
  <fieldset>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_title">姓名</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="title" value="<?=$place[title]?>" id="add_title">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="input01">入学年份</label>
      <div class="col-sm-10">
        <select class="form-control" name="type">
          <option<? if($place[type] == "2009") {?> selected="selected"<? } ?>>2009年</option>
          <option<? if($place[type] == "2008") {?> selected="selected"<? } ?>>2008年</option>
          <option<? if($place[type] == "2007") {?> selected="selected"<? } ?>>2007年</option>
          <option<? if($place[type] == "2006") {?> selected="selected"<? } ?>>2006年</option>
          <option<? if($place[type] == "other") {?> selected="selected"<? } ?>>更早...</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2  control-label" for="add_address">地址</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="address" value="<?=$place[address]?>" id="add_address">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_uri">个人主页url</label>
      <div class="col-sm-10">
        <input type="url" class="form-control" name="uri" value="<?=$place[uri]?>" id="add_uri">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_description">个人描述</label>
      <div class="col-sm-10">
        <textarea class="form-control" name="description" id="add_description"><?=$place[description]?></textarea>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_owner_name">工作单位</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="owner_name" value="<?=$place[owner_name]?>" id="add_owner_name">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_owner_email">邮箱地址</label>
      <div class="col-sm-10">
        <input type="email" class="form-control" name="owner_email" value="<?=$place[owner_email]?>" id="add_owner_email">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label" for="add_address">标记位置</label>
      <div class="col-sm-10">
        <input type="hidden" name="lat" id="mylat" value="<?=$place[lat]?>"/>
        <input type="hidden" name="lng" id="mylng" value="<?=$place[lng]?>"/>
        <div id="map" style="width:80%;height:300px;">
        </div>
        <script type="text/javascript">
          var map = new google.maps.Map( document.getElementById('map'), {
            zoom: 17,
            center: new google.maps.LatLng( <?=$place[lat]?>, <?=$place[lng]?> ),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false
          });
          var marker = new google.maps.Marker({
            position: new google.maps.LatLng( <?=$place[lat]?>, <?=$place[lng]?> ),
            map: map,
            draggable: true
          });
          google.maps.event.addListener(marker, 'dragend', function(e){
            document.getElementById('mylat').value = e.latLng.lat().toFixed(6);
            document.getElementById('mylng').value = e.latLng.lng().toFixed(6);
          });
        </script>
      </div>
    </div>    
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">保存修改</button>
      <input type="hidden" name="task" value="doedit" />
      <input type="hidden" name="place_id" value="<?=$place[id]?>" />
      <input type="hidden" name="view" value="<?=$view?>" />
      <input type="hidden" name="search" value="<?=$search?>" />
      <input type="hidden" name="p" value="<?=$p?>" />
      <a href="index.php" class="btn" style="float: right;">取消</a>
    </div>
  </fieldset>
</form>

<? echo $admin_foot; ?>