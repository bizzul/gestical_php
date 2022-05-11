<?php

function iw_search_where ( $con, $column, $search, $operator = '=', $type = '', $string = 0 ) {
		
	$value = $string == 1 ? $search : ( isset( $_REQUEST[ $search ] ) ? $_REQUEST[ $search ] : '' );

	$return = ''; $hidden = array();
	
	if( $value != '' ) { 

		$return .= " AND ( $column $operator '" . iw_secure( $con, $value ) . "' ";

		if( stripos( $type, 'caps' ) !== false ) { $return .= " OR LOWER( $column ) $operator '" . iw_secure( $con, $value ) . "'"; }

		if( stripos( $type, 'aton' ) !== false ) { $return .= " OR INET_NTOA( $column ) $operator '" . iw_secure( $con, $value ) . "'"; }

		$return .= " ) ";

		//if( $string != 1 ) { $hidden[] = $search; } 
	}
	
	return $return;
}

function iw_search_between( $con, $column, $from, $to, $string = 0 ){

	$from_f = $string == 1 ? $from : ( isset( $_REQUEST[ $from ] ) ? $_REQUEST[ $from ] : '' );

	$to_f = $string == 1 ? $to : ( isset( $_REQUEST[ $to ] ) ? $_REQUEST[ $to ] : '' );

	$return = ''; $hidden = array();
	
	if( !empty( $from_f ) && !empty( $to_f ) ) { // iw_check_date( $from ) 

		$return .= " AND $column >= '" . iw_secure( $con, $from_f ) . " 00:00:00' AND $column <= '$to_f 23:59:59' ";
		//$to_f = date( 'Y-m-d', strtotime( '+1 day', strtotime( iw_secure( $con, $to_f ) ) ) );
		//$return .= " AND ( $column BETWEEN ( '" . iw_secure( $con, $from_f ) . "' ) AND ( '$to_f' ) ) ";
		
		//if( $string != 1 ) { $hidden[] = $from; $hidden[] = $to; }
	}
	
	return $return;
}

function iw_search_like( $con, $columns, $search, $type = '', $string = 0 ){

	$value = $string == 1 ? $search : ( isset( $_REQUEST[ $search ] ) ? $_REQUEST[ $search ] : '' );

	if( !is_array( $columns ) ) { $columns = array_keys( iw_columns( $con, $columns ) ); } // $columns = $table

	if( $value != '' && !empty( $columns ) ) {

		foreach( $columns as $column ) {

			$sql[] = " $column LIKE '%" . iw_secure( $con, $value ) . "%' OR ";

			if( stripos( $type, 'caps' ) !== false ) { $sql[] = " LOWER( $column ) LIKE '%" . iw_secure( $con, $value ) . "%' OR "; }

			if( stripos( $type, 'aton' ) !== false ) { $sql[] = " INET_NTOA( $column ) LIKE '%" . iw_secure( $con, $value ) . "%' OR "; }
		}

		$return = " AND ( " . substr( implode( ' ', $sql ), 0, -3 ) . " ) ";

		//if( $string != 1 ) { $hidden = $search; }
		
		return $return;
	}
}

function iw_search_in( $con, $column, $searches, $in = 1, $string = 0 ) {
				
	$values = $string == 1 ? $searches : ( isset( $_REQUEST[ $searches ] ) ? $_REQUEST[ $searches ] : '' );

	if( !empty( $values ) && is_array( $values ) ) {

		foreach( $values as $value ) { $sql[] = "'" . iw_secure( $con, $value ) . "'";	}

		$return = " AND $column " . ( $in == 1 ? 'IN' : 'NOT IN' ) . " ( " . implode( ',', $sql ) . " ) ";

		//if( $string != 1 ) { $this->fields[] = $searches; }
		
		return $return;
	}
}

function iw_search_null( $con, $column, $field, $string = 0 ){
		
	$value = $string == 1 ? $field : ( isset( $_REQUEST[ $field ] ) ? $_REQUEST[ $field ] : '' );

	$return = '';
	
	if( $value != '' ) {

		$return .= " AND ( $column " . ( $value == '0' ? 'IS NOT NULL' : 'IS NULL' ) . " ) "; // OR col = ''

		//if( $string != 1 ) { $this->fields[] = $field; } 
	}
	
	return $return;
}