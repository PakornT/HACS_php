<?php
if(!isset($_SESSION)){
	session_start();
//	$_SESSION["hashedPassword"]='';
}
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
include "EnDec.php";
if(isset($_POST['job'])&&$_POST['job']=='deleteInstance'){
	$filename="remote_queue.lst";
	$file = file($filename);
	unset($file[$_POST['value']]);
	file_put_contents($filename, $file);
}elseif(isset($_POST['job'])&&$_POST['job']=='deleteTimed'){
	$filename="timed_queue.lst";
	$file = file($filename);
	for($i=$_POST['value'];$i<count($file);$i++){
		$line=explode(':',decrypt($file[$i]));
		$applianceName = $line[0];
		$commandName = $line[1];
		$execTime = $line[2];
		$timeStamp = $line[3];
		if ($commandName=="arrive") {
			$statusFileName="status.conf";
			$status = file($statusFileName);
			$status[1] = "at-home:"."0";
			file_put_contents($statusFileName, $status);
			$file[$i]=encrypt($applianceName.":depart:".$execTime.":".$timeStamp)."\n";
		}elseif ($commandName=="depart") {
			$statusFileName="status.conf";
			$status = file($statusFileName);
			$status[1] = "at-home:"."1";
			file_put_contents($statusFileName, $status);
			$file[$i]=encrypt($applianceName.":arrive:".$execTime.":".$timeStamp)."\n";
		}
	}
	unset($file[$_POST['value']]);
	file_put_contents($filename, $file);
}elseif(isset($_POST['job'])&&$_POST['job']=='clearAllQueue'){
	$filename="timed_queue.lst";
	file_put_contents($filename, '');
}elseif($_SESSION["hashedPassword"]==$sha1_password){
	if(isset($_GET['timed'])){
		$recordFileName="timed_queue.lst";
	}else{
		$recordFileName="remote_queue.lst";
	}
//		$recordFile = fopen($recordFileName, "r") or die("Unable to open configuration file!");
//		fwrite($recordFile, $_POST['value']);


?>
<link rel="stylesheet" type="text/css" href="css/table.css" />
<link rel="stylesheet" type="text/css" href="css/styleAppliance.css" />
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />
<script>
	$(document).ready(function(){
		$("#btnHome").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF']; ?>';
		});
		$("#btnInstance").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?queue'; ?>';
		});
		$("#btnTimed").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?timed&queue'; ?>';
		});
		$("#btnClearAllQueue").click(function(){
			if (confirm('Are you sure you want to delete all queue?')) {
				$.post('queue.php', { job:"clearAllQueue" });
				window.setTimeout(function () {
					location.reload();
				}, 1500)
				}else{
					$('#status').fadeTo('fast',1);
					$('#status span').text('Command cancelled.');
					setTimeout(function() {
						$('#status').fadeTo('fast',0);
					}, 1000);				
				}});
<?php
	$file = file($recordFileName);

	for($i=0;$i<count($file);$i++){
?>
		$("#btnCross<?php echo $i ?>").click(function(){
		if (confirm('Are you sure you want to delete queue <?php echo $i+1 ?>?')) {
			$.post('queue.php', { job:"delete<?php if(isset($_GET['timed'])){echo "Timed";}else{echo "Instance";}?>", value:"<?php echo $i ?>"});
			window.setTimeout(function () {
				location.reload();
			}, 1500)
			}else{
				$('#status').fadeTo('fast',1);
				$('#status span').text('Command cancelled.');
				setTimeout(function() {
					$('#status').fadeTo('fast',0);
				}, 1000);				
			}});
<?php
	}
?>
	});
</script>
<div class="title">
	<div class="left">
		<div class="title title--left" id="btn<?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?>"><i class="button__icon icon icon-<?php if(isset($_GET['timed'])){echo 'cancel';}else{echo 'clock';}?>"></i><br><span><?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?></span></div>
	</div>
	<div class="main">
		<div class="title title--main"><i class="button__icon icon icon-chart"></i><span>QUEUE</span></div>
	</div>
	<div class="right">
	<div class="title title--right" id="btnHome"><i class="button__icon icon icon-home"></i><br><span>Home</span></div>
</div>
<div id='status'>
<center>
<span>
<br>
</span>
</center>
</div>
<div class="box bg-1">
<table class="table table-striped">
	<thead>
		<tr>
			<th>Appliance</th>
			<th>Command</th>
			<th><?php if(isset($_GET['timed'])){echo 'Execute Time</th><th>Order Time</th>';}else{echo 'Times';} ?></th><th><b class="SteedButton" id="btnClearAllQueue">,</b>
		</tr>
	</thead>
	<tbody>
<?php
for($i=0;$i<count($file);$i++){
	$line=decrypt($file[$i]);
	$appliance = explode(':', $line);
	$command = $appliance[1];
	$times = $appliance[2];
	$appliance = $appliance[0];
	if(!empty($appliance)){
?>
		<tr>
			<td><?php echo $appliance?></td>
			<td><?php echo $command?></td>
			<td><?php if(isset($_GET['timed'])){echo date('H:i',$times);}else{echo $times;} ?></td>
<?php
	if(isset($_GET['timed'])){echo '<td>'.date('H:i',explode(':', $line)[3]).'</td>';}
?>
<?php 
//	if(isset($_GET['timed'])){echo '<td><i class="button__icon icon" id="btnCross'.$i.'"><b class="SteedButtonSmall">X</b></i></td>';}
	echo '<td><i class="button__icon icon" id="btnCross'.$i.'"><b class="SteedButtonSmall">X</b></i></td>';
?>
		</tr>
<?php
	}
}
?>
	</tbody>
</table>

</div>
<?php
//	fclose($recordFile);
}else{
	echo "Hello World!";	
	}
?>