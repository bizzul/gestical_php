<?php

define( 'IW_GRID_METHOD', 'POST' );
define( 'IW_GRID_CSRF', '1' );
define( 'IW_GRID_SORTICON', array( 'asc' => ' <span>&#9651;</span>', 'desc' => ' <span>&#9661;</span>', 'asc_sel' => ' <span>&#9650;</span>', 'desc_sel' => ' <span>&#9660;</span>' ) );

function iw_grid_sql ( $con, $sql, $prefix = '' ) {
	
	if( stripos( $sql, 'select' ) === false ) { return false; }
	
	if( preg_match('/SELECT (.*?) FROM/i', $sql, $match) ) { $columns = $match[1]; } else { die( 'Error columns'); }
	if( $columns == '*' || $columns == 't.*' ) { $columns = ''; } 
	else { $columns = array_map('trim', explode(',', $columns )); }

	if( preg_match('/FROM (.*?) AS t/i', $sql, $match) ) { $table = $match[1]; } else { 
		if( preg_match('/FROM (.*?) WHERE/i', $sql, $match) ) { $table = $match[1]; } else { die( 'Error table'); }
	}
	
	if( preg_match('/WHERE (.*?) GROUP BY/i', $sql, $match) ) { $where = $match[1]; } else { 
		if( preg_match('/WHERE (.*?) ORDER BY/i', $sql, $match) ) { $where = $match[1]; } else { 
			if( preg_match('/WHERE (.*?) XXX1/i', $sql . 'XXX1', $match) ) { $where = $match[1]; } else { die( 'Error where'); }
		}
	}
	if( $where == '1' ) { $where = ''; }
	if( $where && strtolower( trim( substr( $where, 0, 3 ) ) ) != 'and' ) { $where = " AND $where"; }
	
	if( preg_match('/ORDER BY (.*?) ASC/i', $sql, $match) ) { $column = $match[1]; } else { 
		if( preg_match('/ORDER BY (.*?) DESC/i', $sql, $match) ) { $column = $match[1]; } else { die( 'Error order'); }
	}

	$order = trim( substr( $sql, -4 ) );

	$rel = '';
	
	if( preg_match('/GROUP BY (.*?) ORDER BY/i', $sql, $match) ) { $group = "GROUP BY $match[1]"; } else { 
		if( preg_match('/GROUP BY (.*?) XXX1/i', $sql . 'XXX1', $match) ) { $group = "GROUP BY $match[1]"; } else { $group =''; }
	}

	$rows = 25; // if( preg_match('/LIMIT (.*?) XXX1/i', $sql . 'XXX1', $match) ) { $rows = $match[1]; } else { die( 'Error rows'); }
	//$grid['page'] = 1;
	
	//print_r( $grid );
	return iw_grid_grid( $con, $table, $columns, $column, $order, $rows, $prefix, $where, $rel, $group );
}

function iw_grid_alias( $column ) {

	if( stripos( $column, 'as' ) !== false ) { return trim( substr( $column, ( stripos( $column, 'as' ) + 2 ) ) ); }

	elseif( stripos( $column, '.' ) !== false ) { return trim( substr( $column, ( stripos( $column, '.' ) + 1 ) ) ); }

	else { return $column; }
}

function iw_grid_grid ( $con, $table, $cols, $col, $order = 'asc', $rows = 25, $prefix = '', $where ='', $rel ='', $group = '' ){
	
	$grid = array( 'data' => array(), 'table' => $table, 'prefix' => $prefix, 'where' => $where, 'rel' => $rel, 'group' => $group );

	if( empty( $cols ) ) { $cols = array(); foreach( iw_columns( $con, $table ) as $col_name => $col_type ) { $grid['columns'][$col_name] = $col_name; } }

	else { foreach( $cols as $col_key => $col_name ) { $grid['columns'][ iw_grid_alias( $col_name ) ] = $col_name; } }

	$grid['column'] = isset( $_REQUEST[$prefix.'c'] ) && ctype_alnum( str_replace( array( '.', '_' ), '', $_REQUEST[$prefix.'c'] ) ) ? $_REQUEST[$prefix.'c'] : $col;
	
	$grid['order'] = isset( $_REQUEST[$prefix.'o'] ) && in_array( $_REQUEST[$prefix.'o'], array( 'asc', 'desc' ) ) ? $_REQUEST[$prefix.'o'] : $order;

	$grid['page'] = isset( $_REQUEST[$prefix.'p'] ) && ctype_digit( $_REQUEST[$prefix.'p'] ) && $_REQUEST[$prefix.'p'] > 0 ? $_REQUEST[$prefix.'p'] : 1;
	
	$grid['rows'] = isset( $_REQUEST[$prefix.'r'] ) && ctype_digit( $_REQUEST[$prefix.'r'] ) && $_REQUEST[$prefix.'r'] > 0 ? $_REQUEST[$prefix.'r'] : $rows;

	$sql  = "SELECT " . implode( ',', $grid['columns'] ) . " FROM $grid[table] AS t $rel WHERE 1 $where $group ";
	
	$sql .= "ORDER BY $grid[column] $grid[order] LIMIT " . ( ( $grid['page'] - 1 ) * $grid['rows'] ) . ", $grid[rows]";

	$res = iw_select( $con, $sql ); 
	
	foreach( $res as $row_num => $row_array ) { foreach( $row_array as $data_key => $data_val ) { $grid['data'][ $row_num ][ $data_key ] = iw_echo( $data_val ); } }
	
	return $grid;
}

