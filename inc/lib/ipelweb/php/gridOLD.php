<?php

function column ( $col_sql, $col_ord, $col_name, $col_sel, $ord_sel ) {
	
	if( $col_sel == $col_sql && $ord_sel == 'asc' ) { // sel asc 
	
		$value  = "<a href='javascript:void(0)' onclick=\"paging('$col_sql','desc')\">$col_name <span>&#9650;</span></a>";
	}
	elseif( $col_sel == $col_sql && $ord_sel != 'asc' ) { // sel desc
	
		$value  = "<a href='javascript:void(0)' onclick=\"paging('$col_sql','asc')\">$col_name <span>&#9660;</span></a>";
	}
	elseif( $col_sel != $col_sql ) { // not sel -> $col_ord
	
		$value  = "<a href='javascript:void(0)' onclick=\"paging('$col_sql','$col_ord')\">$col_name ";
		$value .= ( $col_ord == 'asc' ? '<span>&#9651;</span>' : '<span>&#9661;</span>' ) . '</a>'; 
	}
	
	return $value;
}

function paging ( $table, $where, $rows_sel, $rows, $page, $column, $order, $rel = NULL, $group = NULL ) {
	
	global $con;
	
	if( $rows_sel == 0 ) { return false; }
	else {
		
		$rows_totSQL = mysqli_query( $con, "SELECT t.id FROM " . $table . " AS t " . $rel . " WHERE 1 " . $where . " $group" )
			or die( "Errore nella query (".mysqli_error($con).")<br>" );
		$rows_tot = mysqli_num_rows( $rows_totSQL );	
		mysqli_free_result ( $rows_totSQL );
		
		$pages_tot = ceil( $rows_tot / $rows );
		
		//$value  = AFS_LANG_PAGE . ": ";
		$value = "<select name='p' id='paging_page' onchange='document.form_paging.submit()'>";
		for( $pag = 1; $pag <= $pages_tot; $pag++ ) {
			if( $pag == $page ) { $value .= "<option value='$pag' selected='selected'>" . AFS_LANG_PAGE . " $pag " . AFS_LANG_OF . " $pages_tot</option>\n"; }
			else { $value .= "<option value='$pag'>" . AFS_LANG_PAGE . " $pag " . AFS_LANG_OF . " $pages_tot</option>\n"; } 
		}
		$value .= "</select>"; // &nbsp;
		//$value .= AFS_LANG_OF . " $pages_tot - " . AFS_LANG_ROWS . ": ";
		$value .= "<select name='r' id='paging_rows' onchange='paging(\"$column\",\"$order\")'>";
		$value .= "<option selected='selected' value='$rows'>" . AFS_LANG_ROWS . " $rows " . AFS_LANG_OF . " $rows_tot</option>";
		for( $i = 25; $i <= 200; $i=$i*2 ) { $value .= "<option value='$i'>" . AFS_LANG_ROWS . " $i " . AFS_LANG_OF . " $rows_tot</option>\n"; }
		//$value .= "<option value='10'>10</option>\n<option value='20'>20</option>\n<option value='30'>30</option>\n<option value='50'>50</option>\n";
		$value .= "</select>"; // &nbsp;
		//$value .= AFS_LANG_OF . " $rows_tot";
		
 		$value .= "<input name='c' type='hidden' id='paging_column' value='$column' />";
		$value .= "<input name='o' type='hidden' id='paging_order' value='$order' />"; 
		
		return $value;
	}
}

function paging2( $table, $where, $rows_sel, $rows, $page, $column, $order, $rel = NULL, $group = NULL ) {
	
	global $con;
	
	if( $rows_sel == 0 ) { return false; }
	else {
		
		$rows_totSQL = mysqli_query( $con, "SELECT t.id FROM " . $table . " AS t " . $rel . " WHERE 1 " . $where . " $group " )
			or die( "Errore nella query (".mysqli_error($con).")<br>" );
		$rows_tot = mysqli_num_rows( $rows_totSQL );	
		mysqli_free_result ( $rows_totSQL );
		
		$pages_tot = ceil( $rows_tot / $rows );
	
		if( $page > $pages_tot ) { $page = $pages_tot; } // if current page is greater than total pages
		if( $page < 1 ) { $page = 1; } // if current page is less than first page

		$value  = "<span style='float:left; margin: 6px 12px 0 0'>$rows_tot totali</span>";
		$value .= "<ul class='pagination'>\n";

		if( $rows_tot == 0 ) { $value .= "<li class='page-item active'><a href='javascript:void(0)' class='page-link'>1</a></li>\n\n"; }

		if( $page > 4 ) { $value .= "<li class='page-item'><a href='javascript:void(0)' onClick=\"document.getElementById('paging_page').value='1';document.getElementById('form_paging').submit();\" class='page-link'>&laquo;</a></li>\n\n"; } // 1 <span class='paging_space'>...</span>

		for( $x = ( $page - 3 ); $x < ( ( $page + 3 ) + 1 ); $x++ ) { // loop to show links to range(3) of pages around current page

			if( $x > 0 && $x <= $pages_tot ) { // if it's a valid page number

				$sel = ( $x == $page ) ? "active' style='font-weight:bold'" : '';

				$value .= "<li class='page-item $sel'><a href='javascript:void(0)' onClick=\"document.getElementById('paging_page').value='$x';document.getElementById('form_paging').submit();\" class='page-link'>$x</a></li>\n\n";
			}
		}

		if( $page < ( $pages_tot  - 3 ) ) { $value .= "<li class='page-item'><a href='javascript:void(0)' onClick=\"document.getElementById('paging_page').value='$pages_tot';document.getElementById('form_paging').submit();\" class='page-link'>&raquo;</a></li>\n\n"; } // $pages <span class='paging_space'>...</span> 

		$value .= "</ul><input name='p' id='paging_page' type='hidden' value='1' />";
		$value .= "<input name='c' id='paging_column' type='hidden' value='$column' />";
		$value .= "<input name='o' id='paging_order' type='hidden' value='$order' />"; 
		
		//$value .= "<input type='hidden' name='csrf' value='$_SESSION[iw_csrf]'>\n";

		return $value;
	}
}

?>