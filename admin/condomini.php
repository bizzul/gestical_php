<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// grid

$where = " ";
$value = "";
$counter = 0;
$rel = "";
$group = ' GROUP BY number_route ';

$_REQUEST = iw_secure( $con, $_REQUEST, 0,0,1 );

// ultima data lettura
$ultima = iw_data( $con, 'letture2', 'data', "1=1 ORDER BY data DESC");

// testo
if( isset( $_REQUEST['search11'] ) && $_REQUEST['search11'] != "" ) {
	$colls = array( 'number_route', 'description_route', 'number_estate', 'description_estate', 'number_meter' );
	$collz = ""; foreach( $colls as $coll ) { $collz .= " LOWER(`$coll`) LIKE LOWER('%$_REQUEST[search11]%') OR ";  }
	$where .= " AND ( " . substr( $collz, 0, -3 ) . " ) "; }

if( isset( $_REQUEST['p'] ) && $_REQUEST['p'] != "" && is_numeric( $_REQUEST['p'] ) && $_REQUEST['p'] > 0 ) { $page = $_REQUEST['p']; } else { $page = 1; } 
if( isset( $_REQUEST['r'] ) && $_REQUEST['r'] != "" && is_numeric( $_REQUEST['r'] ) && $_REQUEST['r'] > 0 ) { $rows = $_REQUEST['r']; } else { $rows = 25; }
if( isset( $_REQUEST['c'] ) && ctype_alnum( str_replace( array( '.', '_'),'',$_REQUEST['c']) ) ) { $column = strtolower( $_REQUEST['c'] ); }  else { $column = "number_route"; } 
if( isset( $_REQUEST['o'] ) && strtolower( $_REQUEST['o'] ) == 'desc' ){ $order = "desc"; } else { $order = "asc"; }

$query  = "SELECT t.*, COUNT(*) as myCounter, COUNT(DISTINCT(number_estate)) AS myCounter2 FROM condomini AS t $rel WHERE 1 " . $where . " $group ORDER BY " . $column . " " . $order . " ";
$query .= "LIMIT ".( ( $page - 1 ) * $rows ).", " . $rows;
$result = mysqli_query( $con, $query ) or die( mysqli_error($con) );

$rows_sel = mysqli_num_rows( $result );
if( $rows_sel == 0 ) { $value = "<tr><td colspan='20'><br><b> &nbsp; Nessun risultato trovato</b><br><br></td></tr>"; }
else {

	while( $line = mysqli_fetch_array( $result ) ) {
		
		$counter++;
		if( $counter % 2 == 1 ) { $color = "row_pair"; } else { $color = "row_odd"; }
		
		// contatori
		$contatori = iw_total( $con, 'condomini', "AND number_route = '$line[number_route]' ");
		
		// non letti
		$contatoreNonLetti = mysqli_query( $con, "SELECT t.id FROM condomini t WHERE number_route = '$line[number_route]' AND NOT EXISTS (SELECT * FROM letture2 l WHERE ( l.contatore = t.number_meter OR ( l.meterid != '' AND l.meterid != 0 AND l.meterid = t.number_meter ) ) AND data = '$ultima' )" ) or die( mysqli_error($con));
		$contatoreNonLetti = mysqli_num_rows( $contatoreNonLetti );
	

		$value .= "\t<tr class='$color' id='row_$counter'>\n";
		$value .= "<td>" . $line['number_route'] . "</td>\n";
		$value .= "<td>" . $line['description_route'] . "</td>\n";
		$value .= "<td><b><a href='condomini2.php?id=$line[number_route]'>" . $line['myCounter2'] . "</a></b></td>\n";
		$value .= "<td><b><a href='letture.php?strut11=$line[number_route]'><b>" . $line['myCounter'] . " (". ( $line['myCounter'] + $contatoreNonLetti) .")</b></a></td>\n";
		$value .= "<td><b><a href='nonletti.php?id=$line[number_route]'><b>{$contatoreNonLetti}</b></a></td>\n";
		$value .= "</tr>\n";
	}
}

mysqli_free_result( $result );

?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>

<div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
<h1 class="h2">Strutture</h1>
</div>
	
<div class='iw_subtitle'><span data-feather="search"></span> &nbsp; Filtri di ricerca</div>
<form id="form_search" method="get" action="?" class="border rounded mb-4 d-flex flex-column flex-md-row align-items-center">
	<div class="p-3">
	Cerca testo<br>
	<input type="text" name="search11" class="form-control" value="<?= iw_echo( @$_REQUEST['search11'] ) ?>">
	</div>
	<div class="p-3 ms-auto">
	<br><input type="submit" value="Cerca" class="btn btn-primary  me-2" >
	</div>
<input type="hidden" name="o" value="<?php echo( $order ); ?>" />
<input type="hidden" name="c" value="<?php echo( $column ); ?>" />
<input type="hidden" name="p" value="1" />
</form>		
	
<div class="table-responsive">
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-hover iw-grid-table">
<tr class="iw-grid-column">
<td><?php echo column( "number_route", "asc", 'number_route', $column, $order ) ?></td>
<td><?php echo column( "description_route", "asc", 'description_route', $column, $order ) ?></td>
<td><?php echo column( "myCounter2", "asc", 'Condomini', $column, $order ) ?></td>
<td><?php echo column( "myCounter", "asc", 'Contatori', $column, $order ) ?></td>
<td>Non letti</td>
</tr>
<?php echo $value; ?>
</table>
</div>
	
<div class="float-start">
<input type="button" class='btn btn-primary float-start mt-4' value='Importa CSV' onClick="location.href='condomini_import.php';">
</div>
	
<form id="form_paging" name="form_paging"method="get" action="?" class="iw-grid-paging2 float-end">
<?php echo paging2( 'condomini', $where, $rows_sel, $rows, $page, $column, $order, $rel, $group ); ?>
<? foreach( array('search11') AS $hiddenField ) { 
if( isset( $_REQUEST[ $hiddenField ] ) ) { echo "<input name='$hiddenField' type='hidden' value='".iw_echo($_REQUEST[$hiddenField],0)."' />"; } } ?>
</form>

<? require ( '../inc/tpl/admin_footer.php' ); ?>	
</body>
</html>