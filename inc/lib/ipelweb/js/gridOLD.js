/***************************
	COLUMNS
***************************/

function paging ( column , order ) // search
{
	// change input value
	document.getElementById( 'paging_column' ).value = column;
	document.getElementById( 'paging_order' ).value = order;
	document.getElementById( 'paging_page' ).value = '1';
  	
	// send form
	document.form_paging.submit();
}

/***************************
	ACTIONS
***************************/

function execute ( action , action_id , msg ) 
{
	if ( msg === undefined ) { var exec = true; } else { var exec = confirm( msg ); }
	/*	
	// if delete -> alert message
	if ( action == 'delete_single' || action == 'delete_multiple' ) 
    	var exec = confirm ( "Sei sicuro di voler eliminare definitivamente questo/i record/s?" );
	
	else if  ( action == 'pay_single' || action == 'pay_multiple' )
		var exec = confirm ( "Sei sicuro di voler registrare come pagamenti gli affiliati selezionati?" );
	
	else
    	var exec = true;
	*/
  	if ( exec ) 
	{
		// change input value
		document.getElementById( 'action_id' ).value = action_id;
  		document.getElementById( 'action' ).value = action;
		
		// submit form
  		document.getElementById( 'form_manager' ).submit();
  	}
}

/***************************
	SELECT
***************************/

// colorize row
function select_row ( check, row )
{
	// if check is selected
	if(check.checked) 
	{
		// add class
		document.getElementById(row).className = document.getElementById(row).className+", row_select";
	}
	// if check is not selected
	else
	{
		// remove class
		document.getElementById(row).className = document.getElementById(row).className.replace(/, row_select/i, "");
	}
}
// select all rows
function select_all ( check, rows )
{	
	// if check all is selected
	if(check.checked) 
	{ 
		// for num rows
		for( row=1; row<=rows; row++ )
		{
			// select all rows and change color
			document.getElementById( "id_"+row ).checked = true; 	
			document.getElementById( "row_"+row ).className = document.getElementById( "row_"+row ).className+", row_select";	
		}
	}
	// if select all is not selected
	else
	{
		// for num rows
		for( row=1; row<=rows; row++ )
		{
			// unselect all rows and change color
			document.getElementById( "id_"+row ).checked = false; 
			document.getElementById( "row_"+row ).className = document.getElementById( "row_"+row ).className.replace(/, row_select/i, "");
		}
	}
}

/***************************
	SEARCH
***************************/

// responsive search
$( document ).ready(function() { 
	
	// check if button exists
	if($('#search_filters').length){
		
		// hide search filters onload
		if( $(window).width() < 992 ) { $( "#form_search" ).hide(); }
		
		// show/hide search filters onresize
		var initial_width = $(window).width();
		$(window).resize(function() { 
			if( initial_width != $(window).width() ) {
				if( $(window).width() < 992 ) { 
					$( "#form_search" ).hide(); 
					$( '#search_filters' ).removeClass( "search_show" ); 
					$( "#form_search" ).removeClass( "search_toogle" ); 
				} else { 
					$( "#form_search" ).show(); 
					$(".select2").select2({ width: 'resolve' });
				} 
			}
		});
		
		// show/hide search filters onclick
		$('#search_filters').click(function(){ 
			$( "#form_search" ).toggle(); 
			$( "#form_search" ).toggleClass( "search_toogle" ); 
			$( this ).toggleClass( "search_show" ); 
		});
	} 
});
