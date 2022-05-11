<?php
require( '../config.php' );
require( '../inc/php/global.php' );



// login

if( isset( $_POST['email'] ) && isset( $_POST['password'] ) ) { 

	$cookie = isset( $_POST['cookie'] ) ? 1 : 0;
	
	if( !iw_login( $con, 'iw_admins', $_POST['email'], $_POST['password'], $cookie, 'login1', 'a' ) ) {
		
		iw_login_attempt( $con, 'iw_attempts', 'a', $_POST['email'], $_POST['password'] );
		
		$error = "Dati d'accesso errati";
		
	} else { header( 'location: index.php' ); exit;	}
}

// logout

if( isset( $_GET[ 'logout' ] ) ) {
	
	iw_logout( $con, 'iw_admins', 'login1', 'a' );

	header( 'location: ?loggedout' ); exit;
}

if( isset( $_GET[ 'loggedout'] ) ) { $success = 'Logout effettuato'; }

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="IpelWeb, OBMD">
<title>Gestical Login</title>
<link href="../inc/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
<link href="../inc/css/login.css" rel="stylesheet">
</head>
<body>

<?php
if( isset( $error ) ) { echo "<div class='alert alert-danger fixed-top text-center' role='alert'>$error</div>"; }	
if( isset( $success ) ) { echo "<div class='alert alert-success fixed-top  text-center' role='alert'>$success</div>"; }	
?>
	
<div class="wrapper fadeInDown">
  <div id="formContent">

    <!-- Icon -->
    <div class="fadeIn first  mt-4 mb-3">
      <img class="logo" src="../inc/img/logo_gestical.png" id="icon" alt="Gestical Logo" class="img-fluid" />
    </div>

    <!-- Login Form -->
	<form method="post" action="?">
		<input class="fadeIn second" type="email" name="email" required placeholder="Email">
		<input class="fadeIn third" type="password" name="password" required placeholder="Password">
		<div class="fadeIn fourth mt-1 mb-3 text-muted"><label><input type="checkbox" name="cookie">&nbsp; Ricordami</label></div>
		<input class="fadeIn fourth" type="submit" value="Log in" >
		<input type="hidden" name='csrf' value="<?= $_SESSION['iw_csrf'] ?>">
	</form>

    <!-- Remind Passowrd -->
    <div id="formFooter">
    </div>

  </div>
</div>
	
</body>
</html>