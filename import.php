<?php

define( 'IW_NOSESSION', 1 );
define( 'IW_NOCACHE', 1 );

require( 'config.php' );
require( 'inc/php/global.php' );

// protect cronjob
if( !isset( $_GET[ 'key' ] ) || $_GET[ 'key' ] != 'h3FtD5x' ){ die( 'Errore: chiave non valida' ); }

// loop files
$files = glob('upload/*.{xml}', GLOB_BRACE|GLOB_NOSORT );
array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files); // sort by date
foreach($files as $file) {
	
	// limit loop
	if( $stg['max_files'] == 0 ){ break; }
	$stg['max_files']--;
	
	// log file
	$file_log = substr( $file, 0, -4) . '.log';
	
	// import file
	if( !xml_import1( $file, $file_log ) ) { die( "Errore: importazione del file $file" ); }
	
	// archive file
	rename($file, 'archive/'.str_replace( 'upload/', '', $file ) );
	rename($file_log, 'archive/'.str_replace( 'upload/', '', $file_log ) );
}

function xml_import1( $file, $file_log ) {
	
	global $con;

	if( !file_exists($file)) { return false; }
	
	$filename = str_replace(array('upload/', '.xml'), '', $file );	
	$count = 0;
	
	// import time (from log) // SYSTIME=21/04/07,16:45:25
	foreach(file($file_log) as $line) {
		if( strpos( $line, 'SYSTIME' ) !== false ) { 
			$data['date'] = str_replace( array( 'SYSTIME=', ',', '/' ), array( '20', ' ', '-' ), $line ); 
			$data2['date'] = mysqli_real_escape_string( $con, $data['date'] );
		}
	}

	// for each row
	$content = utf8_encode(file_get_contents($file));
	$xml = simplexml_load_string($content);
	foreach( $xml->MeterData as $row ) {

		// dati fissi (prime 5 righe)
		$data2['frame'] = mysqli_real_escape_string( $con, (string)$row->Data[0]->{'value'} );
		$data2['manufacturer'] = mysqli_real_escape_string( $con, (string)$row->Data[1]->{'value'} ); // serve?
		$data2['id'] = mysqli_real_escape_string( $con, (string)$row->Data[2]->{'value'} );
		$data2['version'] = mysqli_real_escape_string( $con, (string)$row->Data[3]->{'value'} );
		$data2['type'] = mysqli_real_escape_string( $con, (string)$row->Data[4]->{'value'} );
		
		// optional
		$data['meterid'] = '';
		$data['status'] = '';
		$data['acccessnr'] = '';
		$data['lettura'] = '';
		$data['data1'] = '';
		$data['lettura2'] = '';
		$data['data2'] = '';
		$data['data3'] = '';
		$data['errore'] = '';

		// per ogni riga (x leggere dati in base a attr)
		echo "<br><b>Riga $count: " . $row->Data->count() . ' dati:</b><br>';
		$tmpcount = 0;
		foreach( $row->Data as $row2 ) {

			echo $tmpcount . ' '. $row2['attr'] . '->' .$row2->{'value'} .'<br>';
			$tmpcount++;
			//print_r(  $row2 ); 
			//$data['valore'] = (float)@$row->Data[14]->{'value'};

			// tutti
			if( $row2['attr'] == 'Meter ID' ) { $data['meterid'] = $row2->{'value'}; }
			if( $row2['attr'] == 'Status' ) { $data['status'] = $row2->{'value'}; }
			if( $row2['attr'] == 'Access Number' ) { $data['acccessnr'] = $row2->{'value'}; } // serve?

			// ripartitore (HCA)
			if( $data2['type'] == 'HCA' ) {
				if( $row2['attr'] == 'IV,0,0,0,,Date/Time' ) { $data['data1'] = $row2->{'value'}; } // riga 9
				if( $row2['attr'] == 'IV,0,0,0,,Units HCA' ) { $data['lettura'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 10
				if( $row2['attr'] == 'IV,1,0,0,,Date' ) { $data['data3'] = $row2->{'value'}; } // riga 11
				if( $row2['attr'] == 'ErrorV,0,0,0,,ManufacturerSpecific' ) { $data['errore'] = $row2->{'value'}; } // riga 44
				if( $row2['attr'] == 'ErrorV,0,0,0,,Date/Time' ) { $data['data2'] = $row2->{'value'}; } // riga 45
		
			// doppio (cont caldo freddo)
			} elseif( $data2['type'] == 'Heat/CoolM' ) {
				if( $row2['attr'] == 'IV,0,0,0,,Date/Time' ) { $data['data1'] = $row2->{'value'}; } // riga 9
				if( $row2['attr'] == 'IV,0,0,0,Wh,E' ) { $data['lettura'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 10
				if( $row2['attr'] == 'IV,0,0,0,,ErrorFlags(binary)(deviceType specific)' ) { $data['errore'] = $row2->{'value'}; } // riga 11
				if( $row2['attr'] == 'IV,0,0,0,m^3,Vol' ) { $data['data3'] = $row2->{'value'}; } // riga 12
				if( $row2['attr'] == 'IV,1,0,0,Wh,E' ) { $data['lettura2'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 13
			
			// heat
			} elseif( $data2['type'] == 'Heat' ) {
				if( $row2['attr'] == 'IV,0,0,0,,Date/Time' ) { $data['data1'] = $row2->{'value'}; } // riga 9
				if( $row2['attr'] == 'IV,0,0,0,Wh,E' ) { $data['lettura'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 10
				if( $row2['attr'] == 'IV,0,0,0,m^3,Vol' ) { $data['data3'] = $row2->{'value'}; } // riga 11
				if( $row2['attr'] == 'IV,0,0,0,,ErrorFlags(binary)(deviceType specific' ) { $data['errore'] = $row2->{'value'}; } // riga 12
				if( $row2['attr'] == 'IV,1,0,0,,Date' ) { $data['data2'] = $row2->{'value'}; } // riga 13
				if( $row2['attr'] == 'IV,1,0,0,Wh,E' ) { $data['lettura2'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 14
				
			// warmwater e coldwater
			} else {
				if( $row2['attr'] == 'IV,1,0,0,,Date' ) { $data['data2'] = $row2->{'value'}; } // riga 13
				if( $row2['attr'] == 'IV,1,0,0,m^3,Vol' ) { $data['lettura2'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 14
				if( $row2['attr'] == 'IV,0,0,0,,ErrorFlags(binary)(deviceType specific)' ) { $data['errore'] = $row2->{'value'}; } // riga 15
				if( $row2['attr'] == 'IV,0,0,0,,Date/Time' ) { $data['data1'] = $row2->{'value'}; } // riga 31
				if( $row2['attr'] == 'IV,0,0,0,m^3,Vol' ) { $data['lettura'] = (float)str_replace( ',', '.', $row2->{'value'} ); } // riga 32
			}
		}

		// secure data
		foreach( $data as $key => $val ) {
			$data2[ $key ] = mysqli_real_escape_string( $con, $val );
		}
		
		// contatore vuoto > se non c'e chiave TPL-Config è vuoto
		if( $row->Data->count() == 5 || !isset( $row->Data[5]['attr'] ) ) {
			
			$data2['errore'] = 'vuoto';
			
			// controllo esiste (oppure cancella e aggiorna?)
			$sql = "SELECT id FROM vuoti WHERE data = '$data2[date]' AND contatore = '$data2[id]' AND versione = '$data2[version]' LIMIT 1";
			$res = mysqli_query( $con, $sql ) or die ( mysqli_error($con));
			if( mysqli_num_rows( $res ) == 0 ) {
				
				// inserisci 
				$sql = "INSERT INTO vuoti ( data, contatore, tipo, frame, produttore, versione, file ) VALUES 
						( '$data2[date]', '$data2[id]', '$data2[type]', '$data2[frame]', '$data2[manufacturer]', '$data2[version]', '$filename' )";
				mysqli_query( $con, $sql ) or die ( mysqli_error($con));
			}
		} 

		// controllo esiste (oppure canella e aggiorna?)
		$sql = "SELECT id FROM letture2 WHERE data = '$data2[date]' AND contatore = '$data2[id]' AND versione = '$data2[version]' LIMIT 1";
		$res = mysqli_query( $con, $sql ) or die ( mysqli_error($con));
		if( mysqli_num_rows( $res ) == 0 ) {

			// inserisci 
			$sql = "INSERT INTO letture2 ( data, contatore, tipo, produttore, versione, acccessnr, status, errore, meterid, lettura, lettura2, data1, data2, data3, file, importato ) VALUES 
					( '$data2[date]', '$data2[id]', '$data2[type]', '$data2[manufacturer]', '$data2[version]', '$data2[acccessnr]', '$data2[status]', '$data2[errore]',  '$data2[meterid]',  '$data2[lettura]',  '$data2[lettura2]',  '$data2[data1]',  '$data2[data2]',  '$data2[data3]',  '$filename', '".IW_NOW."' )";
			mysqli_query( $con, $sql ) or die ( mysqli_error($con));
		}
		
		$count++;
	}
	
	echo '<br>--------------------<br>';
	
	return $count;
}

// TODO delete old files from archive (after 1 year)

?>