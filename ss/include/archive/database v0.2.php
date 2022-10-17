<?php
/**
 * Database.php
 *
 * The Database class simplifies the task of accessing
 * information from the website's database.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 17, 2004
 */
include("constants.php");

class MySQLDB
{
   var $connection;         //The MySQL database connection
   var $num_active_users;   //Number of active users viewing site
   var $num_active_guests;  //Number of active guests viewing site
   var $num_members;        //Number of signed-up users
   /* Note: call getNumMembers() to access $num_members! */

   /* Class constructor
    * Phil: checked for v0.2 db changes.
    */
   function MySQLDB()
   {
      /* Make connection to database */
      $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die(mysql_error());
      mysql_select_db(DB_NAME, $this->connection) or die(mysql_error());

      /**
       * Only query database to find out number of members
       * when getNumMembers() is called for the first time,
       * until then, default value set.
       */
      $this->num_members = -1;

      if (TRACK_VISITORS) {

         /* Calculate number of users at site */
         $this->calcNumActiveUsers();

         /* Calculate number of guests at site */
         $this->calcNumActiveGuests();
      }
   }

   /**
    * confirmUserPass - Checks whether or not the given
    * username is in the database, if so it checks if the
    * given password is the same password in the database
    * for that user. If the user doesn't exist or if the
    * passwords don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    * Phil: checked for v0.2 db changes.
    */
   function confirmUserPass ($username, $password)
   {
      /* Add slashes if necessary (for query) */
      if (!get_magic_quotes_gpc()) {
	      $username = addslashes($username);
      }

      /* Verify that user is in database */
      $q = "SELECT password FROM ".TBL_KNOWN_USER." WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);
      if (!$result || (mysql_numrows($result) < 1)) {
         return 1; //Indicates username failure
      }

      /* Retrieve password from result, strip slashes */
      $dbarray = mysql_fetch_array($result);
      $dbarray['password'] = stripslashes($dbarray['password']);
      $password = stripslashes($password);

      /* Validate that password is correct */
      if ($password == $dbarray['password']) {
         return 0; //Success! Username and password confirmed
      }
      else {
         return 2; //Indicates password failure
      }
   }

   /**
    * confirmUserID - Checks whether or not the given
    * username is in the database, if so it checks if the
    * given userid is the same userid in the database
    * for that user. If the user doesn't exist or if the
    * userids don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    */
   function confirmUserID ($username, $userid)
   {
      /* Add slashes if necessary (for query) */
      if (!get_magic_quotes_gpc()) {
	      $username = addslashes($username);
      }

      /* Verify that user is in database
       * Phil: changed for v0.2 db
      $q = "SELECT userid FROM ".TBL_USERS." WHERE username = '$username'"; */
      $q = "SELECT u.userid ".
      	   "FROM ".TBL_KNOWN_USER." AS ku ".
      	   "JOIN ".TBL_USERS." AS u ".
      	   "ON u.id = ku.user_id ".
      	   "WHERE ku.username = '$username'";
      $result = mysql_query($q, $this->connection);
      if (!$result || (mysql_numrows($result) < 1)) {
         return 1; //Indicates username failure
      }

      /* Retrieve userid from result, strip slashes */
      $dbarray = mysql_fetch_array($result);
      $dbarray['userid'] = stripslashes($dbarray['userid']);
      $userid = stripslashes($userid);

      /* Validate that userid is correct */
      if ($userid == $dbarray['userid']) {
         return 0; //Success! Username and userid confirmed
      }
      else {
         return 2; //Indicates userid invalid
      }
   }

   /**
    * usernameTaken - Returns true if the username has
    * been taken by another user, false otherwise.
    * Phil: changed for v0.2 db
    */
   function usernameTaken($username)
   {
      if (!get_magic_quotes_gpc()) {
         $username = addslashes($username);
      }
      $q = "SELECT username FROM ".TBL_KNOWN_USER." WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);
      return (mysql_numrows($result) > 0);
   }

   /**
    * usernameBanned - Returns true if the username has
    * been banned by the administrator.
    * Phil: changed for v0.2 db
    */
   function usernameBanned($username)
   {
      if (!get_magic_quotes_gpc()) {
         $username = addslashes($username);
      }
/*      $q = "SELECT username FROM ".TBL_BANNED_USERS." WHERE username = '$username'";*/
      $q = "SELECT username ".
      	   "FROM ".TBL_BANNED_USERS.
      	   "JOIN ".TBL_KNOWN_USER.
      	   "ON user_id = banned_id ".
      	   "WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);
      return (mysql_numrows($result) > 0);
   }

