AlumniMap
=========

represent all your alumni on a map !

based on [represent-map](https://github.com/abenzer/represent-map)

DEMO: [http:map.copypeng.com](http://map.copypeng.com)

AlumniMap是我构建的校友地图开源项目，旨在帮助高等学校等教育机构快速建立自己的校友地图website.
用户提交自己的地址，经纬度或者是邮政编码，都能自动在世界地图上生成位置marker，方便学校联系校友，也方便校友相互联系，找到彼此。

![github](https://raw.github.com/Copypeng/AlumniMap/master/demoImage/2013-11-05%2010:25:33%E7%9A%84%E5%B1%8F%E5%B9%95%E6%88%AA%E5%9B%BE.png "github")

###感谢以下项目

* Bootstrap http://getbootstrap.com/

* represent-map https://github.com/abenzer/represent-map

* jQuery http://jquery.com/

* Pace https://github.com/HubSpot/pace

* 以及 google map API V3

等等

###要求

* PHP 5+

* MySQL

###安装
* 创建一个MySQL数据库

* 导入db文件夹下的places.sql

* 修改db.php文件，填入你的数据库名，用户名，密码，设定后台管理密码

* 上传所有文件至服务器，至此你应该完成了安装

* 记得常登陆后台管理申请，如果用户向你提交了一个申请，默认需要你approve才能在地图上生成marker.

###bug与新功能

强烈期待大家直接Pull requests

###License
MIT License https://raw.github.com/Copypeng/AlumniMap/master/LICENSE

我鼓励大家想办法卖给自己学校然后赚点生活费。
如果你成功使用了本软件，欢迎来wiki提交网址。
