function iw_pages( prefix, column, order ) {
	
	if( !document.getElementById( prefix + 'paging' ).elements[ prefix + 'c' ] ) { return false; }

	document.getElementById( prefix + 'paging' ).elements[ prefix + 'c' ].value = column;
	
	document.getElementById( prefix + 'paging' ).elements[ prefix + 'o' ].value = order;
	
	document.getElementById( prefix + 'paging' ).elements[ prefix + 'p' ].value = 1;
  	
	document.getElementById(  prefix + 'paging' ).submit();
}

function iw_select_row( checkbox, id ) {
	
	if( checkbox.checked ) { document.getElementById( id ).className = document.getElementById( id ).className + ' table-primary iw-grid-selected'; }
	
	else { document.getElementById( id ).className = document.getElementById( id ).className.replace( / table-primary iw-grid-selected/i, '' ); }
}

function iw_select_all( prefix, checkbox, total ) {
		
	for( row = 0; row <= ( total - 1 ); row++ ) {
		
		document.getElementById( prefix + 'id_' + row ).checked = ( checkbox.checked ) ? true : false;
		
		document.getElementById( prefix + 'row_' + row ).className = document.getElementById( prefix + 'row_' + row ).className.replace( / table-primary iw-grid-selected/i, '' );
		
		if( checkbox.checked ) { document.getElementById( prefix + 'row_' + row ).className = document.getElementById( prefix + 'row_' + row ).className + " table-primary iw-grid-selected"; }	
	}
}



var iw_grid_action = '';

function iw_execute( afs_action, afs_msg, prefix, jqueryui ) {
	
	jqueryui = ( typeof jqueryui === 'undefined' ) ? 0 : 1;

	if( afs_msg == '' ) { document.getElementById( prefix + 'action' ).value = afs_action; document.getElementById( prefix + 'grid' ).submit(); }
	
	else {
		
		if( jqueryui == 1 ) { iw_grid_action = afs_action; $( "#afs_confirm_msg" ).html( afs_msg ); $( '#afs_confirm' ).dialog( 'open' ); }

		else{ if( exec = confirm( afs_msg ) ) { document.getElementById( prefix + 'action' ).value = afs_action; document.getElementById( prefix + 'grid' ).submit(); } }
	}
}
