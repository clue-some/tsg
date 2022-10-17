<?php
/**
 * tracksuitgene/ss/front.php
 *
 * The main front page.
 *
 * written by Pred MacLinty for Tracksuitgene.
 *
 */

/* Debug:
$_SERVER['REMOTE_ADDR'] = '199.193.245.13';*/
include ("include/session.php");
include ("page.php");

// user agents don't play nice
$agent = get_browser(null, true);
$isIE = $agent['browser'] == 'IE';
$hasJS = $agent['javascript'] == 1;

$pagemode = isset($_GET["mode"]) ? $_GET["mode"] : "front";
switch ($pagemode)
{
	case "login":
    	if (! $session->logged_in)
    	{
    		$page = new LoginPage ($isIE, $hasJS);
    		break;
    	}
    case "code":
    	if (! $session->logged_in)
    	{
	    	$page = new CodePage ($isIE, $hasJS);
	    	break;
    	}
    case "back":
    	if ($session->logged_in)
    	{
	    	$page = new BackPage ($isIE, $hasJS);
	    	break;
    	}
    case "test":
    	if ($pagemode == "test")
    	{
    		$page = new TestPage($isIE);
    		break;
    	}
    case "front":
    default:
	  	$page = new Page ($isIE, $hasJS);
}

$page->doctype ('xhtml', 'svg', '1.1'); /* Phil: sends header b4 first tags - may need to change 4 login */
$page->head (array ('tsg'), array ('inkweb'));
$page->body ($session->username);
?>