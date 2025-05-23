<?php require_once("includes/session.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php //CLOSE THE SESSION (LOG OUT):
		// Four steps to closing a session
		// (i.e. logging out)
		// 1. Find the session
		session_start();
		// 2. Unset all the session variables
		$_SESSION = array();
		// 3. Destroy the session cookie
		if(isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		// 4. Destroy the session
		session_destroy();
		//Log Out of Facebook
		if(isset($_COOKIE[fbsr_160929550680582])) {
			setcookie(fbsr_160929550680582, '', time()-42000, '/');
		}
		redirect_to("index.php?logout=1");
?>