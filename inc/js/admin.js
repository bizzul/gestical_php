  
feather.replace();



// menu active
if( window.location.pathname == '/admin/' || window.location.pathname == '/admin/index.php' ) { $("#sidebarMenu a.nav-link:eq(0)").addClass('active'); }
if( window.location.pathname == '/admin/condomini.php' ) { $("#sidebarMenu  a.nav-link:eq(1)").addClass('active'); }
if( window.location.pathname == '/admin/letture.php' ) { $("#sidebarMenu a.nav-link:eq(2)").addClass('active'); }
if( window.location.pathname == '/admin/errori.php' || window.location.pathname == '/admin/vuoti.php' ) { $("#sidebarMenu a.nav-link:eq(3)").addClass('active'); }
if( window.location.pathname == '/admin/account.php' ) { $("#sidebarMenu a.nav-link:eq(4)").addClass('active'); }




// toogle desktop menu
function toggle_desktop_menu () {
	if( document.getElementById('sidebarMenu').style.display == 'none' ) {
		document.getElementById('sidebarMenu').style = 'display: block !important';
		document.getElementById('iw_main').style = '';
	} else {
		document.getElementById('sidebarMenu').style = 'display: none !important';
		document.getElementById('iw_main').style = 'width: 100% !important';
	}
}




/* globals Chart:false, feather:false */





	/*

let nomeCliente = "null";
let rows = [];
let headers = [];
let cells = [];


if( window.location.pathname == '/admin/letture.php' ) {

$(document).ready(function(){

    $(function(){
        var $table = $("#letture"),
        $headerCells = $table.find("tr th"),
        $myrows = $table.find("tr"); // Changed this to loop through rows

       
            

        $headerCells.each(function() {
            headers[headers.length] = $(this).text();
        });

        $myrows.each(function() {
            $mycells = $myrows.find( "td" ); // loop through cells of each row
            
            $mycells.each(function() {
                cells.push($(this).text());
            });
            if ( cells.length > 0 ) {
                rows.push(cells);
            }  
        });
        //console.log(headers);
        //console.log(rows);
        //console.log(cells);
    }); 

    $( "#filtroDati" ).autocomplete({
      source: cells
    });
 

    textClient();
    $('#letture').DataTable({
        "searching": false,
        "language": {
        "info": "Mostra _PAGE_ di _PAGES_"},
        "loadingRecords": "Caricamento...",
        "processing":     "Elaborazione...",
        "paginate": {
        "first":      "Primo",
        "last":       "Ultimo",
        "next":       "Prossimo",
        "previous":   "Precedente"
        }
    });

    $("#filtroDati").on("keyup", function() {
    var value = $(this).val().toLowerCase();

    $("#letture tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) });
  });
  
    
});
	
}

function textClient(){

    if (nomeCliente != "null"){

        $(".titleTable").text = ("Letture per: " + nomeCliente);

    }
};

	/*
 $( function() {
    var dateFormat = "dd/mm/yy",
      from = $( "#from" )
        .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 3
        })
        .on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
      });
 
    function getDate( element ) {
      var date;
      try {
        date = $.datepicker.parseDate( dateFormat, element.value );
      } catch( error ) {
        date = null;
      }
 
      return date;
    }
  });
*/
