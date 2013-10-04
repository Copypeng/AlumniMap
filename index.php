<?php
include_once "header.php";

mysql_query("SET NAMES 'utf8'");

?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>校友地图——中国海洋大学地图校友录</title>
    <link rel="SHORTCUT ICON" href="./images/icons/favicon.ico"/>
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen"/>
    <link rel="stylesheet" href="map.css?nocache=289671982568" type="text/css" />
    <link rel="stylesheet" media="only screen and (max-device-width: 480px)" href="mobile.css" type="text/css" />
    <script type="text/javascript" src="./scripts/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="./bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./bootstrap/js/bootstrap-typeahead.js"></script>
    <script type="text/javascript" src="http://ditu.google.cn/maps/api/js?key=AIzaSyC7a2MMoG2nkxwY6bmxjoULoiz2YTp43iI&sensor=false&language=cn"></script>
    <script type="text/javascript" src="./scripts/label.js"></script>
    <script type="text/javascript" src="./scripts/markerclusterer.js"></script>
    <script type="text/javascript">
      var map;
      var infowindow = null;
      var gmarkers = [];
      var markerTitles =[];
      var highestZIndex = 0;  
      var agent = "default";
      var zoomControl = true;


      // detect browser agent
      $(document).ready(function(){
        if(navigator.userAgent.toLowerCase().indexOf("iphone") > -1 || navigator.userAgent.toLowerCase().indexOf("ipod") > -1) {
          agent = "iphone";
          zoomControl = false;
        }
        if(navigator.userAgent.toLowerCase().indexOf("ipad") > -1) {
          agent = "ipad";
          zoomControl = false;
        }
      }); 
      

      // resize marker list onload/resize
      $(document).ready(function(){
        resizeList() 
      });
      $(window).resize(function() {
        resizeList();
      });
      
      // resize marker list to fit window
      function resizeList() {
        newHeight = $('html').height() - $('#footerbar').height();
        $('#list').css('height', newHeight + "px"); 
        $('#menu').css('margin-top', $('#footerbar').height()); 
      }


      // initialize map
      function initialize() {
        // set map styles
        var mapStyles = [
        {
            featureType: "administrative.locality",
            stylers: [
              { visibility: "on" }
            ]
          },{
            featureType: "administrative.neighborhood",
            elementType: "geometry",
            stylers: [
              { visibility: "simplified" }
            ]
          },{
            featureType: "administrative.neighborhood",
            elementType: "labels.text",
            stylers: [
              { visibility: "on" },
              { saturation: 33 },
              { lightness: 20 }
            ]
          },{
            featureType: "road.highway",
            stylers: [
              { visibility: "simplified" },
              { lightness: 18 }
            ]
          },{
			featureType: "poi",
			elementType: "labels",
			stylers: [
				{visibility: "off"}
			]
		  }
        ];
        // set map options
        var myOptions = {
          zoom: 2,
          maxZoom: 19,
          minZoom: 2,
          center: new google.maps.LatLng(25.600094,-148.902344),
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          streetViewControl: true,
          mapTypeControl: true,
          panControl: false,
          featureType: "poi.school",
		      elementType: "labels",
          zoomControl: zoomControl,
          styles: mapStyles,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle.DEFAULT,
            position: google.maps.ControlPosition.LEFT_CENTER
          }
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
        zoomLevel = map.getZoom();

        // prepare infowindow
        infowindow = new google.maps.InfoWindow({
          content: "一会就好……"
        });

        // only show marker labels if zoomed in
        google.maps.event.addListener(map, 'zoom_changed', function() {
          zoomLevel = map.getZoom();
          if(zoomLevel <= 15) {
            $(".marker_label").css("display", "none");
          } else {
            $(".marker_label").css("display", "inline");
          }
        });

        // markers array: name, type (icon), lat, long, description, uri, address
        markers = new Array();
        <?php
          $types = Array(
              Array('2009', '2009级'),
              Array('2008','2008级'),
              Array('2007', '2007级'), 
              Array('2006', '2006级'), 
              Array('other', '更早...'),
              );
          $marker_id = 0;
          foreach($types as $type) {
            $places = mysql_query("SELECT * FROM places WHERE approved='1' AND type='$type[0]' ORDER BY title");
            $places_total = mysql_num_rows($places);
            while($place = mysql_fetch_assoc($places)) {
              $place[title] = htmlspecialchars_decode(addslashes(htmlspecialchars($place[title])));
              $place[description] = str_replace(array("\n", "\t", "\r"), "", htmlspecialchars_decode(addslashes(htmlspecialchars($place[description]))));
              $place[uri] = addslashes(htmlspecialchars($place[uri]));
              $place[owner_email] = addslashes(htmlspecialchars($place[owner_email]));
              $place[address] = htmlspecialchars_decode(addslashes(htmlspecialchars($place[address])));
              echo "
                markers.push(['".$place[title]."', '".$place[type]."', '".$place[lat]."', '".$place[lng]."', '".$place[description]."', '".$place[uri]."', '".$place[address]."', '".$place[owner_email]."']); 
                markerTitles[".$marker_id."] = '".$place[title]."';
              "; 
              $count[$place[type]]++;
              $marker_id++;
            }
          } 
        ?>

        // add markers
        jQuery.each(markers, function(i, val) {
          infowindow = new google.maps.InfoWindow({
            content: ""
          });

          // offset latlong ever so slightly to prevent marker overlap
          rand_x = Math.random();
          rand_y = Math.random();
          val[2] = parseFloat(val[2]) + parseFloat(parseFloat(rand_x) / 6000);
          val[3] = parseFloat(val[3]) + parseFloat(parseFloat(rand_y) / 6000);

          // show smaller marker icons on mobile
          if(agent == "iphone") {
            var iconSize = new google.maps.Size(16,19);
          } else {
            iconSize = null;
          }
          // build this marker
          var markerImage = new google.maps.MarkerImage("./images/icons/"+val[1]+".png", null, null, null, iconSize);
          var marker = new google.maps.Marker({
            position: new google.maps.LatLng(val[2],val[3]),
            map: map,
            title: '',
            clickable: true,
            infoWindowHtml: '',
            zIndex: 10 + i,
            icon: markerImage
          });
          marker.type = val[1];
          gmarkers.push(marker);


          // format marker URI for display and linking
          var markerURI = val[5];
          if(markerURI.substr(0,7) != "http://") {
            markerURI = "http://" + markerURI; 
          }
          var markerURI_short = markerURI.replace("http://", "");
          var markerURI_short = markerURI_short.replace("www.", "");

          // add marker click effects (open infowindow)
          google.maps.event.addListener(marker, 'click', function () {
            infowindow.setContent(
              "<div class='marker_title' style='font-weight:bold;'>"+val[0]+"</div>"
              + "<div class='marker_uri'><a target='_blank' href='"+markerURI+"'>"+markerURI_short+"</a></div>"
              + "<div class='marker_desc'>"+val[4]+"</div>"
              + "<div class='marker_address'>"+val[6]+"</div>"
              + "<div class='marker_email'><a target='_blank' href='mailto:"+val[7]+"'>"+val[7]+"</a></div>"
            );
            infowindow.open(map, this);
          });

          // add marker label
          var latLng = new google.maps.LatLng(val[2], val[3]);
          var label = new Label({
            map: map,
            id: i
          });
          label.bindTo('position', marker);
          label.set("text", val[0]);
          label.bindTo('visible', marker);
          label.bindTo('clickable', marker);
          label.bindTo('zIndex', marker);
        });
          var mcOptions = {gridSize: 10, maxZoom: 19};
          var markerCluster = new MarkerClusterer(map, gmarkers, mcOptions);
          var infowindowlist = new google.maps.InfoWindow({
          content: ""
        });
     google.maps.event.addListener(markerCluster, "clusterclick", function(c) {
      var currentZoom = map.getZoom();
      infowindowlist.close();
      if(currentZoom >= 17) {
        var myLatlng = new google.maps.LatLng(c.getCenter().lat(), c.getCenter().lng());
        var n = "";
        var markerId = new Array();
        
        infowindowlist.close();
        
        n += '<div class="clusterList"><ul>';

        for (var i = 0; i < c.markerClusterer_.clusters_[0].markers_.length; i++) {
          markerId = (c.markerClusterer_.clusters_[0].markers_[i].list);
          n += '<li class="clusterListItem"><a href="#" onclick="goToMarker(\'' + markerId + '\')">' + markerTitle[markerId] +'</a></li>'
        }
        n += '</ul></div>';
        infowindowlist.setContent(n);
        infowindowlist.setPosition(myLatlng);
        infowindowlist.open(map);
      }
    });
    MarkerClusterer.prototype.onClick = function(){
      return true; 
    };

        // zoom to marker if selected in search typeahead list
        $('#search').typeahead({
          source: markerTitles, 
          onselect: function(obj) {
            marker_id = jQuery.inArray(obj, markerTitles);
            if(marker_id > -1) {
              map.panTo(gmarkers[marker_id].getPosition());
              map.setZoom(15);
              google.maps.event.trigger(gmarkers[marker_id], 'click');
            }
            $("#search").val("");
          }
        });
      } 


      // zoom to specific marker
      function goToMarker(marker_id) {
        if(marker_id) {
          map.panTo(gmarkers[marker_id].getPosition());
          map.setZoom(15);
          google.maps.event.trigger(gmarkers[marker_id], 'click');
        }
      }

      // toggle (hide/show) markers of a given type (on the map)
      function toggle(type) {
        if($('#filter_'+type).is('.inactive')) {
          show(type); 
        } else {
          hide(type); 
        }
      }

      // hide all markers of a given type
      function hide(type) {
        for (var i=0; i<gmarkers.length; i++) {
          if (gmarkers[i].type == type) {
            gmarkers[i].setVisible(false);
          }
        }
        $("#filter_"+type).addClass("inactive");
      }

      // show all markers of a given type
      function show(type) {
        for (var i=0; i<gmarkers.length; i++) {
          if (gmarkers[i].type == type) {
            gmarkers[i].setVisible(true);
          }
        }
        $("#filter_"+type).removeClass("inactive");
      }
      
      // toggle (hide/show) marker list of a given type
      function toggleList(type) {
        $("#list .list-"+type).toggle();
      }


      // hover on list item
      function markerListMouseOver(marker_id) {
        $("#marker"+marker_id).css("display", "inline");
      }
      function markerListMouseOut(marker_id) {
        $("#marker"+marker_id).css("display", "none");
      }

      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    
    <? echo $head_html; ?>
  </head>
  <body>
    
    <!-- display error overlay if something went wrong -->
    <?php echo $error; ?>
    
    <!-- google map -->
    <div id="map_canvas"></div>
    
    <!-- right-side menu -->
    <div class="menu" id="menuRight" style="right:0;">
    	<div class="toggle" id="toggleRight" style="right:250px;">
			<img src="images/icons/right-double-arrow.png" width="25">
		</div>
		<div class="wrapper">
      <div class="logo">
        <a href="./"><img src="./images/OUC-logo.png" alt="中国海洋大学校友地图"></a>
      </div>
        	<h3>中国海洋大学校友地图</h3>
        	<p>To connect and unite the past students of OUC</p>
        	<div class="buttons">
        	<button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_add">+ 加入</button>
          &nbsp;&nbsp;
          <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_info">关 于</button>
          </div>
       <div class="input-group">
        <span class="input-group-addon">@</span>
        <input type="text" class="form-control" name="search" id="search" placeholder="输入人名搜索..." data-provide="typeahead" autocomplete="off">
      </div>
		</div>
      <ul class="list" id="list">
        <?php
          $types = Array(
              Array('2009', '2009级'),
              Array('2008','2008级'),
              Array('2007', '2007级'), 
              Array('2006', '2006级'), 
              Array('other', '更早...'),
              );
          if($show_events == true) {
            $types[] = Array('event', 'Events'); 
          }
          $marker_id = 0;
          foreach($types as $type) {
            if($type[0] != "event") {
              $markers = mysql_query("SELECT * FROM places WHERE approved='1' AND type='$type[0]' ORDER BY title");
            } else {
              $markers = mysql_query("SELECT * FROM events WHERE start_date > ".time()." AND start_date < ".(time()+4838400)." ORDER BY id DESC");
            }
            $markers_total = mysql_num_rows($markers);
            echo "
              <li class='category'>
                <div class='category_item'>
                  <div class='category_toggle' onClick=\"toggle('$type[0]')\" id='filter_$type[0]'></div>
                  <a href='#' onClick=\"toggleList('$type[0]');\" class='category_info'><img src='./images/icons/$type[0].png' alt='' />$type[1]<span class='total'> ($markers_total)</span></a>
                </div>
                <ul class='list-items list-$type[0]'>
            ";
            while($marker = mysql_fetch_assoc($markers)) {
              echo "
                  <li class='".$marker[type]."'>
                    <a href='#' onMouseOver=\"markerListMouseOver('".$marker_id."')\" onMouseOut=\"markerListMouseOut('".$marker_id."')\" onClick=\"goToMarker('".$marker_id."');\">".$marker[title]."</a>
                  </li>
              ";
              $marker_id++;
            }
            echo "
                </ul>
              </li>
            ";
          }
        ?>
      </ul>
    </div>
    
    <!-- more info modal -->
    <div class="modal fade" id="modal_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
    		<div class="modal-content">
      			<div class="modal-header">
        			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        				<h3 class="modal-title" id="modal_info">关于校友地图</h3>
      			</div>
      			<div class="modal-body">
        			<p>中国海洋大学校友地图的开发旨在方便海大毕业生的相互联系。<br>
        			<span>To connect and unite the past students of OUC.</span><hr>
        			您能在一张地图上看到分布在世界各地的海大人，并找到他们公开的联系方式。在这里，您能快速地通过搜索或者分类找到您当年的校友。为了方便其他校友也能找到您，热烈欢迎您提交自己的信息:</p>
        			<p style="text-align:center;"><button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_add" data-dismiss="modal">+ 加入</button><span> 或点击网页右侧面板相同按钮</span></p>
       				<p>如果您在使用过程中遇到问题，或者对校友地图有任何意见与建议，欢迎向我们反馈：<a href="mailto:pengyongouc@gmail.com">pengyongouc@gmail.com</a></p><hr>
       				<p>校友地图项目的诞生离不开<a href="http://www2.ouc.edu.cn/oceanyouth/" target="_blank">中国海洋大学校团委</a>与<a href="http://222.195.158.146/nc/index.html" target="_blank">中国海洋大学网络与信息中心</a>的支持.<br>离不开众多<a href="./Credits.html" target="_blank">开源工具</a>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
    </div>
    </div>

    <!-- add something modal -->
    <div class="modal fade" id="modal_add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
    		<div class="modal-content">
     			<form action="add.php" id="modal_addform" class="form-horizontal" role="form">
        			<div class="modal-header">
          				<button type="button" class="close" data-dismiss="modal">×</button>
          				<h3>立即加入!</h3>
        			</div>
       				<div class="modal-body">
          					<fieldset>
          				<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_title">您的姓名：</label>
              				<div class="col-sm-3">
                				<input type="text" class="form-control" name="title" id="add_title" maxlength="100" autocomplete="off">
                			</div>
              				<label class="col-sm-3 control-label" for="input01">入学年份：</label>
              				<div class="col-sm-3">
                				<select name="type" id="add_type" class="col-sm-3 form-control">
                  				<option value="2009">2009年</option>
                  				<option value="2008">2008年</option>
                  				<option value="2007">2007年</option>
                  				<option value="2006">2006年</option>
                  				<option value="other">更早...</option>
                				</select>
                			</div>
            			</div>
            			<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_owner_email">您的邮箱：</label>
              				<div class="col-sm-9">
               					<input type="email" class="form-control" name="owner_email" id="add_owner_email">
              				</div>
            			</div>
            			<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_owner_name">工作单位：</label>
             				<div class="col-sm-9">
                				<input type="text" class="form-control" name="owner_name" id="add_owner_name" maxlength="100">
                				<p class="help-block">如：美国麻省理工学院，华为技术有限公司，国家海洋局等</p>
             				</div>
           				</div>
            			<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_address">标记地址：</label>
              				<div class="col-sm-9">
                				<input type="text" class="form-control" name="address" id="add_address">
                				<p class="help-block">请填写您所处位置的经纬度（如:<a href="http://goo.gl/maps/Mo9h1" target="_blank">36.060561, 120.334635</a>）或者详细地址（如:<a href="http://goo.gl/maps/C9r9x" target="_blank">青岛市崂山区松岭路238号</a>），如果您的地址在<a href="http://ditu.google.cn/" target="_blank">谷歌地图</a>上可以生效，那么在这里也可以</p>
              				</div>
            			</div>
            			<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_uri">个人主页：</label>
              				<div class="col-sm-9">
                				<input type="url" class="form-control" id="add_uri" name="uri" placeholder="http://">
                				<p class="help-block">
                  					比如新浪微博个人主页<a href="http://e.weibo.com/oucnews" target="_blank">http://e.weibo.com/oucnews</a>，不要漏掉 http:// 哦
                				</p>
              				</div>
            			</div>
            			<div class="form-group">
              				<label class="col-sm-3 control-label" for="add_description">自我描述：</label>
              			<div class="col-sm-9">
                			<textarea class="form-control" rows="3" id="add_description" name="description" maxlength="150"></textarea>
                			<p class="help-block">
                  				简洁，有力。您最近从事什么工作？您的人生格言？150字以内
                			</p>
              			</div>
            			</div>
          					</fieldset>
          				<div id="result"></div>
        			</div>
        			<div class="modal-footer" style="text-align:right;">
          				<button type="submit" class="btn btn-primary"> 提 交</button>
          				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        			</div>
      			</form>
     		</div>
    	</div>
   </div>

    <script>
      // add modal form submit
      $("#modal_addform").submit(function(event) {
        event.preventDefault(); 
        // get values
        var $form = $( this ),
            owner_name = $form.find( '#add_owner_name' ).val(),
            owner_email = $form.find( '#add_owner_email' ).val(),
            title = $form.find( '#add_title' ).val(),
            type = $form.find( '#add_type' ).val(),
            address = $form.find( '#add_address' ).val(),
            uri = $form.find( '#add_uri' ).val(),
            description = $form.find( '#add_description' ).val(),
            url = $form.attr( 'action' );

        // send data and get results
        $.post( url, { owner_name: owner_name, owner_email: owner_email, title: title, type: type, address: address, uri: uri, description: description },
          function( data ) {
            var content = $( data ).find( '#content' );
            
            // if submission was successful, show info alert
            if(data == "success") {
              $("#modal_addform #result").html("我们已经收到了您提交的信息，审核之后您就能在地图上看见自己了，请耐心等待，谢谢."); 
              $("#modal_addform #result").addClass("alert alert-info");
              $("#modal_addform p").css("display", "none");
              $("#modal_addform fieldset").css("display", "none");
              $("#modal_addform .btn-primary").css("display", "none");
              
            // if submission failed, show error
            } else {
              $("#modal_addform #result").html(data); 
              $("#modal_addform #result").addClass("alert alert-danger");
            }
          }
        );
      });
    </script>
        <!-- footerbar -->
    <div class="footerbar" id="footerbar">
      <div class="container">
      <div class="row">
      	<div class="col-xs-6 col-md-4">Copyright©中国海洋大学. All Rights Reserved.</div>
      </div>
    </div>
    <script>
		$('#toggleRight').toggle(function() {
			$('#menuRight').animate({
				right: -230
			}, 'slow', function() {
			});
			$('#toggleRight').animate({
				right: 20
			}, 'slow', function() {
			});
			document.getElementById('toggleRight').innerHTML = "<img src=\"images/icons/left-double-arrow.png\" width=\"25\">";
		}, function() {
			$('#menuRight').animate({
				right: 0
			}, 'slow', function() {
			});
			$('#toggleRight').animate({
				right: 250
			}, 'slow', function() {
			});
			document.getElementById('toggleRight').innerHTML = "<img src=\"images/icons/right-double-arrow.png\" width=\"25\">";
		});
	</script>
  </body>
</html>
