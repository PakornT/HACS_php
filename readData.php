<?php
$queueFileName="remote_queue.lst";
$queueTimedFileName="timed_queue.lst";
include 'EnDec.php';
include "hashedpassword.php";
if(isset($_POST['hashedPassword']) && $_POST['hashedPassword']==$sha1_password){
	if(isset($_POST['action'])&&$_POST['action']=='timeup'){
		readAndDeleteFirstLine($queueTimedFileName);
	}else{
		if($_POST['type']=='timed'){
			$file = readLines($queueTimedFileName);
			if(count($file)>0){
				for($i=0;$i<count($file);$i++){
					echo trim(decrypt($file[$i]));
					if ($i!=count($file)-1){
						echo "\n";
					}
				}
			}else{
				echo 'Empty';
			}
		}else{
			$encrypted = readAndDeleteFirstLine($queueFileName);
			if ($encrypted!=''){
	//				$output = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($sha1_password), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($sha1_password))), "\0");
				$output = decrypt($encrypted);
			}else{
				$output='Empty';
			}
			echo trim($output);
		}
	}
}else{
	echo "Hello World!";
}
	function readLines($filename) {
		$file = file($filename);
//		$output = $file[0];
//		unset($file[0]);
		file_put_contents($filename, $file);
		return $file;
	}

	function readAndDeleteFirstLine($filename) {
		$file = file($filename);
		if(count($file)>0){
		$output = $file[0];
		unset($file[0]);
		file_put_contents($filename, $file);
		}else{
			$output = '';
		}
		return $output;
	}
//	function echobr($text='') {echo $text."\n<br>\n";}
?>