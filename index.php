<?php
if(!isset($_SESSION)){
	session_start();
}
date_default_timezone_set('Asia/Bangkok');
//if(!isset($_SESSION["hashedPassword"]))
//session_start();
//	print_r($_SESSION);
	include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
	$_SESSION["calledFromIndex"]=True;
//$sha1_password='e4d3d3f0fce651d09aee5480ec5e58268ccc2409';//1976
if(isset($_POST['loginPassword'])){
	$hashedPassword = sha1($_POST['loginPassword']);
	$_SESSION["hashedPassword"]=$hashedPassword;
}
?>
<!DOCTYPE html>
<html>
<head>
<!-- Home Appliance Controll System -->
<title>HACS</title>
<!-- <link rel="stylesheet" type="text/css" href="css/normalize.css" /> -->
<link rel="stylesheet" type="text/css" href="css/base.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/buttons.css" />
<link rel="stylesheet" type="text/css" href="css/steed-fonts.css" />
<!-- <link rel="stylesheet" type="text/css" href="css/panel.css" /> -->
<script src="scripts/jquery-2.1.3.js"></script>
<?php
 /*
<!-- <script> -->
<!-- $(document).ready(function(){ -->
<!-- 	$("button").click(function(){ -->
<!-- 		var txt = ""; -->
<!-- 		txt += "Document width/height: " + $(document).width(); -->
<!-- 		txt += "x" + $(document).height() + "\n"; -->
<!-- 		txt += "Window width/height: " + $(window).width(); -->
<!-- 		txt += "x" + $(window).height(); -->
<!-- 		$("#div1").html(txt); -->
<!-- 	}); -->
<!-- }); -->
<!-- </script> -->
*/
?>

<?php

	if(isset($_SESSION["hashedPassword"])){
		if($_SESSION["hashedPassword"]!=$sha1_password){
		session_destroy();
		}
	}
?>

</head>
<?php
$statusFileName="status.conf";
$status = file($statusFileName);
$keepAlive = explode(':', $status[0]);
$keepAlive = $keepAlive[1];
//echo time();
if(time()-$keepAlive<90){
	$keepAlive="<font color=\"#00FF00\">Alive</font>";
}elseif(time()-$keepAlive<180){
	$keepAlive="<font color=\"#FFFF00\">Unsure</font>";
}else{
	$keepAlive="<font color=\"red\">Dead!</font>";
}
?>
<body>
<?php
//echo $_SERVER['REQUEST_URI'];
?>
<script>
$(document).ready(function() {
	function update() {
//		$("#timer").html("Hello World!");
		$.ajax({
			type: 'POST',
			url: 'homeAndServer.php',
			data: {job:"serverTime"},
			dataType: "text",
		 	timeout: 5000,
			success: function(data) {
				$("#timer").html(data); 
				window.setTimeout(update, 5000);
		 }
		});
	 }
	 update();
});
</script>
<div class="status-header">
<?php if(isset($_GET['logout'])){
echo "	<span class=\"left\">\n";
echo "	Logging out...\n";
echo "	</span>\n";
}else{
?>
	<span class="left" id="timer">
	Left
	</span>
<?php } ?>
	<span class="right">
		RasPi Status:<?php  echo $keepAlive;?>
	</span>
</div>

<?php 
	if(!isset($_SESSION["hashedPassword"])){
		include "login_keypad.php";
	}elseif($_SESSION["hashedPassword"]!=$sha1_password){
		echo "<div class=\"errorMessage\">Incorrect Password!</div>\n";
		include "login_keypad.php";
	}else{
//		session_destroy();
		if(isset($_GET['logout'])){
			session_destroy();
?>
			<script>
			$(document).ready(function () {
				// Handler for .ready() called.
				window.setTimeout(function () {
					location.href = "<?php echo $_SERVER['PHP_SELF']; ?>";
				}, 1500)
			});
			</script>
			Logged out.<br>
			Reload in 2 seconds...
<?php
		}elseif(isset($_GET['appliance'])){
//			echo $_GET['appliance'];
			include "configAppliance.php";
		}elseif(isset($_GET['queue'])){
			//			echo $_GET['appliance'];
						include "queue.php";
		}elseif(isset($_GET['stat'])){
			//			echo $_GET['appliance'];
						include "statDisplay.php";
		}else{
			include "selectAppliance.php";
		}
		if(!isset($_GET['logout'])){
?>
<script>
	$(document).ready(function(){
		$("#btnQueue").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?queue&timed'; ?>';
		});
		$("#btnStat").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?stat'; ?>';
		});
	});
</script>
	<br />
	<div class="footer">
<!-- 	<center> -->
		<button class="button button--appliance--list--footer" id="btnStat"><b class="SteedButton">0</b><br><span>Stat</span></button>
		<button class="button button--appliance--list--footer" id="btnQueue"><i class="button__icon icon icon-chart"></i><br><span>Queue</span></button>
<?php
	include "homeAndServer.php";
//	require_once "homeAndServer.php";
//	echo "	</center>";
?>
	</div>
<?php
		}
	}
?>
</body>
</html>