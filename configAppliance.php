<?php
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
if($_SESSION["hashedPassword"]==$sha1_password){ 
	$explodeFactor = ':';
	$configName = explode($explodeFactor, $_GET['appliance']);
	$commonName = $configName[1];
	$configName = $configName[0];
	
	$configFileName="remote_config.conf";
	$configFile = fopen($configFileName, "r") or die("Unable to open configuration file!");
	do{
		$appliance=chop(fgets($configFile));
		$temp = str_split($appliance,strlen($commonName));
	}while($temp[0]!=$commonName);
	do{
		$noFuction=chop(fgets($configFile));
		$temp = str_split($noFuction,2);
	}while($temp[0]=="//");
	for ($i=0;$i<$noFuction;$i++) {
		do{
			$functionList[$i]=chop(fgets($configFile));
			$temp = str_split($functionList[$i],2);
		}while($temp[0]=="//");
	}
	for ($i=0;$i<$noFuction;$i++) {
		$functionList[$i]=explode(':',$functionList[$i]);
	}
	?>
<link rel="stylesheet" type="text/css" href="css/styleAppliance.css" />
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />
<script>
$(document).ready(function(){
//	$("#btnBack").click(function () {
//		window.history.back();
//	})
	$("#btnTimed").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']; ?>?timed&appliance=<?php echo $_GET['appliance'];?>';
	});
	$("#btnInstance").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']; ?>?appliance=<?php echo $_GET['appliance'];?>';
	});
	$("#btnHome").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']; ?>';
	});
	$("#btnQueue").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF'].'?queue'; ?>';
	});
	
<?php
for($i=0;$i<$noFuction;$i++){
	$paramCount = count($functionList[$i]);
//	for($j=0;$j<$paramCount;$j++){
?>
	$("#btn<?php echo $functionList[$i][0]; ?>").click(function () {
<?php
if(!isset($_GET['timed'])){

	if ($paramCount>2) {
?>
		var times = prompt("How many times you want to press this button?", "1");
//		if (times == null) {
//			times = 1;
//		}
<?php
	}else{
		echo "var times = 1;";
	}
}else{
?>
		var times = prompt("How long do you want to schedule (minutes or HH:MM format)?", "10");
//		if (times == null) {
//			times = 10;
//		}
<?php	
}
?>
		if(times!=null){
		var temp = $("#hidden<?php echo $functionList[$i][0]; ?>").val();
		$("#hidden<?php echo $functionList[$i][0]; ?>").val($("#hidden<?php echo $functionList[$i][0]; ?>").val()+':'+times+'\n');
		$.post('recordData.php', $("#form<?php echo $functionList[$i][0];?>").serialize(), function(ret){
			if(ret!=""){
//			alert(ret);
			$('#status').fadeTo('fast',1);
			$('#status span').text(ret);
			setTimeout(function() {
				$('#status').fadeTo('fast',0);
			}, 1000);
			}});
		$("#hidden<?php echo $functionList[$i][0]; ?>").val(temp)
			return false;
		}else{
			$('#status').fadeTo('fast',1);
			$('#status span').text('Command queue is not set.');
			setTimeout(function() {
				$('#status').fadeTo('fast',0);
			}, 1000);
		}
	})
<?php
//		}
//	}
}
?>
})
</script>
<div class="title">
	<div class="left">
		<div class="title title--left" id="btn<?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?>"><i class="button__icon icon icon-<?php if(isset($_GET['timed'])){echo 'cancel';}else{echo 'clock';}?>"></i><span><?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?></span></div>
	</div>
	<div class="main">
		<div class="title title--main"><i class="button__icon icon icon-<?php echo $configName;?>"></i><br><span>
		<?php if(isset($_GET['timed'])){echo 'Timed: ';}?>
		<?php
//		echo $commonName;
//		$temp=explode($explodeFactor, $typeAppliance[$i]);
			$temp = explode('_',$commonName);
			if (count($temp) > 1){
				echo ucfirst($temp[1]);
			}else{
				echo ucfirst($temp[0]);
			}
		?>
		</span></div>
	</div>
	<div class="right">
		<div class="title title--right" id="btnHome"><i class="button__icon icon icon-home"></i><br><span>Home</span></button>
	</div>
</div>
	<br>
	<div id='status'>
	<center>
	<span>
	<br>
	</span>
	</center>
	</div>
	<div class="box bg-1">
	<?php

for ($i=0;$i<$noFuction;$i++) {	
	?>
	<form id="form<?php echo $functionList[$i][0];?>" method="post" action="">
		<input name="value" id="hidden<?php echo $functionList[$i][0];?>" type="hidden" value="<?php echo $commonName.":".$functionList[$i][0];?>"/>
<?php
if(isset($_GET['timed'])){
?>
	<input name="type" type="hidden" value="timed"/>
<?php
}else{
?>
	<input name="type" type="hidden" value="instance"/>
<?php }?>

	</form>
	<button class="button button--appliance--list" id="btn<?php echo $functionList[$i][0];?>"><span><?php echo str_replace(" ","<br>",$functionList[$i][1]);?></span></button>
<?php
}

		echo "\n	<br>\n";
	?>
	</div>
	
<?php
fclose($configFile);
}else{
	echo "Hello World!";	
	}
?>

<?php

function getConfig($string) {
	$temp = explode("//", chop($string));
	return $temp[0];
}

?>
