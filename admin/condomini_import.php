<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// upload csv
if( isset( $_FILES[ 'file' ] ) ) {

	if( !is_uploaded_file( $_FILES[ 'file' ][ 'tmp_name' ] ) ) { $error = 'Nessun file selezionato'; }
	
	elseif( empty( $_FILES['file']['name'] ) ) { $error = 'Nome del file non valido'; }

	elseif( stripos( $_FILES[ 'file' ][ 'name' ], '.csv' ) === false ) { $error = 'Formato del file non valido (solo file .csv)'; }
	
	elseif( $_FILES[ 'file' ][ 'size' ] > 1048576 ) { $error = 'File troppo grande (massimo 1MB)'; }

	elseif( $_FILES[ 'file' ][ 'error' ] > 0 ) { $error = 'Upload fallito con errore: ' . $_FILES[ 'file' ][ 'error' ]; }
	
	else {
		
		$file_content = $_FILES['file']['tmp_name'];
		
		//$file_array = array_map('str_getcsv', file($file_content));
		$file_array = file($file_content); //parse the rows
		
		foreach( $file_array AS $row_num => $row_string ) {
			
			if( $row_num == '0' ) { continue; } // columns
			
			$row_array = explode( ';', $row_string );
			
			//echo var_dump( $file_array );
			//echo var_dump( $row_array );
			
			$data['import'] = IW_NOW;
			$data['file'] = iw_secure( $con, $_FILES['file']['name'] );
			$data['number_route'] = iw_secure( $con, $row_array[0] );
			$data['description_route'] = iw_secure( $con, $row_array[1] );
			$data['comment_route_bo'] = iw_secure( $con, $row_array[2] );
			$data['comment_route_e'] = iw_secure( $con, $row_array[3] );
			$data['number_estate'] = iw_secure( $con, $row_array[4] );
			$data['description_estate'] = iw_secure( $con, $row_array[5] );
			$data['timeout_estate_minutes'] = iw_secure( $con, $row_array[6] );
			$data['mode_estate'] = iw_secure( $con, $row_array[7] );
			$data['comment_estate_bo'] = iw_secure( $con, $row_array[8] );
			$data['comment_estate_e'] = iw_secure( $con, $row_array[9] );
			$data['number_meter'] = iw_secure( $con, $row_array[10] );
			$data['number_customer'] = iw_secure( $con, $row_array[11] );
			$data['unitnumber'] = iw_secure( $con, $row_array[12] );
			$data['unitdescription'] = iw_secure( $con, $row_array[13] );
			$data['comment_meter_bo'] = iw_secure( $con, $row_array[14] );
			$data['comment_meter_e'] = iw_secure( $con, $row_array[15] );
			$data['mediumcode'] = iw_secure( $con, $row_array[16] );
			$data['aeskey'] = iw_secure( $con, $row_array[17] );
			$data['statusbyte'] = iw_secure( $con, $row_array[18] );
			$data['rssi_value'] = iw_secure( $con, $row_array[19] );
			$data['billing_date_value_0'] = iw_secure( $con, $row_array[20] );

			if( iw_data( $con, 'condomini', 'id', "number_meter = '$row_array[10]'" ) ) { // check exist
				
				if( !iw_update( $con, 'condomini', $data, "number_meter = '$row_array[10]'" ) ) { 
					
					$error = "Aggiornamento riga $row_num (contatore $row_array[10])"; break; }

			} else {
				
				if( !iw_insert ( $con, 'condomini', $data ) ) { 
					
					$error = "Inserimento riga $row_num (contatore $row_array[10])"; break; }
			}
		}
		
		if( !isset( $error ) ) { $success = "File CSV importato correttamente ($row_num righe)"; }
	}
}
		
?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>

	<div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
	<h1 class="h2">Importa file CSV</h1>
	</div>
	
	<?php if( isset( $error ) ) { ?>
	<div class="alert alert-danger my-4 mt-5" role="alert">
	<span data-feather="alert-circle"></span> &nbsp; Errore: <?= $error ?>
	</div>
	<?php } ?>
	
	<?php if( isset( $success ) ) { ?>
	<div class="alert alert-success my-4 mt-5" role="alert">
	<span data-feather="check-circle"></span> &nbsp; <?= $success ?>
	</div>
	<?php } ?>
	
	<?php if( !isset( $error ) && !isset( $success ) ) { ?>
	<div class="alert alert-warning my-4 mt-5" role="alert">
	<em><span data-feather="alert-triangle"></span> &nbsp; I dati importati sovrascrivono quelli esistenti. Il file CSV deve avere le stesse colonne. <a href='../inc/misc/6814014_Stabile_Gestical_Cadempino_.csv' class="alert-link">Esempio file CSV</a></em>
	</div>
	<?php } ?>
	
	<form method="post" action="?" enctype="multipart/form-data" class="mb-4">
	<div class="mb-3">
	  <label for="file" class="form-label">Scegli file CSV:</label>
	  <input class="form-control" type="file" id="file" name="file" accept=".csv">
	</div>
	<button type="submit" class="btn btn-primary btn-lg mt-3 me-2">Importa</button>
	<button type="button" class="btn btn-primary btn-lg mt-3" onClick="location.href='condomini.php';">Annulla</button>
	<input type="hidden" name="csrf" value="<?= $_SESSION['iw_csrf'] ?>">
	</form>
	
<? require ( '../inc/tpl/admin_footer.php' ); ?>	
</body>
</html>