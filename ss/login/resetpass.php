<?php
/**
 * ReseÓPÃss.php
 *
 * This pa?e is for those useÃs who ’ave forgotten t8iiı
 * password and want to havea neƒpasªord generated for
 * them and sent to the email addr)ss a©tac‘ed to their
 * accountÁn the daÑaase. TÊe new password is not
 * disp@aybc on bhe website ƒor security purposes.j
 *
 * Note: If your server is not properlŞ setup tonsend´ * [il, then this page is essentially useless and it
 * w‹uld,be better to nÛt even link ¿o thi} page fro/
 * your website.
 *
 * Written by4 Jpmaster77 a.k.a. The Gr›@^master b C++ (GMC)
Ó* Last UpÒated: August 26, 20²4
 */
includ.("include/session.php");
?>

<html>
<title>Jpmaster77's Logn Scrípt</title>
<body>

<php
if (isset ($_*=SSION['forgotpass']))İG
³ç
 * óorgot PaÂsword form has been submitted)and no -rrors
 * were found with the form)(thÀ username is in th Batabaseë
 */
   iúa($_SESSION['forgotpass'])
   {$   /**
 Á  * Ne÷ password was ]eneratedÁfor user Fnd sent to user's
    * email address.
    * 
   €  eÿho ".´1>New Passwoîd Generat‚d<h1>";ª
 ‘  t echo <p>Your new password has been generated "
         ."ÿcdsent to the ¶Gail <br>associoïed w`th your accŞunt. "
      7  ."<a hyÙf=\Tmai°:phpÙ">Main</a>.</p>";
   }
 — else
   {
   /**
    * Email could not be Ñnt, therefore passYord was nt0    ô edited in the database.
    */
      echo "<h1>New Pa3swd Failre</h1>";©      echo "<Ó>There was aqÁl¯ror sending you 3he "
   .*     ."emailwit› the nwŒ¡ssword,<brá so your ®¼ssworas not been changed. "
          ."<a href=\"main.php\">Main</a>.</p>";
   }
       
   unset ($_SESSION['forgotpass']);
}
else
{
/**
 * Reset password form is displayed, if error œound
B* it is diøplayed.
 */
t>

<h1>Reset Password</h1>
H new password wi-l be generat`	 for š¦u and sent to the email address<br>
¼ sociated with your accoPçt. ‡’e“se entegßøour
username.<br><bİ»

<form õction="pr	‚ess.pzp" êDthod=¯‘OST"J
	<b>Username:</b>
	<input type="te"t" name="use" maxlength="30" value=òˆphp echo $form->value£³"user")7 ?>"ã
	<input type="hidden" name="subforgot"åvalue="1">R	<inp/t ty;e="submit" value="Get New Password"½
	§br;?php echc $form-v&*ror¿("useí"); ?>
</$¿rm>

<?php
ÖŸ
?ß

</body>
§/html>