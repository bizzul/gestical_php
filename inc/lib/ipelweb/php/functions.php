<?php

function iw_rand ( $lenght = 6, $numeric = 0 ) {

	$chars = !$numeric ? 'abcdefghijklmnopqrstuvwxyz0123456789' : '0123456789';
	
	$string = substr( str_shuffle( $chars . $chars . $chars ), 0, $lenght );
	
	if( substr( $string, 0, 1 ) == '0' ) { return iw_rand( $lenght, $numeric ); }
	
	return $string;
}

function iw_echo ( $string, $echo = 0, $html = 0 ) {
	
	//if( !$string ) { return false; }
	
	if( $html == 0 ) { $string = htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' ); }
		
	if( $echo != 1 ) { return stripslashes( $string ); } else { echo( stripslashes( $string ) ); }
}

function iw_num( $number, $decimal = 2, $currency = '€' ) {

	$number = empty( $number ) ? 0 : str_replace( "'", '', $number );

	if( !ctype_digit( str_replace( array( '.', '-' ), '', $number ) ) ) { return $number; } // check
	
	$number = number_format( $number, $decimal, '.', ( empty( $currency ) ? '' : "'" ) );
	
	if( !empty( $currency )  ) { $number = "$number $currency"; }
	
	return $number;
}

function iw_date( $date, $format = 'd.m.Y' ) { // convert or check?
	
	return date( $format, strtotime( $date ) );
}









/*
// classe meta.php
// classe custom.php o core.php
function send () {}
function followup () {}
function signup () {}
function stats ( $type, $ip ) {}
*/








/*

function iw_modsec ( $data ) {
	
	$from = array( '_ht_', '_hts_', '_js_', '_js2_', '_ifr_', '_ifr2_', '_dw_', '_tag_', '_tag2_', '_etag_', '_etag2_' ); 
	
	$to = array( 'http://', 'https://', '<script', '</script>', '<iframe', '</iframe>', 'document.write', '<', '>', '&lt;', '&gt;' );
	
	if( !is_array( $data ) ) { $data = array( $data ); }
	
	foreach( $data as $key => $value ) {
		
		if( is_array( $value ) ) { $data[ $key ] = iw_modsec( $value ); }
		
		else { $data[ $key ] = str_replace( $from, $to, $value ); }
	}
	
	return count( $data ) > 1 ? $data : reset( $data );
}*/




function iw_meta_read ( $con, $table, $user, $name = NULL ) {
	
	if( $name != '' ) { $name = "name = '$name' AND"; }
	
	$limit = $name == '' ? '' : "LIMIT 1";
	
	$res = iw_query( $con, "SELECT name, value FROM $table WHERE $name rel = '$user' $limit" );

	if( mysqli_num_rows( $res ) == 0 ) { return false; }

	while( $row = mysqli_fetch_assoc( $res ) ) { 
		
		if( $name != '' ) { return $row[ 'value' ]; }
		
		$value[ $row[ 'name' ] ] = $row[ 'value' ];
	}
	
	mysqli_free_result( $res );
	
	return $value;
}

function afs_meta_delete ( $afs_name, $afs_type, $afs_rel = 0 ) {
	
	global $afs_con, $afs_table;
	
	if( !ctype_alnum( str_replace( '_', '', $afs_name ) ) || !in_array( $afs_type, array( 'a', 'b', 'c', 'w' ) ) || !ctype_alnum( "$afs_rel" ) ) { return false; }
	
	$afs_name = $afs_name == '_all_' ? '' : "name = '$afs_name' AND";
	
	$afs_limit = $afs_name == '' ? '' : "LIMIT 1";
	
	$afs_sql = "DELETE FROM $afs_table[meta] WHERE $afs_name type = '$afs_type' AND rel = '$afs_rel' $afs_limit";
	
	mysqli_query( $afs_con, $afs_sql ) or die( 'afs-error-meta-2' );

	if( mysqli_affected_rows( $afs_con ) != 1 ) { return false; }
	
	return mysqli_affected_rows( $con );
}

function afs_meta_save ( $afs_name, $afs_value, $afs_type, $afs_rel = 0 ) {
	
	global $afs_con, $afs_table;
	
	if( !ctype_alnum( str_replace( '_', '', $afs_name ) ) || !in_array( $afs_type, array( 'a', 'b', 'c', 'w' ) ) || !ctype_alnum( "$afs_rel" ) ) { return false; }
	
	$afs_sql = "SELECT id FROM $afs_table[meta] WHERE name = '$afs_name' AND type = '$afs_type' AND rel = '$afs_rel' LIMIT 1";
	
	$afs_res = mysqli_query( $afs_con, $afs_sql ) or die( 'afs-error-meta-3' );

	if( mysqli_num_rows( $afs_res ) == 1 ) {
		
		$afs_id = mysqli_fetch_row( $afs_res );
	
		$afs_sql = "UPDATE $afs_table[meta] SET value = '" . afs_secure( $afs_con, $afs_value ) . "' WHERE id = '$afs_id[0]' LIMIT 1";
	
	} else {
		
		$afs_sql = "INSERT INTO $afs_table[meta] VALUES ( NULL, '$afs_type', '$afs_rel', '$afs_name', '" . afs_secure( $afs_con, $afs_value ) . "' )";
	}
	
	mysqli_query( $afs_con, $afs_sql ) or die( 'afs-error-meta-4' );
	
	if( mysqli_affected_rows( $con ) != 1 ) { return false; }
	
	return true;
}



/*
function iw_table ( $data ) {
	
	$return = '';
	
	foreach( $data as $row => $columns ) {
		
		$return .= "<tr>\n";
		
		foreach( $columns as $key => $value ) {
			
			$return .= "<td>$value</td>\n";
		}
		
		$return.= "</tr>\n";
	}
	
	return $return;
}


function iw_fields ( $con, $table, $id = NULL ) { // con non serve piu

	global $db;//$db=new iw_mysql(); // !!!!!!!!!!
	
	$columns = $db->columns (  $table );
	
	if( $id != '' ) { $value = $db->row(  $table, $id ); }
	
	$return = '';
	
	foreach( $columns as $column => $type ) {
		
		$content = iw_echo( $id != '' ? $value[ $column ] : @$_POST[ $column ], 0 );
		
		$return .= "<label for='$column'>$column</label><br>";
		
		if( strpos( $type, 'text' ) !== false ) { $return .= "<textarea id='$column' name='$column'>$content</textarea><br><br>"; }
		
		else { $return .= "<input type='text' id='$column' name='$column' value='$content'><br><br>"; }
	}

	return $return;
}
*/

?>