<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'phpids' ) );

dvwaDatabaseConnect();
	
if( isset( $_POST[ 'Register' ] ) ) {
	// Anti-CSRF
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}

	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'login.php' );

	$username = $_POST[ 'username' ];
	$user = stripslashes( $username );
	$user = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

	$password = $_POST[ 'password' ];
	$confirmpassword = $_POST[ 'confirmpassword' ];
	$pass = stripslashes( $password );
	$pass = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
	$pass = md5( $pass );
	
	$query = ("SELECT table_schema, table_name, create_time
				FROM information_schema.tables
				WHERE table_schema='{$_DVWA['db_database']}' AND table_name='users'
				LIMIT 1");
	$result = @mysqli_query($GLOBALS["___mysqli_ston"],  $query );
	if( mysqli_num_rows( $result ) != 1 ) {
		dvwaMessagePush( "First time using DVWA.<br />Need to run 'setup.php'." );
		dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'setup.php' );
	}
	$user = $_POST[ 'username' ];
	$firstname = $_POST[ 'first' ];
	$lastname = $_POST[ 'last' ];
	if ($password != $confirmpassword) {
		echo "Passwords do not match, please try again!";
	} else {
		$user_check_query = "SELECT * FROM dvwa.users WHERE user='$user' LIMIT 1";
		$usercheck = @mysqli_query($GLOBALS["___mysqli_ston"],  $user_check_query );
		$user_verify = mysqli_fetch_assoc($usercheck);
		if ($user_verify['user'] === $username) {
      			echo "<script>alert('Username already exists, please use another name.')</script>";
    		} else {
			$userquery = ("SELECT * FROM dvwa.users;");
			$userresult = @mysqli_query($GLOBALS["___mysqli_ston"],  $userquery );
			$count = mysqli_num_rows( $userresult ) + 1;
			$query  = "INSERT INTO dvwa.users (user_id, first_name, last_name, user, password, avatar, failed_login) values ('$count', '$firstname', '$lastname', '$user', '$pass', '/hackable/users/admin.jpg', '0');";
			$result = @mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '.<br />Try <a href="setup.php">installing again</a>.</pre>' );
			if( $result && mysqli_num_rows( $result ) == 1 ) {    // Login Successful...
				dvwaMessagePush( "You have logged in as '{$user}'" );
				dvwaLogin( $user );
				dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'index.php' );
			dvwaMessagePush( 'Successfully created user, please login now.' );
			dvwaRedirect( 'login.php' );
			}
		}
	}
	
}

$messagesHtml = messagesPopAllToHtml();

Header( 'Cache-Control: no-cache, must-revalidate');    // HTTP/1.1
Header( 'Content-Type: text/html;charset=utf-8' );      // TODO- proper XHTML headers...
Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );     // Date in the past

// Anti-CSRF
generateSessionToken();

echo "<!DOCTYPE html>
<html lang=\"en-GB\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
		<title>Login :: Damn Vulnerable Web Application (DVWA) v" . dvwaVersionGet() . "</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/login.css\" />
	</head>
	<body>
	<div id=\"wrapper\">
	<div id=\"header\">
	<br />
	<p><img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/login_logo.png\" /></p>
	<br />
	</div> <!--<div id=\"header\">-->
	<div id=\"content\">
	<form action=\"register.php\" method=\"post\">
	<fieldset>
			<label for=\"user\">New Username</label> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"username\"><br />
			<label for=\"firstname\">First Name</label> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"first\"><br />
			<label for=\"lastname\">Last Name</label> <input type=\"text\" class=\"loginInput\" size=\"20\" name=\"last\"><br />
			<label for=\"pass\">New Password</label> <input type=\"password\" class=\"loginInput\" AUTOCOMPLETE=\"off\" size=\"20\" name=\"password\"><br />
      			<label for=\"confirmpass\">Confirm Password</label> <input type=\"password\" class=\"loginInput\" AUTOCOMPLETE=\"off\" size=\"20\" name=\"confirmpassword\"><br />
			<br />
			<p class=\"submit\"><input type=\"submit\" value=\"Register\" name=\"Register\"></p>
			
			<a href=\"login.php\">Already Registered? Login Here</a>
	</fieldset>
	" . tokenField() . "
	</form>
	<br />
	{$messagesHtml}
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<!-- <img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/RandomStorm.png\" /> -->
	</div > <!--<div id=\"content\">-->
	<div id=\"footer\">
	<p>" . dvwaExternalLinkUrlGet( 'https://github.com/digininja/DVWA/', 'Damn Vulnerable Web Application (DVWA)' ) . "</p>
	</div> <!--<div id=\"footer\"> -->
	</div> <!--<div id=\"wrapper\"> -->
	</body>
</html>";

?>