   /**
    * addNewUser - Inserts the given (username, password, email)
    * info into the database. Appropriate user level is set.
    * Returns true on success, false otherwise.
    * Phil: changed for v0.2 db
    */
   function addNewUser($username, $password, $email)
   {
      $time = time();

      /* If admin sign up, give admin user level */
      if (strcasecmp($username, ADMIN_NAME) == 0){
         $ulevel = ADMIN_LEVEL;
      } else {
         $ulevel = USER_LEVEL;
      }
/*      $q = "INSERT INTO ".TBL_USERS." VALUES ('$username', '$password', '0', $ulevel, '$email', $time)";*/

      $q = "INSERT INTO ".TBL_USERS." (userid, userlevel, timestamp) ".
      	   "VALUES ('0', $ulevel, $time)";
      $success = mysql_query($q, $this->connection);

      $q = "INSERT INTO ".TBL_KNOWN_USER." (user_id, username, password, email) ".
      	   "VALUES (LAST_INSERT_ID(), '$username', '$password', '$email')";

      return ($success and mysql_query($q, $this->connection));
   }

   /**
    * updateUserField - Updates a field, specified by the field
    * parameter, in the user's row of the database.
    * Phil: changed for v0.2 db
    */
   function updateUserField($username, $field, $value)
   {
/*      $q = "UPDATE ".TBL_USERS." SET ".$field." = '$value' WHERE username = '$username'";*/
      $q = "UPDATE ".TBL_USERS." JOIN ".TBL_KNOWN_USER." "
      	  ."ON id = user_id "
      	  ."SET ".$field." = '$value' "
      	  ."WHERE username = '$username'";

      return mysql_query($q, $this->connection);
   }

   /**
    * getUserInfo - Returns the result array from a mysql
    * query asking for all information stored regarding
    * the given username. If query fails, NULL is returned.
    * Phil: changed for v0.2 db
    */
   function getUserInfo($username)
   {
/*      $q = "SELECT * FROM ".TBL_USERS." WHERE username = '$username'";*/
      $q = "SELECT * FROM ".TBL_USERS." JOIN ".TBL_KNOWN_USER." ON id = user_id WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);

      /* Error occurred: return given name by default */
      if (!$result || (mysql_numrows($result) < 1)) {
         return NULL;
      }

      /* Return result array */
      $dbarray = mysql_fetch_array($result);

      return $dbarray;
   }

   /**
    * getNumMembers - Returns the number of signed-up users
    * of the website, banned members not included. The first
    * time the function is called on page load, the database
    * is queried, on subsequent calls, the stored result
    * is returned. This is to improve efficiency, effectively
    * not querying the database when no call is made.
    * Phil: changed for v0.2 db
    */
   function getNumMembers()
   {
      if ($this->num_members < 0)
      {
         /*$q = "SELECT * FROM ".TBL_USERS;*/
         $q = "SELECT * FROM ".TBL_KNOWN_USER." AS ku LEFT JOIN ".TBL_BANNED_USERS." AS bu ".
         	  "ON ku.user_id = bu.banned_id WHERE bu.banned_id IS NULL";
         $result = mysql_query($q, $this->connection) or die(mysql_error());	/*Phil: error handling */
         $this->num_members = mysql_numrows($result);
      }
      return $this->num_members;
   }

   /**
    * calcNumActiveUsers - Finds out how many active users
    * are viewing site and sets class variable accordingly.
    * Phil: no change for v0.2 db
    */
   function calcNumActiveUsers()
   {
      /* Calculate number of users at site */
      $q = "SELECT * FROM ".TBL_ACTIVE_USERS;
      $result = mysql_query($q, $this->connection) or die(mysql_error());	/*Phil: error handling */
      $this->num_active_users = mysql_numrows($result);
   }

   /**
    * calcNumActiveGuests - Finds out how many active guests
    * are viewing site and sets class variable accordingly.
    * Phil: no change for v0.2 db
    */
   function calcNumActiveGuests()
   {
      /* Calculate number of guests at site */
      $q = "SELECT * FROM ".TBL_ACTIVE_GUESTS;
      $result = mysql_query($q, $this->connection) or die(mysql_error());	/*Phil: error handling */
      $this->num_active_guests = mysql_numrows($result);
   }

