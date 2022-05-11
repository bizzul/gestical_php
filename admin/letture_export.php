<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }


// columns
$value = "Data;Nnumber route;Number estate;Description estate;Tipo;ContatoreID;Lettura;Errore;\n";

// where
$date = iw_data( $con, 'letture2', 'data', "1=1 ORDER BY data DESC"); // ultima data letture

// grid
$sql = "SELECT DATE_FORMAT( data, '%d/%m/%Y' ) AS data_f, tipo, contatore, lettura, lettura2, errore, c.number_route, c.description_estate, c.number_estate FROM letture2 AS t LEFT JOIN condomini AS c ON ( t.contatore = c.number_meter OR ( t.meterid != '' AND t.meterid != 0 AND t.meterid = c.number_meter ) ) WHERE data LIKE '$date' ORDER BY data ASC";
$result = mysqli_query( $con, $sql ) or die( mysqli_error($con) );
if( mysqli_num_rows( $result ) > 0 ) { 

	while( $line = mysqli_fetch_assoc( $result ) ) {
		
		if( $line['tipo'] == 'Heat' ) { $line['lettura'] = $line['lettura'] * 0.00000100; $line['lettura2'] = $line['lettura2'] * 0.00000100; } // wattora to megawatt 
		
		$value .= "$line[data_f];";
		$value .= "$line[number_route];";
		$value .= "$line[number_estate];";
		$value .= "$line[description_estate];";
		$value .= "$line[tipo];";
		$value .= "$line[contatore];";
		$value .= "$line[lettura];";
		$value .= "$line[errore];";
		$value .= "\n";
	}
}

mysqli_free_result( $result );

// download CSV
header("Content-Type: application/text");
header("Content-Disposition: attachment; filename=letture.csv");
print $value;

?>