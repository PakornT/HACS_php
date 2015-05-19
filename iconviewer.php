<!-- <link rel="stylesheet" type="text/css" href="css/styleAppliance.css" /> -->
<style>
.button.button--appliance {
	width: 220px;
	height: 220px;
	min-width: 0;
	padding: 0;
	color: #37474f;
	-webkit-transition: color 0.3s;
	transition: color 0.3s;
/*	text-align: center;	*/
}
.button--appliance .button__icon {
	font-size: 80px;
/*	width: 22px;*/
}
</style>
<link rel="stylesheet" type="text/css" href="css/styleApplianceIcon.css" />

<?php

$icon=array("icon-box",
"icon-write",
"icon-clock",
"icon-reply",
"icon-reply-all",
"icon-forward",
"icon-flag",
"icon-search",
"icon-trash",
"icon-envelope",
"icon-bubble",
"icon-bubbles",
"icon-user",
"icon-users",
"icon-cloud",
"icon-download",
"icon-upload",
"icon-rain",
"icon-sun",
"icon-moon",
"icon-bell",
"icon-folder",
"icon-pin",
"icon-sound",
"icon-microphone",
"icon-camera",
"icon-image",
"icon-cog",
"icon-calendar",
"icon-book",
"icon-map-marker",
"icon-store",
"icon-support",
"icon-tag",
"icon-heart",
"icon-video-camera",
"icon-trophy",
"icon-cart",
"icon-eye",
"icon-cancel",
"icon-chart",
"icon-target",
"icon-printer",
"icon-location",
"icon-bookmark",
"icon-monitor",
"icon-cross",
"icon-plus",
"icon-left",
"icon-up",
"icon-browser",
"icon-windows",
"icon-switch",
"icon-dashboard",
"icon-play",
"icon-fast-forward",
"icon-next",
"icon-refresh",
"icon-film",
"icon-home");

$indicator = count($icon);

for ($i=0;$i<$indicator;$i++) {
?>
<button class="button button--appliance"><i class="button__icon icon 
<?php echo $icon[$i];?>
"></i><br><span><?php echo $icon[$i];?></span></button>
<?php
	if (($i+1) % 5 == 0)
		echo "\n<br>\n";
}

?>