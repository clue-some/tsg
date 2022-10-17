<?php
/**
 * Mailer.php
 *
 * The Mailer class is meant to simplify the task of sending
 * emails to users. Note: this email system will not work
 * if your server is not set up to send mail.
 *
 * If you are running Windows and want a mail server, check
 * out this website to see a list of freeware programs:
 * <http://www.snapfiles.com/freeware/server/fwmailserver.html>
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 19, 2004
 */

class Mailer
{
   /**
    * sendWelcome - Sends a welcome message to the newly
    * registered user, also supplying the username and
    * password.
    */
   function sendWelcome ($user, $email, $pass)
   {
      $from = "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM_ADDR . ">";
      $subject = "tracksuitgene.com";
      $body = $user . ",\n\n"
             . "Your login details are: \n\n"
             . "Username: " . $user . "\n"
             . "Password: " . $pass . "\n\n"
             . "When required, new passwords will be generated automatically\n"
             . "and sent to this email address.\n"
             . "You may change these details yourself when signed in.\n\n"
             . "Your details will never be shared with anyone else.\n\n"
             . "Tracksuitgene.com will accept this code:\n"
             . /* PJIS: tsg->generateCode ($user, "newuser") */ "URELALOR3" . "\n"
             . "http://www.tracksuitgene.com/\n\n"
             . "Thanks.\n"
             . "- tracksuitgene";

      $body = wordwrap ($body, 70);
      return mail ($email, $subject, $body, $from);
   }

   /**
    * sendNewPass - Sends the newly generated password
    * to the user's email address that was specified at
    * sign-up.
    */
   function sendNewPass ($user, $email, $pass)
   {
      $from = "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM_ADDR . ">";
      $subject = "Tracksuitgene.com";
      $body = $user.",\n\n"
             . "Your login details are:\n\n"
             . "Username: ".$user."\n"
             . "New Password: ".$pass."\n\n"
             . "You may change these details yourself when signed in.\n"
             . "Your details will never be shared with anyone else.\n\n"
             . "http://www.tracksuitgene.com/\n\n"
             . "Thanks.\n"
             . "- Tracksuitgene.";

      $body = wordwrap ($body, 70);
      return mail ($email, $subject, $body, $from);
   }
};

/* Initialize mailer object */
$mailer = new Mailer;

?>