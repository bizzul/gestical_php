<?php

function iw_query ( $con, $query ) {
	
	if( !$res = mysqli_query( $con, $query ) ) {
		
		die( 'MySQL error: ' . mysqli_error( $con ) );
	}
		
	return $res;
}

function iw_tables ( $con ) { 

	$res = iw_query( $con, "SHOW TABLE STATUS" );
	
	while( $row = mysqli_fetch_assoc( $res ) ) { $value[ $row[ 'Name' ] ] = $row[ 'Rows' ]; }
	
	mysqli_free_result( $res );
	
	return $value;
}

function iw_columns ( $con, $table ) {
	
	$res = iw_query( $con, "DESCRIBE $table" );
	
	while( $row = mysqli_fetch_assoc( $res ) ) { $value[ $row[ 'Field' ] ] = $row[ 'Type' ]; }
	
	mysqli_free_result( $res );
	
	return $value;
}

function iw_select ( $con, $query ) {
	
	$res = iw_query( $con, $query );
	
	if( mysqli_num_rows( $res ) == 0 ) { return array(); }
		
	while( $row = mysqli_fetch_assoc( $res ) ) { $value[] = $row; }
	
	mysqli_free_result( $res );
	
	return $value;
}

function iw_data ( $con, $table, $column, $where, $join = '' ) {
	
	if( strpos( $where, '=' ) === false ) { $where = "id = '$where'"; }

	$res = iw_query( $con, "SELECT t.$column FROM $table AS t $join WHERE $where LIMIT 1" );

	if( mysqli_num_rows( $res ) == 0 ) { return false; }
	
	$row = mysqli_fetch_row( $res );
	
	return $row[0];
}

function iw_row ( $con, $table, $where, $join = '' ) {
	
	if( strpos( $where, '=' ) === false ) { $where = "id = '$where'"; }
	
	$res = iw_query( $con, "SELECT t.* FROM `$table` AS t $join WHERE $where LIMIT 1" );

	if( mysqli_num_rows( $res ) == 0 ) { return false; }
	
	return mysqli_fetch_assoc( $res );	
}

function iw_column ( $con, $table, $column, $key ='id', $where = '' ) {

	$res = iw_query( $con, "SELECT t.$key, t.$column FROM $table AS t WHERE 1 $where ORDER BY $column ASC" ); 

	if( mysqli_num_rows( $res ) == 0 ) { return array(); }
		
	while( $row = mysqli_fetch_assoc( $res ) ) { $value[ $row[ $key ] ] = $row[ $column ]; }
	
	mysqli_free_result( $res );
	
	return $value;
}

function iw_distinct ( $con, $table, $column, $where = '' ) {
	
	$res = iw_query( $con, "SELECT DISTINCT t.$column FROM $table AS t WHERE 1 $where ORDER BY $column ASC" );
	
	if( mysqli_num_rows( $res ) == 0 ) { return array(); }
		
	while( $row = mysqli_fetch_row( $res ) ) { $value[] = $row[0]; }
	
	mysqli_free_result( $res );
	
	return $value;
}

function iw_total ( $con, $table, $where = '', $join = '', $distinct = 't.id' ) {
	
	$res = iw_query( $con, "SELECT COUNT( $distinct ) FROM `$table` AS t $join WHERE 1 $where" ); 

	$row = mysqli_fetch_row( $res );
	
	return number_format( $row[0], 0, '.', "" );
}

function iw_sum ( $con, $table, $column, $where = '', $join = '', $float = 2 ) {
	
	$res = iw_query( $con, "SELECT SUM(t.$column) FROM `$table` AS t $join WHERE 1 $where" );

	$row = mysqli_fetch_row( $res );
	
	return number_format( $row[0], $float, '.', "" );
}

function iw_insert ( $con, $table, $data ) {
	
	$columns = implode( '`,`', array_keys( $data ) );

	foreach( $data as $key => $val ) { $data[ $key ] = $val === NULL ? 'NULL' : "'$val'"; }

	iw_query( $con, "INSERT INTO $table ( `$columns` ) VALUES ( " . implode( ',', $data ) . " )" );
	
	return mysqli_affected_rows( $con );
}

function iw_update ( $con, $table, $data, $where, $limit = 1  ) {
	
	if( $limit != 0 ) { $limit = "LIMIT $limit"; }
	
	if( strpos( $where, '=' ) === false ) { $where = "id = '$where'"; }
	
	foreach( $data as $key => $val ) { $sql[] = "$key = " . ( $val === NULL ? 'NULL' : ( $val == '++' ? "$key+1" : "'$val'" ) ); }

	iw_query( $con, "UPDATE $table SET " . implode( ',', $sql ) . " WHERE $where $limit" );
	
	return mysqli_affected_rows( $con );
}

function iw_delete ( $con, $table, $where, $limit = 1 ) {
	
	if( $limit != 0 ) { $limit = "LIMIT $limit"; }
	
	if( strpos( $where, '=' ) === false ) { $where = "id = '$where'"; }
	
	iw_query( $con, "DELETE FROM $table WHERE $where $limit" );
	
	return mysqli_affected_rows( $con );
}

function iw_secure ( $con, $data, $html = 0, $force = 0, $array = 0 ) {

	if( !is_array( $data ) ) { $data = array( $data ); }
	
	foreach( $data as $key => $value ) {
		
		if( is_array( $value ) ) { $data[ $key ] = iw_secure( $con, $value, $html, $force ); }
		
		else {
	
			if( $html == 0 ) { $data[ $key ] = strip_tags( $value ); }

			if( $force == 0 && @get_magic_quotes_gpc() ) { $data[ $key ] = trim( $value ); }

			else { $data[ $key ] = mysqli_real_escape_string( $con, trim( $value ) ); }
		}
	}
	
	//print_r( $data );

	return count( $data, 1 ) > 1 || $array ? $data : reset( $data );
}

function iw_unique ( $con, $table, $column = 'id', $lenght = 6, $numeric = 0, $loop = 10 ) {
	
	if( $loop == 0 ) { die( 'Error invalid unique_id' ); }
	
	$id = iw_rand( $lenght, $numeric );
	
	$res = iw_query( $con, "SELECT $column FROM $table WHERE id = '$id' LIMIT 1" );

	if( mysqli_num_rows( $res ) == 1 ) { return iw_unique( $con, $table, $column, $lenght, $numeric, $loop - 1 ); }

	return $id;
}

