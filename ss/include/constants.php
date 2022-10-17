<?php
/**
 * Constants.php
 *
 * This file is intended to group all constants to
 * make it easier for the site administrator to tweak
 * the login script.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 19, 2004
 */

/**
 * Database Constants - these constants are required
 * in order for there to be a successful connection
 * to the MySQL database. Make sure the information is
 * correct.
 */
// Phil: replaced correct details
define("DB_SERVER", "localhost");
define("DB_USER", "phil");
define("DB_PASS", "honeyphibunch");
define("DB_NAME", "tracksuitgene");

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_USERS", "user");
define("TBL_ACTIVE_USERS",  "active_user");
define("TBL_ACTIVE_GUESTS", "active_guest");
define("TBL_BANNED_USERS",  "banned_user");
/* Phil: Added for relationalised data tables */
define("TBL_GUEST", "guest");
define("TBL_KNOWN_USER", "known_user");
/* Phil: Tables subsequently added */
define("TBL_TRACKING", "tracking");
define("TBL_USER_TRACKING", "user_tracking");

/**
 * Special Names and Level Constants - the admin
 * page will only be accessible to the user with
 * the admin name and also to those users at the
 * admin user level. Feel free to change the names
 * and level constants as you see fit, you may
 * also add additional level specifications.
 * Levels must be digits between 0-9.
 */
define("ADMIN_NAME", "cluesome");
define("GUEST_NAME", "[not logged in]");
define("ADMIN_LEVEL", 9);
define("USER_LEVEL",  1);
define("GUEST_LEVEL", 0);

/**
 * This boolean constant controls whether or
 * not the script keeps track of active users
 * and active guests who are visiting the site.
 * Phil: it must be set true for my changes to work.
 */
define("TRACK_VISITORS", true);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 10);
define("GUEST_TIMEOUT", 5);

/**
 * Cookie Constants - these are the parameters
 * to the setcookie function call, change them
 * if necessary to fit your website. If you need
 * help, visit www.php.net for more info.
 * <http://www.php.net/manual/en/function.setcookie.php>
 */
define("COOKIE_EXPIRE", 60*60*24*100);  //100 days by default
define("COOKIE_PATH", "/");  //Available in whole domain

/**
 * Email Constants - these specify what goes in
 * the from field in the emails that the script
 * sends to users, and whether to send a
 * welcome email to newly registered users.
 */
define("EMAIL_FROM_NAME", "tracksuitgene");
define("EMAIL_FROM_ADDR", "info@tracksuitgene.com");
define("EMAIL_WELCOME", false);		// Phil: change when ready

/**
 * This constant forces all users to have
 * lowercase usernames, capital letters are
 * converted automatically.
 */
define("ALL_LOWERCASE", false);	// Phil: changed
?>