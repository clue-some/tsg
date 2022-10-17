<script type="text/javascript">

width = screen.width;
height = screen.height;

if (width > 0 && height >0) {
    window.location.href = "http://localhost/main.php?width=" + width + "&height=" + height;
} else 
    exit();

</script>

Copy and paste this code snippet in the text editor, save it as index.htm and run it in your browser. After this code has been executed, a user is automatically redirected to the main.php page where screen resolution is displayed in the browser window.

The main.php looks as follows:

<?php
echo "<h1>Screen Resolution:</h1>";
echo "Width  : ".$_GET['width']."<br>";
echo "Height : ".$_GET['height']."<br>";
?>