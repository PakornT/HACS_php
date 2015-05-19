<?php
/* List of all API commands in POST. Being resoponded in JSON format.
 ->hashedPassword
	->job	-> 	enqueue 		// Enqueue for both timed and instance
					->	type	// Timed or Instance
					+->	command // Command for specific appliance (hwName, cmdName, Times(How many) or Time(When)))
					+-> inputType	// Either input is Epoch Time, or in minutes and times of execution
			+->	stat			// Server statistic relate command
					->	show
						->	width
						+->	height
					+->	clear
			+->	queue			// Inquire the current queue on the server
					-> type
						-> timed
						-> instance
					-> clear
			+->	delQueue		// Delete the selected queue on the server
					-> 	type
						-> timed
						-> instance
					+->	queueIndex
			+->	info			// Inquire server status : time, keepalive(health), arrive(0==not at home)|depart(1==at home)
			+->	hwList			// List all appliances from config file
			+->	hwCmdList		// List all command for specific appliance from config file
				->	appliance
			+-> changePassword 	// Change password
					-> hashedOldPassword
						-> newPassword
RESPONSES FROM EACH COMMAND
	->job	-> 	enqueue
			+->	stat
					->	show	// image name to be used with <img> tag or else.
					+->	clear
			-> queue
					->	type
						->	timed		// {{Appliance Name, Command, Execution Time, Order Time} X No. of Queue}
						+->	instance	// {{Appliance Name, Command, Times of Execution} X No. of Queue}
			-> info				// {server's health(Alive:0, Unsure:1, Dead!:2),server's time('d-m-Y H:i')}
			-> hwList			// {{appliance's name} X Number of appliance}
			-> hwCmdList		
				->	appliance	// {{cmdCommonName,cmdName,holdable boolean} X No of command for speficied appliance}
*/
	if(!isset($_SESSION)){
		session_start();
	}
	include "hashedpassword.php";
	include "EnDec.php";
	date_default_timezone_set('Asia/Bangkok');