function iw_grid_getdata() {
	// solo ged grid col order page e rows (vedi qui sopra)
}

function iw_grid_table( $grid, $columns = 1, $manage = 0 ) {

	$return = "<div class='table-responsive'>\n<table class='table table-hover iw-grid-table'>\n";

	if( $grid['columns'] ) {

		$return .= "<thead><tr class='iw-grid-column'>\n";

		if( $manage ) { $return .= "<th scope='col' style='width:40px;text-align:right'><input type='checkbox' name='$grid[prefix]all' id='$grid[prefix]all' value='1' onClick=\"iw_select_all( '$grid[prefix]', this, '" . count( $grid['data'] ) . "' )\"/></th>\n"; }

		foreach( $grid['columns'] as $col => $sql ) {

			if( empty( $grid['show'] ) || array_key_exists( $col, $grid['show'] ) ) { 

				if( $sql == '' ) { $return .= "<th scope='col'>" . iw_echo( $col ) . "</th>\n"; }

				else { $return .= "<th scope='col'>" . iw_grid_column( $grid, $col, $sql, 'asc' )."</th>\n"; }
			}
		}

		$return .= "</tr></thead><tbody>\n";
	}	

	if( empty( $grid['data'] ) ) { $return .= "<tr><td colspan='20' class='iw-grid-empty'>IW_LANG_EMPTY</td></tr>";  }

	else {

		foreach( $grid['data'] as $row => $data ) {

			$return .= "<tr id='$grid[prefix]row_$row'>\n";

			if( $manage ) { $return .= "<td style='width:40px;text-align:right'><input type='checkbox' class='' name='$grid[prefix]id[]' id='$grid[prefix]id_$row' value='{$grid['data'][ $row ][ 'id' ]}' onclick=\"iw_select_row( this, '$grid[prefix]row_$row' )\" /></td>\n"; }

			foreach( $data as $key => $value ) {	

				if( empty( $grid['show'] ) || array_key_exists( $key, $grid['show'] ) ) { $return .= "<td>$value</td>\n"; }	
			}

			$return .= "</tr>\n";
		}
	}

	$return .= "</tbody>\n";

	if( !empty( $grid['summary'] ) && is_array( $grid['summary'] ) ) { $return .= iw_grid_summary( $grid, $manage ); }

	if( $manage ) { $return .= iw_grid_hidden( $grid, 1 ); }

	return $return . "</table>\n</div>\n";
}

function iw_grid_summary( $grid, $manage = 0 ){ // separato? anche columns?
			
	$return  = "<tr class='iw_summary'>\n";

	if( $manage ) { $return .= "<td></td>\n"; }

	foreach( $grid['columns'] as $col => $sql ) {

		if( empty( $grid['show'] ) || in_array( $col, $grid['show'] ) ) {

			if( array_key_exists( $col, $grid['summary'] ) ) {

				$sum = 0;

				foreach( $grid['data'] as $row => $data ) { $sum = $sum + $grid['data'][ $row ][ $col ]; }

				$return .= "<td>" . number_format( $sum, 0, '.', "'" ) . $grid['summary'][ $col ] . "</td>\n";

			} else { $return .= "<td></td>\n"; }
		}
	}

	return $return .= "</tr>\n";
}

