<?php
if(!isset($_SESSION)){
	session_start();
	$_SESSION["hashedPassword"]='';
}
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
if($_SESSION["hashedPassword"]==$sha1_password){ ?>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />
<?php
	$configFileName="remote_config.conf";
	$configFile = fopen($configFileName, "r") or die("Unable to open configuration file!");
	do{
		$numberAppliance=chop(fgets($configFile));
		$temp = str_split($numberAppliance,2);
	}while($temp[0]=="//");
	do{
		$typeAppliance=chop(fgets($configFile));
		$temp = str_split($numberAppliance,2);
	}while($temp[0]=="//");
	$typeAppliance = explode(',', $typeAppliance);
	do{
		$appliance=chop(fgets($configFile));
		$temp = str_split($appliance,2);
	}while($temp[0]=="//");
?>
<script>
$(document).ready(function(){
	$("#btnTimed").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']; ?>?timed';
	});
	$("#btnInstance").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']; ?>';
	});
<?php
	$explodeFactor=':';

for ($i=0;$i<count($typeAppliance);$i++) {
	if(isset($_GET['timed'])){
?>
	$("#btn<?php $temp=explode($explodeFactor, $typeAppliance[$i]); echo $temp[1]; ?>").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF'].'?timed&appliance='.$typeAppliance[$i]; ?>';
	});
<?php 
	}else{
?>
		$("#btn<?php $temp=explode($explodeFactor, $typeAppliance[$i]); echo $temp[1]; ?>").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?appliance='.$typeAppliance[$i]; ?>';
		});
<?php		
	}
}
?>
	$("#btnLogout").click(function(){
		window.location = '<?php echo $_SERVER['PHP_SELF'].'?logout'; ?>';
	});
});
</script>
<div class="title">
	<div class="left">
	<div class="title title--left" id="btn<?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?>"><i class="button__icon icon icon-<?php if(isset($_GET['timed'])){echo 'cancel';}else{echo 'clock';}?>"></i><span><?php if(isset($_GET['timed'])){echo 'Instance';}else{echo 'Timed';}?></span></div>
	</div>
<!-- USED TO PLACED HERE -->
<div class="main">
	<div class="title title--main"><i class="button__icon icon icon-dashboard"></i><span><?php if(isset($_GET['timed'])){echo 'Timed';}else{echo 'Instance';}?></span>
	</div>
</div>
</div>
<div id='status'>
<center>
<span>
<br>
</span>
</center>
</div>
<div class="box bg-1">
<?php
for ($i=0;$i<count($typeAppliance);$i++) {?>
	<button class="button button--appliance" id="btn<?php $temp=explode($explodeFactor, $typeAppliance[$i]); echo $temp[1]; ?>"><i class="button__icon icon icon-<?php $temp=explode($explodeFactor, $typeAppliance[$i]); echo $temp[0]; ?>"></i><br><span>
<?php $temp=explode($explodeFactor, $typeAppliance[$i]);
	$temp = explode('_',$temp[1]);
	if (count($temp) > 1){
		echo $temp[1];
	}else{
		echo $temp[0];
	}
?>
	</span></button>
	<?php
}
?>
<button class="button button--appliance" id="btnLogout"><i class="button__icon icon icon-cross"></i><br><span>Logout</span></button>
</div>	
<?php
fclose($configFile);
}else{
echo "Hello World!";	
}
?>