//	if(true){
	if(isset($_POST['hashedPassword']) && $_POST['hashedPassword']==$sha1_password){
		$_SESSION["hashedPassword"]=$_POST['hashedPassword'];
		if($_POST['job']=="enqueue"){//ENQUEUE
			enqueue($_POST['command'], $_POST['type'], $_POST['inputType']);
		}elseif($_POST['job']=="stat"){//INQUIRE server statistic
//			if(isset($_POST['show'])){
//				echo	"<img src=statDisplay.php?show&w=".$_POST['width']."&h=".$_POST['height']." \>";
//				echo	"statDisplay.php?show&w=".$_POST['width']."&h=".$_POST['height'];
//				generateSession();
//			}else
			if(isset($_POST['clear'])){
				file_put_contents("stat.db", "");
				echo "Stat is cleared";
			}
		}elseif($_POST['job']=="queue"){//INQUIRE queue list
			if(isset($_POST['clear'])){
				clearQueue($_POST['type']);
			}else{
				echo json_encode(inquireQueue($_POST['type']));
			}
		}elseif($_POST['job']=="delQueue"){//DELETE queue list
			delQueue($_POST['type'], $_POST['queueIndex']);
		}elseif($_POST['job']=="info"){//INQUIRE server information
			$jsonResponse = array('serverHealth'=>serverHealth(),'serverTime'=>serverTime(),'atHome'=>atHomeCheck());
//			$jsonResponse = array(serverHealth(),serverTime());
			echo json_encode($jsonResponse);
			//DONE
		}elseif($_POST['job']=="hwList"){//LISTING all appliances in config file
			echo json_encode(hwList());
			//DONE
		}elseif($_POST['job']=="hwCmdList"){//LISTING all function for each inquired appliance in config file
			echo json_encode(hwCmdList($_POST['appliance']));
			//DONE
		}elseif($_POST['job']=="changePassword"){
			changePassword($_POST['hashedOldPassword'], $_POST['newPassword']);
		}else{
			echo "Hello World!";
		}
	}elseif(isset($_GET['showStat'])){
//		print_r($_GET);
//		session_regenerate_id(true);
		generateStat($_GET['w'], $_GET['h']);
	}else{
		echo "Hello World!";
	}
	
	//FUNCTIONS SECTION
	
	function enqueue($command, $hwType, $inputType){
		$type = strtolower($hwType);
		echo $type."\n";
		$command = chop($command);
		$data = explode(':',$command);
		$hwName = $data[0];
		$commandName = $data[1];
		$exeTimes = $data[2];
		echo $hwName."\n";
		echo $commandName."\n";
		echo $exeTimes."\n";
		if($type=="instance"){
			$filename="remote_queue.lst";
		}else{
			$filename="timed_queue.lst";
		}
		$file = file($filename);
		if($type=="timed"){
			$serverCurrentTimeArray=getdate();
			$serverCurrentTime=mktime($serverCurrentTimeArray['hours'],$serverCurrentTimeArray['minutes'],$serverCurrentTimeArray['seconds'],$serverCurrentTimeArray['mon'],$serverCurrentTimeArray['mday'],$serverCurrentTimeArray['year']);
			if ($inputType == "epoch"){
				$targetTime = round($exeTimes);
			}else{
				$targetTime = $serverCurrentTime + ($exeTimes*60);
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
				$command = $hwName.":".$commandName.":".$targetTime.":".$serverCurrentTime;
		}
		if($type=="timed"){
			recordAndSortData($filename, $command);
			echo $hwName . " command is successfully queued in " . round(($targetTime-$serverCurrentTime)/60) . " minutes.";
		}else{
			recordData($filename, $command);
			echo $hwName . " command is successfully queued in " . $exeTimes . " time(s).";
		}
	}
	
	function recordData($recordFileName, $data) {
		$file = file($recordFileName);
		array_push($file, encrypt($data)."\n");
	//	array_push($file, $data);
		file_put_contents($recordFileName, $file);
	}

	function recordAndSortData($recordFileName, $data) {
		$file = file($recordFileName);
		array_push($file, encrypt($data)."\n");
		$file = sortBottom($file);
		file_put_contents($recordFileName, $file);
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

	
	function serverHealth(){
		$statusFileName="status.conf";
		$status = file($statusFileName);
		$keepAlive = explode(':', $status[0]);
		$keepAlive = $keepAlive[1];
		$keepAlive = time()-$keepAlive;
		if($keepAlive<90){
			return "0";
		}elseif($keepAlive<180){
			return "1";
		}else{
			return "2";
		}
	}
	function serverTime(){
		return date('d-m-Y H:i');
	}
	function atHomeCheck(){
		$statusFileName="status.conf";
		$status = file($statusFileName);
		return explode(':', $status[1])[1];
	}
	
	function hwList(){
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
		fclose($configFile);
		$jsonResponse = array();
		if (count($numberAppliance)>0){
			for ($i=0;$i<count($typeAppliance);$i++) {
	//			array_push($jsonResponse, explode(':',$typeAppliance[$i])[1]);
				$jsonResponse[$i]=array("name"=>explode(':',$typeAppliance[$i])[1]);
			}
		}else{
			$jsonResponse = array("name"=>"Empty List");
		}
		
//		$jsonResponse = new ArrayObject($jsonResponse);
//		$jsonResponse->append(array("name"=>"Home"));
		array_push($jsonResponse,array("name"=>"Home"));
		return $jsonResponse;
	}
	
	function hwCmdList($hwName) {
		if($hwName == "Home"){
			$statusFileName="status.conf";
			$status = file($statusFileName);
			$beHomeStat = explode(':', $status[1])[1];
			if ($beHomeStat=="1") {
				$command = "depart";
			}elseif ($beHomeStat=="0") {
				$command = "arrive";
			}
			$jsonResponse=array(array("commonCmdName"=>$command,"raspiCmdName"=>$command,"holdable"=>"0"));
		}else{
			$configFile = file("remote_config.conf");
			$i=0;
			while(str_split($configFile[$i],strlen($hwName))[0]!=$hwName){
				$i++;
			}
			$applianceName=chop($configFile[$i]);
			$i++;
			while(str_split($configFile[$i],2)[0]=="//"){
				$i++;
			}
			$noFuction=chop($configFile[$i]);
			for ($j=0;$j<$noFuction;$j++) {
				do{
					$i++;
				}while(str_split($configFile[$i],2)[0]=="//");
				$cmdName = explode(':',chop($configFile[$i]))[0];
				$cmdCommonName = explode(':',chop($configFile[$i]))[1];
				if(count(explode(':',chop($configFile[$i])))>2){
				$holdable = 1;
				}else{
				$holdable = 0;
				}
				$jsonResponse[$j]=array("commonCmdName"=>$cmdCommonName,"raspiCmdName"=>$cmdName,"holdable"=>$holdable);
			}
		}
		return $jsonResponse;
	}
	
	function inquireQueue($hwType){
		$type = strtolower($hwType);
			if($type=="instance"){
				$filename="remote_queue.lst";
			}else{
				$filename="timed_queue.lst";
			}
			$file = file($filename);
			$jsonResponse = array();
			if(count($file)>0){
				for($i=0;$i<count($file);$i++){
					$line=chop(decrypt($file[$i]));
					$line = explode(':', $line);
					$appliance = $line[0];
					$command = $line[1];
					$exeTimes = $line[2];
					if($type=="instance"){
						$jsonResponse[$i]=array("name"=>$appliance,"command"=>$command,"noExecTime"=>$exeTimes);
					}else{
						$orderTimes = $line[3];
						$jsonResponse[$i]=array("name"=>$appliance,"command"=>$command,"execTime"=>date('d-m-Y H:i',$exeTimes),"orderTime"=>date('d-m-Y H:i',$orderTimes));
//						$jsonResponse[$i]=array($appliance,$command,date('d-m-Y H:i',$exeTimes),date('d-m-Y H:i',$orderTimes));
					}
				}
			}else{
				$jsonResponse=array("name"=>"Empty Queue");
			}
			return $jsonResponse;
	}
	
	function clearQueue($hwType){
		$type = strtolower($hwType);
		if($type=="instance")
			$filename="remote_queue.lst";
		else
			$filename="timed_queue.lst";
		file_put_contents($filename, '');
	}
	
	function delQueue($hwType, $index){
		$type = strtolower($hwType);
		if($type=="instance"){
			$filename="remote_queue.lst";
			$file = file($filename);
		}else{
			$filename="timed_queue.lst";
			$file = file($filename);
			for($i=$index;$i<count($file);$i++){
				$line = explode(':',decrypt($file[$i]));
				$applianceName = $line[0];
				$commandName = $line[1];
				$execTime = $line[2];
				$timeStamp = $line[3];
				if ($commandName=="arrive") {
					$statusFileName = "status.conf";
					$status = file($statusFileName);
					$status[1] = "at-home:"."0";
					file_put_contents($statusFileName, $status);
					$file[$i] = encrypt($applianceName.":depart:".$execTime.":".$timeStamp)."\n";
				}elseif ($commandName=="depart") {
					$statusFileName = "status.conf";
					$status = file($statusFileName);
					$status[1] = "at-home:"."1";
					file_put_contents($statusFileName, $status);
					$file[$i] = encrypt($applianceName.":arrive:".$execTime.":".$timeStamp)."\n";
				}
			}
		}			
		unset($file[$index]);
		file_put_contents($filename, $file);
	}
	
	function generateSession(){
		echo session_id();
	}
	
	function generateStat($width, $height){
		require_once 'phplot.php';
		$fileName="stat.db";
		updateStat($fileName);
		$file = file($fileName);
		$maxTime = 0;
		$minTime = 999999;
		$plotFactor = 10;
		$wDelimited = 100;
		$incrementIndex = 1;
		if ((count($file)/$incrementIndex)>(floor(($width-$wDelimited)/$plotFactor))) {
//			$incrementIndex = floor(count($file)*$plotFactor/floor(($_GET['w']-$wDelimited)/$plotFactor));
			$incrementIndex = ceil(count($file)/($width-$wDelimited)*$plotFactor);
		}
//		echo count($file)."<br>";
//		echo (floor(($_GET['w']-$wDelimited)/10))."<br>";
//		echo ceil(count($file)/($_GET['w']-$wDelimited)*10)."<br>";
//		echo $incrementIndex."<br>";
//		echo floor(count($file)/$incrementIndex);
		for($i=0;$i<floor(count($file)/$incrementIndex);$i++){
			$data[$i][0]=strval(date('d/m/y H:i:s',explode(':',decrypt($file[($i*$incrementIndex)]))[0]));
			$data[$i][1]=trim(explode(':',decrypt($file[$i*$incrementIndex]))[1]);
			if($data[$i][1]>$maxTime)
				$maxTime = $data[$i][1];
			if($data[$i][1]<$minTime)	
				$minTime = $data[$i][1];
		}
//		print_r($data);
		if (count($file)==0){
			$data[$i][0]=0;$data[$i][1]=0;$maxTime=2;$minTime=0;
		}
		$plot = new PHPlot($width, $height);
		$plot->SetImageBorderType('plain');

		$plot->SetPlotType('area');
		$plot->SetDataType('text-data');
		$plot->SetDataValues($data);

		# Main plot title:
		$plot->SetTitle("Ping time from RasPi (".$_SERVER['HTTP_HOST'].")\n (with increment ".$incrementIndex.")");
		# Set Y data limits, tick increment, and titles:
		$maxTime = (floor(ceil($maxTime) * 100 / 25)+1)*0.25;
		$minTime = (floor(floor($minTime) * 100 / 25)-1)*0.25;
		if($minTime<0){
			$minTime = 0;
		}
		$incrementIndex=(floor((($maxTime/floor(($height-100)/10))*100)/5)+1)*0.05;
		$plot->SetPlotAreaWorld(NULL, $minTime, NULL, $maxTime);
		$plot->SetYTickIncrement($incrementIndex);
		$plot->SetYTitle('Ping Time (ms)');
		$plot->SetXTitle('Time');
		$plot->SetXLabelAngle(90);
		# Colors are significant to this data:
		$plot->SetRGBArray('large');
		$plot->SetDataColors(array('LimeGreen'));
//		$plot->SetLegend(array('Cherry'));

		# Turn off X tick labels and ticks because they don't apply here:
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->DrawGraph();
	}
	
	function updateStat($statFileName){
		
		$stat = file($statFileName);
		if(count($stat)>0){
			$lastPing = explode(':',decrypt($stat[count($stat)-1]))[0];
			$lastPingDiff = time()-$lastPing;
			if($lastPingDiff>60)
				for($i=1;$i<floor($lastPingDiff/60);$i++){
					array_push($stat, encrypt(($lastPing+($i*60)+(pow(-1,rand(0,1))*rand(0,9))).":0")."\n");
				}
		}
		file_put_contents($statFileName, $stat);
	}
	
	function changePassword($hashedOldPassword, $newPassword){
		$file = file("hashedpassword.php");
		if($hashedOldPassword==explode('\'', $file[1])[1]){
		$file[1] = "	\$sha1_password='".sha1($newPassword)."';//$newPassword\n";
		file_put_contents("hashedpassword.php", $file);
		}
	}
?>