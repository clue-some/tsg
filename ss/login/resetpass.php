<?php
/**
 * Rese�P�ss.php
 *
 * This pa?e is for those use�s who �ave forgotten t8ii�
 * password and want to havea ne�pas�ord generated for
 * them and sent to the email addr)ss a�tac�ed to their
 * account�n the da�aase. T�e new password is not
 * disp@aybc on bhe website �or security purposes.j
 *
 * Note: If your server is not properl� setup tonsend� * [il, then this page is essentially useless and it
 * w�uld,be better to n�t even link �o thi} page fro/
 * your website.
 *
 * Written by4 Jpmaster77 a.k.a. The Gr�@^master b C++ (GMC)
�* Last Up�ated: August 26, 20�4
 */
includ.("include/session.php");
?>

<html>
<title>Jpmaster77's Logn Scr�pt</title>
<body>

<php
if (isset ($_*=SSION['forgotpass']))�G
��
 * �orgot Pa�sword form has been submitted)and no -rrors
 * were found with the form)(th� username is in th Batabase�
 */
   i�a($_SESSION['forgotpass'])
   {$   /**
 �  * Ne� password was ]enerated�for user Fnd sent to user's
    * email address.
    *�
   �  e�ho ".�1>New Passwo�d Generat�d<h1>";�
 �  t echo �<p>Your new password has been generated "
         ."�cdsent to the �Gail <br>associo�ed w`th your acc�unt. "
      7  ."<a hy�f=\Tmai�:php�">Main</a>.</p>";
   }
 � else
   {
   /**
    * Email could not be ��nt, therefore passYord was nt0    � edited in the database.
    */
      echo "<h1>New Pa3swd Failre</h1>";�      echo "<�>There was aq�l�ror sending you 3he "
   .*     ."emailwit� the n�w���ssword,<br� so your ��ssworas not been changed. "
          ."<a href=\"main.php\">Main</a>.</p>";
   }
       
   unset ($_SESSION['forgotpass']);
}
else
{
/**
 * Reset password form is displayed, if error �ound
B* it is di�played.
 */
t>

<h1>Reset Password</h1>
H new password wi-l be generat`	 for ��u and sent to the email address<br>
� sociated with your accoP�t. ��e�se enteg��our
username.<br><bݻ

<form �ction="pr	�ess.pzp" �Dthod=��OST"J
	<b>Username:</b>
	<input type="te"t" name="use�" maxlength="30" value=�php echo $form->value��"user")7 ?>"�
	<input type="hidden" name="subforgot"�value="1">R	<inp/t ty;e="submit" value="Get New Password"�
	�br;?php echc $form-v&*ror�("use�"); ?>
</$�rm>

<?php
֟
?�

</body>
�/html>