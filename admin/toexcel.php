<?php

$mysqli = new mysqli("localhost","username","password","dbname");
// Get data from the database.
$sql = "SELECT * FROM places";
// Grab the records
$res = $mysqli->query($sql);
//  Default the type to HTML
$type = 'html';
if (isset($_GET['type']) && $_GET['type'] == 'csv') {
	// If the type is set to csv, then override to csv.
	$type = 'csv';
}


// Determine if the file should be shown as a table, or saved to excel.
if (isset($_GET['download']) && $type == 'csv') {
	$filename = "file_" . date("Y-m-d") . ".csv";

	// Send HTTP headers to tell the browser what type of file this is for download.
	header("Content-type: text/csv;charset=utf-8");
	header("Content-disposition: attachment; filename=$filename");

} elseif (isset($_GET['download']) && $type == 'html') {
	// Fake an Excel file by sending HTML data to Excel.
	// Excel will import it after a warning.
	$filename = "file_" . date("Y-m-d") . ".xls";

	// Send HTTP headers to tell the browser what type of file this is for download.
	header("Content-type: application/vnd.excel");
	header("Content-disposition: attachment; filename=$filename");

} else {

 // if Download is not set, display an HTML form to help demo this.
 ?>
	<form action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
		是否生成文件下载? <input type="checkbox" name="download" value="yes">是
		<input type="submit" value="下载">
	</form>
	预览
	<hr>
	<!-- Output of actual file starts here. Delete everything above including this line if you want to save the data. -->
 <?php
 }


if ($type == 'csv') {
	echo "姓名,入学年份,地址,工作单位,职务,电话,电子邮件\n";
	
	// Then loop over the results, keeping commas in between the records.
	// Something to consider: 
	// 		What do you do when the data for the synopsis has a comma in it??
	while ($row = $res->fetch_assoc()) {
		echo $row['name'] . ",";
		echo $row['type'] . ",";
		echo $row['address'] . ",";
		echo $row['employer_name'] . ",";
		echo $row['position'] . ",";
		echo $row['phone_number'] . ",";
		echo $row['email'];
		echo "\n";
	}

} else {
	// Otherwise, print out some HTML tables for the content.
	// Excel will read this table and auto-import it into rows and columns.
	?>
		<table>
		<tr>
			<th>姓名</th>
			<th>入学年份</th>
			<th>地址</th>
			<th>工作单位</th>
			<th>职务</th>
			<th>电话</th>
			<th>电子邮件</th>
		</tr>
		<?php 
			while ($row = $res->fetch_assoc()) {
			?>
				<tr>
					<td><?php echo $row['name'];?></td>
					<td><?php echo $row['type'];?></td>
					<td><?php echo $row['address'];?></td>
					<td><?php echo $row['employer_name'];?></td>
					<td><?php echo $row['position'];?></td>
					<td><?php echo $row['phone_number'];?></td>
					<td><?php echo $row['email'];?></td>
				</tr>	
			<?php
			}
		?>
		</table>
	<?php
}