<?php
if(!isset($_SESSION)){
	session_start();
//	$_SESSION["hashedPassword"]='';
}
date_default_timezone_set('Asia/Bangkok');
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
//include "EnDec.php";
//if($_SESSION["hashedPassword"]==$sha1_password){	
//	print_r($_SESSION);
if(isset($_SESSION["calledFromIndex"])||(isset($_POST["hashedPassword"])&&($_POST["hashedPassword"]==$sha1_password))){
	if(!isset($_POST['job'])){
?>
<script>
	$(document).ready(function(){
		$("#btnBeHome").click(function(){
			var times = prompt("When will you arrive home (HH:MM 24Hr Format)?", "15");
			if (times != null) {
				var temp = $("#hiddenBeHome").val();
				$("#hiddenBeHome").val($("#hiddenBeHome").val()+':'+times+'\n');
				$.post('recordData.php', $("#formBeHome").serialize(), function(ret){
					if(ret!=""){
					$('#status').fadeTo('fast',1);
					$('#status span').text(ret);
					setTimeout(function() {
						$('#status').fadeTo('fast',0);
					}, 1000);
					}});
				$("#hiddenBeHome").val(temp);
				window.setTimeout(function () {
					location.reload();
				}, 1500)
			}else{
				$('#status').fadeTo('fast',1);
				$('#status span').text("Command is not queued.");
				setTimeout(function() {
					$('#status').fadeTo('fast',0);
				}, 1000);
			}
//			window.location = '<?php echo $_SERVER['PHP_SELF'].'?queue'; ?>';
		});
	});
</script>
<?php
	$statusFileName="status.conf";
	$status = file($statusFileName);
	$status = explode(':',$status[1]);
	if($status[1]=='1'){
		$icon = 'L';
		$atHome='depart';
	}else{
		$icon = 'U';
		$atHome='arrive';
	}
?>	
	<button class="button button--appliance--list--footer" id="btnBeHome"><?php echo "<b class=\"SteedButton\">".$icon."</b><br><span>". $atHome; ?></span></button>
	<form id="formBeHome" method="post" action=""><input name="value" id="hiddenBeHome" type="hidden" value="Home:<?php echo $atHome; ?>"><input name="type" type="hidden" value="timed"/></form>
<?php
	}elseif($_POST['job']=="serverTime"){
		//		$msg = date('d/m/Y H:i:s');
				$msg = date('d/m/Y H:i');
				echo $msg;
	}elseif(($_POST["hashedPassword"]==$sha1_password)&&($_POST['job']=="keep-alive")){
		//	$t=time();
			$statusFileName="status.conf";
			$status = file($statusFileName);
		//	echo $t . "<br>";
			$status[0] = "keep-alive:".time()."\n";
		//		unset($file[0]);
			file_put_contents($statusFileName, $status);
	}elseif(($_POST["hashedPassword"]==$sha1_password)&&$_POST['job']=='ping'){
		include "EnDec.php";
		if(isset($_POST['value'])){
			echo $_POST['value']."\n";
			$statFileName="stat.db";
			$stat = file($statFileName);
			if(count($stat)>0){
				$lastPing = explode(':',decrypt($stat[count($stat)-1]))[0];
				$lastPingDiff = time()-$lastPing;
				if($lastPingDiff>180)
					for($i=1;$i<floor($lastPingDiff/60);$i++){
						array_push($stat, encrypt(($lastPing+($i*60)).":0")."\n");
					}
			}
			array_push($stat, encrypt(time().":".round($_POST['value'],3))."\n");
			file_put_contents($statFileName, $stat);
		}else{
			echo microtime(true);
			echo "\n";
		}
	}
}else{
		echo 'Hello World!';
}
?>