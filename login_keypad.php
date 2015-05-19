<?php
include "hashedpassword.php";//Include hashedPassword, aka $sha1_password variable
if(isset($_SESSION["calledFromIndex"])&&$_SESSION["calledFromIndex"]){
?>
<script>
$(document).ready(function(){
    var el = $(":password").get(0);
    el.focus();
	<?php for ($i = 0; $i < 10; $i++) {
?>
	$("#btn<?php echo $i;?>").click(function(){
		$(":passWord").val($(":passWord").val()+"<?php echo $i;?>");
	});
<?php
	}
	?>
	$("#btnSharp").click(function(){
		$(":passWord").val($(":passWord").val()+"#");
	});
	
	$("#btnStar").click(function(){
		$(":passWord").val($(":passWord").val()+"*");
	});
	
	$("#btnClear").click(function(){
		$(":password").val("");
	});
	
	$("#btnSubmit").click(function(){
		$("#getPassword").submit();
	});
});
</script>

<div class="main">
	<center>
	<form action="" method="post" id="getPassword" class="Form">
	<input type="password" name="loginPassword" class="inputPassword">
	</form>
	<br>
	<button id="btnClear" class="button button--aylen button--round-l button--text-medium"><span>Clear</span></button>
	<button id="btnSubmit" class="button button--aylen button--round-l button--text-medium"><span>Submit</span></button>
	<br>
<?php for ($i = 1; $i < 10; $i+=3) { ?>
		<button id="btn<?php echo $i; ?>" class="button button--aylen button--round-l button--text-medium"><span><?php echo $i; ?></span></button>
		<button id="btn<?php echo $i+1; ?>" class="button button--aylen button--round-l button--text-medium"><span><?php echo $i+1; ?></span></button>
		<button id="btn<?php echo $i+2; ?>" class="button button--aylen button--round-l button--text-medium"><span><?php echo $i+2; ?></span></button><br>
<?php } ?>
	<button id="btnStar" class="button button--aylen button--round-l button--text-medium"><span>*</span></button>
	<button id="btn0" class="button button--aylen button--round-l button--text-medium"><span>0</span></button>
	<button id="btnSharp" class="button button--aylen button--round-l button--text-medium"><span>#</span></button><br>
	</center>
</div>

<?php
}else{
	echo "Hello World!";	
	}
?>