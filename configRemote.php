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
<link rel="stylesheet" type="text/css" href="css/jquery-linedtextarea.css" />
<link rel="stylesheet" type="text/css" href="css/base.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/buttons.css" />
<link rel="stylesheet" type="text/css" href="css/steed-fonts.css" />
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />
<script src="scripts/jquery-2.1.3.js"></script>
<script src="scripts/jquery-linedtextarea.js"></script>
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
		}elseif($_POST!=null){
?>
    <script>
        $(document).ready(function () {
            // Handler for .ready() called.
            window.setTimeout(function () {
                location.href = "<?php echo $_SERVER['PHP_SELF']; ?>";
            }, 1500)
        });
    </script>
<?php
            if(isset($_POST['btnReverse'])){
                echo "Configurations is reversed! Reload in 2 seconds...";
                $file = file("remote_config.bak.conf");
                file_put_contents("remote_config.conf", $file);
            }else{
                echo "Configurations is saved! Reload in 2 seconds...";
                $file = file("remote_config.conf");
                file_put_contents("remote_config.bak.conf", $file);
                $file = $_POST['config'];
                file_put_contents("remote_config.conf", $file);
            }
        }else{
			// PUT ALL THE COMMANDS FOR CONFIGURING RasPiR
            ?>
	<script>
		$(document).ready(function(){
			$("#lstAppliance").click(function(){
				window.location = '<?php echo $_SERVER['PHP_SELF'].'?'; ?>'+$("#lstAppliance").val();
			});
        });
	</script>
<div class="title">
<div class="main">
	<div class="title title--main"><span>Configurations</span>
	</div>
</div>
</div>
<script>
$(function() {

  // Target a single one
  $(".configArea").linedtextarea();

});
</script>
    <div class="box bg-1">
<?php
            $file = file("remote_config.conf");
//            echo "<div class=\"box bg-1\">\n";
echo "<textarea rows=\"6\" cols=\"50\" class=\"configArea\" name=\"config\" form=\"configFile\">";
            foreach( $file as $line){
                echo $line;
            }
echo "</textarea>";
		}
        
        if(!isset($_GET['logout']) && $_POST == null){
?>
    </div>
	<br />
	<div class="footer">
	<script>
		$(document).ready(function(){
			$("#btnLogout").click(function(){
				window.location = '<?php echo $_SERVER['PHP_SELF'].'?logout'; ?>';
			});
		});
	</script>
    <form action="" method="post" id="configFile" class="Form">
        <input type="submit" name="btnSubmit" class="inputPassword" value="Save">
        <input type="submit" name="btnReverse" class="inputPassword" value="Reverse">
	</form>
	<button class="button button--appliance--list--footer" id="btnLogout"><i class="button__icon icon icon-cross"></i><br><span>Logout</span></button>
	</div>
<?php
		}
    }
?>
</body>
</html>