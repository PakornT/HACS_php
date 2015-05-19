<?php
if(!isset($_SESSION)){
	session_start();
}
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
date_default_timezone_set("Asia/Bangkok"); 
if(isset($_SESSION["hashedPassword"])||(isset($_POST["hashedPassword"])&&$_POST["hashedPassword"]==$sha1_password)){
	if(isset($_GET['show'])){
//		echo $_GET['width'];
		require_once 'phplot.php';
		require_once "EnDec.php";
		$fileName="stat.db";
		updateStat($fileName);
		$file = file($fileName);
		$maxTime = 0;
		$minTime = 999999;
		$plotFactor = 10;
		$wDelimited = 100;
		$incrementIndex = 1;
		if ((count($file)/$incrementIndex)>(floor(($_GET['w']-$wDelimited)/$plotFactor))) {
//			$incrementIndex = floor(count($file)*$plotFactor/floor(($_GET['w']-$wDelimited)/$plotFactor));
			$incrementIndex = ceil(count($file)/($_GET['w']-$wDelimited)*$plotFactor);
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
		$plot = new PHPlot($_GET['w'], $_GET['h']);
		$plot->SetImageBorderType('plain');

		$plot->SetPlotType('area');
		$plot->SetDataType('text-data');
		$plot->SetDataValues($data);

		# Main plot title:
		$plot->SetTitle('Ping time from RasPi (with increment '.$incrementIndex.')');
		# Set Y data limits, tick increment, and titles:
		$maxTime = (floor(ceil($maxTime) * 100 / 25)+1)*0.25;
		$minTime = (floor(floor($minTime) * 100 / 25)-1)*0.25;
		if($minTime<0){
			$minTime = 0;
		}
		$incrementIndex=(floor((($maxTime/floor(($_GET['h']-100)/10))*100)/5)+1)*0.05;
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
	}elseif(isset($_POST['job']) && $_POST['job']=='clear'){
		$fileName="stat.db";
//		$file = file($fileName);
		file_put_contents($fileName, "");
		echo "Stat is cleared";
	}elseif($_SESSION["hashedPassword"]==$sha1_password){
?>

<link rel="stylesheet" type="text/css" href="css/table.css" />
<link rel="stylesheet" type="text/css" href="css/styleAppliance.css" />
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />
<script>
	$(document).ready(function(){
		window.setTimeout(function () {
			location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?stat";
		}, 60000);
		$("#btnHome").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF']; ?>';
		});
		$("#btnInstance").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?queue'; ?>';
		});
		$("#btnTimed").click(function(){
			window.location = '<?php echo $_SERVER['PHP_SELF'].'?timed&queue'; ?>';
		});
		$("#btnTrash").click(function(){
			if (confirm('Are you sure you want to clear stat?')) {
				// Clear it!
				$.post('statDisplay.php', $("#formTrash").serialize(), function(ret){
					if(ret!=""){
					$('#status').fadeTo('fast',1);
					$('#status span').text(ret);
					setTimeout(function() {
						$('#status').fadeTo('fast',0);
					}, 1000);
					}});
				setTimeout(function(){location.reload();}, 2500);
			} else {
				// Do nothing!
			}

		});
	});
</script>
<div class="title">
	<div class="left">
		<div class="title title--left" id="btnTrash">
		<i class="button__icon icon icon-trash"></i>
		<br><span>Clear Stat</span></div>
	</div>
	<div class="main">
		<div class="title title--main"><b class="SteedTitle">0</b><span>Statistics</span></div>
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
<script>
$(document).ready(function() {
	var width = Math.round($(window).width()*0.85);
	var height = Math.round(width*3/8);
	$('#mainDisplay').html("<img src=statDisplay.php?show&w="+width+"&h="+height+" />");
});
</script>
<!-- 	<img src=statDisplay.php?show&w=1024&h=600 /> -->
<div class="box bg-1" id="mainDisplay">
</div>
<form id="formTrash" method="post" action="">
	<input name="job" id="hiddenTrash" type="hidden" value="clear"/>
</form>
<?php
	}
}else{
	if(isset($_GET['show'])){
		$my_img = imagecreate( 200, 80 );
		$background = imagecolorallocate( $my_img, 0, 0, 255 );
		$text_colour = imagecolorallocate( $my_img, 255, 255, 0 );
		$line_colour = imagecolorallocate( $my_img, 128, 255, 0 );
		imagestring( $my_img, 4, 30, 25, "  Hello  World!", $text_colour );
		imagesetthickness ( $my_img, 5 );
		imageline( $my_img, 30, 45, 165, 45, $line_colour );

		header( "Content-type: image/png" );
		imagepng( $my_img );
		imagecolordeallocate( $line_color );
		imagecolordeallocate( $text_color );
		imagecolordeallocate( $background );
		imagedestroy( $my_img );}
	else{
		echo "Hello World!";	
	}
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
?>