   /**
    * addActiveUser - Updates username's last active timestamp
    * in the database, and also adds him to the table of
    * active users, or updates timestamp if already there.
    * Phil: changed for v0.2 db
    */
   function addActiveUser($username, $time)
   {
/*      $q = "UPDATE ".TBL_USERS." SET timestamp = '$time' WHERE username = '$username'";*/
      $q = "SELECT user_id FROM ".TBL_KNOWN_USER." WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);
      if ($result && (mysql_num_rows($result) > 0))
      {
      	$dbarray = mysql_fetch_array($result);
      	$id = $dbarray['user_id'];

      	if (!TRACK_VISITORS) return;

        $q = "REPLACE INTO ".TBL_ACTIVE_USERS." VALUES ('$id', '$time')";
        mysql_query($q, $this->connection) or die(mysql_error());
        $this->calcNumActiveUsers();
      }
   }

   /* addActiveGuest - Adds guest to active guests table
    * Phil: updated for v0.2 db
    * Phil: update for tracking
    */
   function addActiveGuest($ip, $time)
   {
      if (!TRACK_VISITORS) return;

      /* Calculate number of users at site */
      $q = "SELECT * FROM ".TBL_ACTIVE_GUESTS." WHERE ip='$ip'";
      $result = mysql_query($q, $this->connection) or die(mysql_error());
      $rowsreturned = mysql_num_rows($result);

      if ($rowsreturned == 0)
      {
	      $q = "INSERT INTO ".TBL_USERS." (userid, userlevel, timestamp) values ('guest', ".GUEST_LEVEL.", '$time')";
    	  mysql_query($q, $this->connection) or die(mysql_error());
	      $q = "INSERT INTO ".TBL_ACTIVE_GUESTS." (guest_id, ip, timestamp) VALUES (LAST_INSERT_ID(), '$ip', '$time')";
	      mysql_query($q, $this->connection) or die(mysql_error());

      } else {

	      $q = "UPDATE ".TBL_ACTIVE_GUESTS." SET timestamp='$time' WHERE ip='$ip'";
	      mysql_query($q, $this->connection) or die(mysql_error());
      }

      $this->calcNumActiveGuests();
   }

   /* These functions are self explanatory, no need for comments */

   /* removeActiveUser
    * Phil: changed for v0.2 db
    */
   function removeActiveUser($username)
   {
      if (!TRACK_VISITORS) return;

      $q = "SELECT user_id FROM ".TBL_KNOWN_USER." WHERE username = '$username'";
      $result = mysql_query($q, $this->connection);
      if ($result && (mysql_num_rows($result) > 0))
      {
      	$dbarray = mysql_fetch_array($result);
      	$id = $dbarray['user_id'];

/*      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE username = '$username'";*/
        $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE active_id = $id";
        mysql_query($q, $this->connection) or die(mysql_error());
        $this->calcNumActiveUsers();
      }
   }

   /* removeActiveGuest
    * Phil: no change for v0.2 db; note that the user table will grow with unidentified guest users.
    */
   function removeActiveGuest($ip)
   {
      if (!TRACK_VISITORS) return;

      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE ip = '$ip'";
      mysql_query($q, $this->connection);
      $this->calcNumActiveGuests();
   }

   /* removeInactiveUsers *
    * Phil: no change for v0.2 db
    */
   function removeInactiveUsers()
   {
      if (!TRACK_VISITORS) return;

      $timeout = time() - USER_TIMEOUT * 60;

      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE timestamp < $timeout";
      mysql_query($q, $this->connection) or die(mysql_error());
      $this->calcNumActiveUsers();
   }

   /* removeInactiveGuests
    * Phil: no change for v0.2 db; as in removeActiveGuest() will leave unidentifiable user records in place.
    */
   function removeInactiveGuests()
   {
      if (!TRACK_VISITORS) return;

      $timeout = time() - GUEST_TIMEOUT * 60;
      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE timestamp < $timeout";
      mysql_query($q, $this->connection) or die(mysql_error());
      $this->calcNumActiveGuests();
   }

   /**
    * query - Performs the given query on the database and
    * returns the result, which may be false, true or a
    * resource identifier.
    */
   function query($query){
      return mysql_query($query, $this->connection);
   }
};

/* Create database connection */
$database = new MySQLDB;

?>