function iw_grid_paging( $con, $grid, $rows = 1, $force = 0, $form = '' ) { // form fuori
		
	if( $force != 0 ) { $total = $force; } else { $total = iw_total( $con, $grid[ 'table' ], $grid[ 'where' ], $grid[ 'rel' ] ); }

	echo( "<form id='$grid[prefix]paging' name='$grid[prefix]paging' method='".IW_GRID_METHOD."' action='$form' class='form-inline iw-grid-paging'>\n" ); 

	echo( "<select class='form-contr  ol' name='$grid[prefix]p' id='$grid[prefix]page' onChange=\"document.getElementById('$grid[prefix]paging').submit()\">\n" );

	if( $total == 0 ) { echo "<option value=''>IW_LANG_PAGE 1 IW_LANG_OF 1</option>\n\n"; }

	$pages = ceil( str_replace( "'", '', $total ) / $grid[ 'rows' ] );

	for( $i = 1; $i <= $pages; $i++ ) { echo( "<option value='$i' " . ( $i == $grid[ 'page' ] ? 'selected' : '' ) . ">IW_LANG_PAGE $i IW_LANG_OF $pages</option>\n" ); }

	echo( "</select>\n" );

	if( $rows ) {

		echo( "<select class='form-contr  ol' name='$grid[prefix]r' onChange=\"document.getElementById('$grid[prefix]page').value=1; document.getElementById('$grid[prefix]paging').submit()\">\n" );

		//echo( "<option value='$grid[rows]' selected>IW_LANG_ROWS $grid[rows] IW_LANG_OF $total</option>\n" );

		for( $i = 25; $i <= 200; $i = $i * 2 ) { echo( "<option value='$i' " . ( $grid['rows'] == $i ? 'selected' : '' ) . ">IW_LANG_ROWS $i IW_LANG_OF $total</option>\n" ); }

		echo( "</select>\n" );

	} else { echo( "<input type='hidden' name='$grid[prefix]r' value='$grid[rows]'>" ); }

	echo iw_grid_hidden( $grid, 0, 0 ) . '</form>';
}

function iw_grid_paging2( $con, $grid, $force = 0, $form = '' ) {
	
	if( $force != 0 ) { $total = $force; } else { $total = iw_total( $con, $grid[ 'table' ], $grid[ 'where' ], $grid[ 'rel' ] ); }

	$pages = ceil( str_replace( "'", '', $total ) / $grid[ 'rows' ] ); // if( $pages > 1 ) {

	if( $grid[ 'page' ] > $pages ) { $grid[ 'page' ] = $pages; } // if current page is greater than total pages

	if( $grid[ 'page' ] < 1 ) { $grid[ 'page' ] = 1; } // if current page is less than first page

	
	echo( "<form id='$grid[prefix]paging' name='$grid[prefix]paging' method='".IW_GRID_METHOD."' action='$form' class='iw-grid-paging2'>\n<ul class='pagination'>\n" ); // " . ( $total == 0 ? "onsubmit='return false;'" : '' ) . "

	if( $total == 0 ) { echo "<li class='page-item active'><a href='javascript:void(0)' class='page-link'>1</a></li>\n\n"; }

	if( $grid[ 'page' ] > 4 ) { echo "<li class='page-item'><a href='javascript:void(0)' onClick=\"document.getElementById('$grid[prefix]page').value='1';document.getElementById('$grid[prefix]paging').submit();\" class='page-link'>&laquo;</a></li>\n\n"; } // 1 <span class='paging_space'>...</span>

	for( $x = ( $grid[ 'page' ] - 3 ); $x < ( ( $grid[ 'page' ] + 3 ) + 1 ); $x++ ) { // loop to show links to range(3) of pages around current page

		if( $x > 0 && $x <= $pages ) { // if it's a valid page number

			$sel = ( $x == $grid[ 'page' ] ) ? "active' style='font-weight:bold'" : '';

			echo "<li class='page-item $sel'><a href='javascript:void(0)' onClick=\"document.getElementById('$grid[prefix]page').value='$x';document.getElementById('$grid[prefix]paging').submit();\" class='page-link'>$x</a></li>\n\n";
		}
	}

	if( $grid[ 'page' ] < ( $pages - 3 ) ) { echo "<li class='page-item'><a href='javascript:void(0)' onClick=\"document.getElementById('$grid[prefix]page').value='$pages';document.getElementById('$grid[prefix]paging').submit();\" class='page-link'>&raquo;</a></li>\n\n"; } // $pages <span class='paging_space'>...</span> 

	echo( "</ul><input type='hidden' name='$grid[prefix]p' id='$grid[prefix]page' value='1'>\n" );

	echo iw_grid_hidden( $grid, 0, 0 ) . '</form>';
}

