<?php 
include 'class/value_saver.php';
$d= dirname(__FILE__);

$saver=new value_saver($d);

$iterate=$saver->get();
if ($iterate!= 1)
{
if(!isset($HTTP_COOKIE_VARS["users_resolution2"])) 

//means cookie is not found set it using Javascript 
{ 
$iterate=1;
$saver->save($iterate); 
?> 
<script language="javascript"> 
<!-- 
writeCookie(); 

function writeCookie() 
{ 

var the_cookie = "users_resolution2="+ screen.width +"x"+ screen.height; 

document.cookie=the_cookie 

location ='<?php echo $_SERVER['PHP_SELF'];?>'; 
} 
//--> 
</script> 
<?php
} 
else{
$screen_resolution = $HTTP_COOKIE_VARS["users_resolution2"]; 
} 
}
else{
$iterate=0;
$saver->save($iterate); 
$screen_resolution = $HTTP_COOKIE_VARS["users_resolution2"]; 
} 
echo $screen_resolution; 
?> 


File Name : value_saver.php


<?php
class value_saver{

var $fileDir;
function value_saver($filedir){


$this->fileDir=$filedir;
}
// save value to file
function save($data){
if(!$fp=fopen($this->fileDir.'/conf/data.txt','w')){
trigger_error('Error opening data
file',E_USER_ERROR);
}

fwrite($fp,$data);
fclose($fp);
}
// get value from file
function get(){

$contents= file_get_contents($this->fileDir.'/conf/data.txt');
return $contents;
}
}
?>