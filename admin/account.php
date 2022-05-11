<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// account
if( isset( $_POST['email'] ) && isset( $_POST['pw'] ) ) {
	
	$post = iw_secure( $con, $_POST );
	
	if( !empty( $post['email'] ) && !filter_var( $post['email'], FILTER_VALIDATE_EMAIL ) ) { $error = 'Email non valida'; }
	elseif( !empty( $post['email'] ) && $post['email'] != @$post['email2'] ) { $error = 'Le email inserite non corrispondono'; }
	
	if( !empty( $post['pw'] ) && strlen( $post['pw'] ) <= 5 ) { $error = 'La password deve avere almento 6 caratteri'; }
	elseif( !empty( $post['pw'] ) && $post['pw'] != @$post['pw2'] )  { $error = 'Le password inserite non corrispondono'; }
	 
	if( !isset( $error ) ) {
		
		if( !empty( $post['email'] ) && !iw_update( $con, 'iw_admins', array('email'=>$post['email']), $usr['id'] ) ) { $error = "MySQL query failed 1"; }
		if( !empty( $post['pw'] ) && !iw_update( $con, 'iw_admins', array('passkey'=>iw_password($post['pw'])), $usr['id'] ) ) { $error = "MySQL query failed 2"; }
		
		if( !isset( $error ) ) { header( 'location: ?saved' ); exit; }
	}
}

?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>

	<div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
	<h1 class="h2">Modifica account</h1>
	</div>
	
	<?php if( isset( $error ) ) { ?>
	<div class="alert alert-danger my-4 mt-5" role="alert">
	<span data-feather="alert-circle"></span> &nbsp; Errore: <?= $error ?>
	</div>
	<?php } ?>
	
	<?php if( isset( $_GET['saved'] ) ) { ?>
	<div class="alert alert-success my-4 mt-5" role="alert">
	<span data-feather="check-circle"></span> &nbsp; Modifiche salvate
	</div>
	<?php } ?>

	<form method="post" action="?" class="mb-4" >
	<div class="mb-3">
	<label for="name" class="form-label">Nome e cognome:</label>
	<input type="text" class="form-control" id="name" name="name" readonly value="<?= $usr['name'] ?>">
	</div>
	<div class="mb-3">
	<label for="email0" class="form-label">Email attuale:</label>
	<input type="email" class="form-control" id="email0" name="email0" readonly value="<?= $usr['email'] ?>">
	</div>
	<div class="mb-3">
	<label for="email" class="form-label">Modifica Email:</label>
	<input type="email" class="form-control" id="email" name="email" value="">
	</div>
	<div class="mb-3">
	<label for="email2" class="form-label">Conferma nuova email:</label>
	<input type="email" class="form-control" id="email2" name="email2" value="" >
	</div>
	<div class="mb-3">
	<label for="pw" class="form-label">Nuova password:</label>
	<input type="password" class="form-control" id="pw" name="pw" value="">
	</div>
	<div class="mb-3">
	<label for="pw2" class="form-label">Conferma nuova password:</label>
	<input type="password" class="form-control" id="pw2" name="pw2" value="">
	</div>
	<button type="submit" class="btn btn-primary btn-lg mt-3 me-2">Salva</button>
	<button type="button" class="btn btn-primary btn-lg mt-3" onClick="location.href='index.php';">Annulla</button>
	<input type="hidden" name="csrf" value="<?= $_SESSION['iw_csrf'] ?>">
	</form>

<? require ( '../inc/tpl/admin_footer.php' ); ?>	
</body>
</html>