function iw_grid_column( $grid, $name, $col, $order ) {

	if( stripos( $col, 'as' ) !== false ) { $col = trim( substr( $col, ( stripos( $col, 'as' ) + 2 ) ) ); }

	if( str_replace( '`', '', $grid['column'] ) != $col ) { return "<a href='javascript:void(0)' onclick=\"iw_pages('$grid[prefix]', '$col', '$order')\">" . iw_echo( $name ) . IW_GRID_SORTICON[ $order == 'asc' ? 'asc' : 'desc' ] . '</a>'; }

	else { return "<a href='javascript:void(0)' onclick=\"iw_pages('$grid[prefix]', '$col', '" . ( $grid['order'] == 'asc' ? 'desc' : 'asc' ) . "')\">" . iw_echo( $name ) . IW_GRID_SORTICON[ $grid['order'] == 'asc' ? 'asc_sel' : 'desc_sel' ] . '</a>'; }
}

function iw_grid_hidden( $grid, $manage = 0, $paging = 1 ) {

	$return  = "<input type='hidden' name='$grid[prefix]c' value='$grid[column]'>\n";

	$return .= "<input type='hidden' name='$grid[prefix]o' value='$grid[order]'>\n";

	if( $paging ) { $return .= "<input type='hidden' name='$grid[prefix]r' value='$grid[rows]'>\n"; }

	if( $paging ) { $return .= "<input type='hidden' name='$grid[prefix]p' value='1'>\n"; }

	if( $manage ) { $return .= "<input type='hidden' name='$grid[prefix]action' id='$grid[prefix]action' />\n"; }

	if( defined( 'IW_GRID_CSRF' ) && IW_GRID_CSRF && isset( $_SESSION[ 'iw_csrf' ] ) ) { $return .= "<input type='hidden' name='csrf' value='$_SESSION[iw_csrf]'>\n"; }

	if( !empty( $grid['hidden_search'] ) && is_array( $grid['hidden_search'] ) && ( $manage == 1 || $paging == 0 ) ) { 

		$return .= iw_grid_hidden_search( $grid['hidden_search'] );
		
		/*foreach( $grid['hidden_search'] as $search ) {

			if( isset( $_REQUEST[ $search ] ) ) { 

				if( !is_array( $_REQUEST[ $search ] ) ) { $return .= "<input type='hidden' name='" . $search . "' value='" . iw_echo( $_REQUEST[ $search ] ) . "'>\n"; }

				else { foreach( $_REQUEST[ $search ] as $val ) { $return .= "<input type='hidden' name='" . $search . "[]' value='" . iw_echo( $val ) . "'>\n"; } }
			}
		}*/
	}

	return $return;
}

function iw_grid_hidden_search ( ) {
	
	$args = func_get_args();

	if( !$args[0] || !is_array( $args[0] ) ) { return false; }
	
	$return = '';
	
	foreach( $args[0] as $field ) {

		if( isset( $_REQUEST[ $field ] ) ) { 

			if( !is_array( $_REQUEST[ $field ] ) ) { $return .= "<input type='hidden' name='" . $field . "' value='" . iw_echo( $_REQUEST[ $field ] ) . "'>\n"; }

			else { foreach( $_REQUEST[ $field ] as $val ) { $return .= "<input type='hidden' name='" . $field . "[]' value='" . iw_echo( $val ) . "'>\n"; } }
		}
	}
	
	return $return;
}

function iw_grid_order( $grid, array $sort ) {
	
	$grid['show'] = $sort;
	
	foreach( $sort as $name => $col ) { 
		
		if( isset( $grid['columns'][$col] ) ) { $grid['columns'][$name] = $col; unset( $grid['columns'][$col] ); } // rename
	}

	$grid['columns'] = array_replace( $sort, $grid['columns'] ); // order

	foreach( $grid['data'] as $row => $columns ) {

		foreach( $sort as $name => $col ) { 

			if( isset( $grid['data'][$row][$col] ) ) { $grid['data'][$row][$name] = $grid['data'][$row][$col]; unset( $grid['data'][$row][$col] ); } // rename	
		}
		
		$grid['data'][$row] = array_replace( $sort, $grid['data'][$row] ); // order
	}

	return $grid;
}

