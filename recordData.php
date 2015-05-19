<?php
	date_default_timezone_set('Asia/Bangkok');
	include "hashedpassword.php";
	include "EnDec.php";
if (isset($_POST['value'])&&!empty($_POST['value']))
{
	if($_POST['type']=='timed'){
		$recordFileName="timed_queue.lst";
	}else{
		$recordFileName="remote_queue.lst";
	}
	$data = explode(':',$_POST['value']);
	$commonName = $data[0];
	$commandName = $data[1];
	$times = $data[2];
	if($_POST['type']=='timed'){
		$serverCurrentTimeArray=getdate();
		$serverCurrentTime=mktime($serverCurrentTimeArray['hours'],$serverCurrentTimeArray['minutes'],$serverCurrentTimeArray['seconds'],$serverCurrentTimeArray['mon'],$serverCurrentTimeArray['mday'],$serverCurrentTimeArray['year']);
		if (count(explode(':', $_POST['value']))>3){
			$targetTime = mktime($data[2],trim($data[3]),$serverCurrentTimeArray['seconds'],$serverCurrentTimeArray['mon'],$serverCurrentTimeArray['mday'],$serverCurrentTimeArray['year']);
			if($targetTime<$serverCurrentTime){
				$targetTime = mktime($data[2],$data[3],$serverCurrentTimeArray['seconds'],$serverCurrentTimeArray['mon'],$serverCurrentTimeArray['mday']+1,$serverCurrentTimeArray['year']);
			}
		}else{
//			print_r($data);
			$targetTime = $serverCurrentTime + ($data[2]*60);
		}
		if ($commandName=="arrive") {
			$statusFileName="status.conf";
			$status = file($statusFileName);
			$status[1] = "at-home:"."1";
			file_put_contents($statusFileName, $status);
		}elseif ($commandName=="depart") {
			$statusFileName="status.conf";
			$status = file($statusFileName);
			$status[1] = "at-home:"."0";
			file_put_contents($statusFileName, $status);
		}
		$data = $commonName.":".$commandName.":".$targetTime.":".$serverCurrentTime;
		$output = $data;
	}else{
		$output = $_POST['value'];
	}
	
	if($_POST['type']=='timed'){
		recordAndSortData($recordFileName, $output);
		echo $commonName . " command is successfully queued in " . ($targetTime-$serverCurrentTime)/60 . " minutes.";
//		print_r($_POST['value']);
	}else{
		recordData($recordFileName, $output);
		echo $commonName . " command is successfully queued in " . $times . " time(s).";
	}
}else{
	echo "Hello World";
}

function recordData($recordFileName, $data) {
	$file = file($recordFileName);
	array_push($file, encrypt($data)."\n");
//	array_push($file, $data);
	file_put_contents($recordFileName, $file);
	return 0;
}

function recordAndSortData($recordFileName, $data) {
	$file = file($recordFileName);
	array_push($file, encrypt($data)."\n");
//	$output = $file[0];
//	unset($file[0]);
	$file = sortBottom($file);
	file_put_contents($recordFileName, $file);
	return 0;
}

function sortBottom($file){
	if(count($file)>1){
		for($i=count($file)-2;$i>=0;$i--){
//			$bottom = explode(':',$file[$i+1])[2];
			$bottom = explode(':',decrypt($file[$i+1]))[2];
			$top = explode(':',decrypt($file[$i]))[2];
			if($bottom<$top){
				$temp = $file[$i+1];
				$file[$i+1]=$file[$i];
				$file[$i] = $temp;
			}else{
				unset($temp);
				break;
			}
		}
	}
	return $file;
}

//function decrypt($encrypted){	
//	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($sha1_password), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($sha1_password))), "\0");
//}
//	
//function encrypt($data) {
//	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($sha1_password), $data, MCRYPT_MODE_CBC, md5(md5($sha1_password))));
//}
?>