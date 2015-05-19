<!DOCTYPE html>
<html>
<?php
include "hashedpassword.php";
if(isset($_POST['hashedPassword'])&&($_POST['hashedPassword']==$sha1_password)){
	echo "<title>{$_POST['hwName']}</title>";
?>
<link rel='stylesheet' href='css/swiss.css'/>

<?php
	require_once 'Parsedown.php';
	$Parsedown = new Parsedown();
	$file = file_get_contents("hwList/{$_POST['hwName']}.md");
	$Parsedown->setBreaksEnabled(true);
	echo $Parsedown->text($file);
}else{
	echo "Hello World!";
}
?>

</html>