function iw_grid_add( $grid, $add, $value = '' ) {
	
	// check already exist
		
	$grid['columns'][ $add ] = '';

	foreach( $grid['data'] as $row => $columns ) {

		foreach( $grid['data'][ $row ] as $key => $val ) { $vars[ "[$key]" ] = $val; }

		$grid['data'][ $row ][ $add ] = str_replace( array_keys( $vars ), array_values( $vars ), $value );
	}
	
	return $grid;
}

function iw_grid_replace( $grid, $column, $replace ) { // passare solo grd[data] ?

	foreach( $grid['data'] as $row => $columns ) {

		if( isset( $grid['data'][ $row ][ $column ] ) ) { 

			foreach( $columns as $key => $val ) { $vars[ "[$key]" ] = $val; }

			$grid['data'][ $row ][ $column ] = str_replace( array_keys( $vars ), array_values( $vars ), $replace );			
		}
	}
	
	return $grid;
}

function iw_grid_rel( $con, $grid, $column, $table, $read, $where, $type = 'data' ) {

	foreach( $grid['data'] as $row => $columns ) {

		if( isset( $grid['data'][ $row ][ $column ] ) ) {

			foreach( $columns as $key => $val ) { $vars[ "[$key]" ] = iw_secure( $con, $val ); }

			$where = str_replace( array_keys( $vars ), array_values( $vars ), $where );

			if( $type == 'data' ) { $grid['data'][ $row ][ $column ] = iw_data( $con, $table, $read, $where ); }

			elseif( $type == 'count' ) { $grid['data'][ $row ][ $column ] = iw_total( $con, $table, $where ); }

			elseif( $type == 'sum' ) { $grid['data'][ $row ][ $column ] = iw_sum( $con, $table, $read, $where ); }
		}
	}
	
	return $grid;
}

function iw_grid_func( $grid, $column, $function, array $arguments = null ) {

	foreach( $grid['data'] as $row => $columns ) {

		if( isset( $grid['data'][ $row ][ $column ] ) && function_exists( $function ) ) { 

			$arguments2 = !empty( $arguments ) ? $arguments : array( "[$column]" );

			foreach( $grid['data'][ $row ] as $key => $val ) { $vars[ "[$key]" ] = $val; }

			foreach( $arguments2 as $key => $argument ) { $arguments2[ $key ] = str_replace( array_keys( $vars ), array_values( $vars ), $argument ); }

			$grid['data'][ $row ][ $column ] = call_user_func_array( $function, $arguments2 );
		}
	}
	
	return $grid;
}

function iw_grid_array( $grid, $column, $array ) {

	if( !isset( $grid['columns'][ $column ] ) ) { iw_grid_add( $grid, $column ); } //$this->ccopy( $column, $copy ) public function ccopy( $add, $value = '' ) { }

	foreach( $grid['data'] as $row => $columns ) {

		if( isset( $grid['data'][ $row ][ $column ] ) && isset( $array[ $grid['data'][ $row ][ $column ] ] ) ) { 

			$grid['data'][ $row ][ $column ] = $array[ $grid['data'][ $row ][ $column ] ];			
		}
	}
	
	return $grid;
}
	
function iw_grid_cond( $grid, $column, $equal, $then, $else = '', $operator = '=' ) {

	if( !isset( $grid['columns'][ $column ] ) ) { iw_grid_add( $grid, $column ); }

	foreach( $grid['data'] as $row => $columns ) { 

		foreach( $columns as $key => $val ) { $vars[ "[$key]" ] = $val; } // vars

		$true = 0;	//if( isset( $this->res[ $row ][ $column ] ) ) { 

		if( $operator == '=' && $grid['data'][ $row ][ $column ] == $equal ) { $true = 1; }

		elseif( $operator == '<' && $grid['data'][ $row ][ $column ] < $equal ) { $true = 1; }

		if( $true ) { $grid['data'][ $row ][ $column ] = str_replace( array_keys( $vars ), array_values( $vars ), $then ); }

		elseif( $else != '' ) { $grid['data'][ $row ][ $column ] = str_replace( array_keys( $vars ), array_values( $vars ), $else ); }
	}
	
	return $grid;
}
	
function iw_grid_read( $grid, $column ) { // serve?

	$array = array();

	foreach( $grid['data'] as $row => $columns ) {

		if( isset( $grid['data'][ $row ][ $column ] ) ) {

			$array[ $row ] = $grid['data'][ $row ][ $column ];
		}
	}
	return $array;
}