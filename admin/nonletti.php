<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login

if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// check id
if( !isset( $_REQUEST[ 'id' ] ) || empty( $_REQUEST[ 'id' ] ) ) { header( 'location: condomini.php?error=id1' ); exit; }
$id = iw_secure( $con, $_REQUEST[ 'id' ] );
if( !$row = iw_row( $con, 'condomini', "number_route = '$id'" ) ) { header( 'location: condomini.php?error=id2' ); exit; }

// grid

$where = " AND t.number_route = '$id' AND l.id IS NULL ";
$value = "";
$counter = 0;
$rel = "";
$group = '';

$_REQUEST = iw_secure( $con, $_REQUEST, 0,0,1 );

// ultima data lettura
$ultima = iw_data( $con, 'letture2', 'data', "1=1 ORDER BY data DESC");

// lettura
if( isset( $_REQUEST['data11'] ) && $_REQUEST['data11'] != "" ) { $whereData = "AND data = '$_REQUEST[data11]' "; } else { $whereData = " AND data = '$ultima' "; }
// testo
if( isset( $_REQUEST['search11'] ) && $_REQUEST['search11'] != "" ) {
	$colls = array( 'number_route', 'description_route', 'number_estate', 'description_estate', 'number_meter' );
	$collz = ""; foreach( $colls as $coll ) { $collz .= " LOWER(`$coll`) LIKE LOWER('%$_REQUEST[search11]%') OR ";  }
	$where .= " AND ( " . substr( $collz, 0, -3 ) . " ) "; }

if( isset( $_REQUEST['p'] ) && $_REQUEST['p'] != "" && is_numeric( $_REQUEST['p'] ) && $_REQUEST['p'] > 0 ) { $page = $_REQUEST['p']; } else { $page = 1; } 
if( isset( $_REQUEST['r'] ) && $_REQUEST['r'] != "" && is_numeric( $_REQUEST['r'] ) && $_REQUEST['r'] > 0 ) { $rows = $_REQUEST['r']; } else { $rows = 25; }
if( isset( $_REQUEST['c'] ) && ctype_alnum( str_replace( array( '.', '_'),'',$_REQUEST['c']) ) ) { $column = strtolower( $_REQUEST['c'] ); }  else { $column = "t.number_route ASC, t.number_estate"; } 
if( isset( $_REQUEST['o'] ) && strtolower( $_REQUEST['o'] ) == 'asc' ){ $order = "asc"; } else { $order = "desc"; }


$rel = "LEFT JOIN letture2 AS l ON ( l.contatore = t.number_meter OR  l.meterid = t.number_meter ) $whereData";

$query  = "SELECT * FROM condomini t $rel WHERE 1 $where "; // $where 
$query .= "ORDER BY " . $column . " " . $order . " LIMIT ".( ( $page - 1 ) * $rows ).", " . $rows;
//$query2 = "SELECT * FROM condomini t WHERE number_route = '6814014' AND NOT EXISTS (SELECT * FROM letture2 l WHERE ( l.contatore = t.number_meter OR ( l.meterid != '' AND l.meterid != 0 AND l.meterid = t.number_meter ) )  AND data = '$ultima' ) ";

$result = mysqli_query( $con, $query ) or die( mysqli_error($con) );

$rows_sel = mysqli_num_rows( $result );
if( $rows_sel == 0 ) { $value = "<tr><td colspan='20'><br><b> &nbsp; Nessun risultato trovato</b><br><br></td></tr>"; }
else {

	while( $line = mysqli_fetch_array( $result ) ) {
		
		$counter++;
		if( $counter % 2 == 1 ) { $color = "row_pair"; } else { $color = "row_odd"; }
		

		//$colore_errore = ( $line['errore'] != '' && $line['errore'] != '0b' ) || $line['errore'] == 'vuoto' ? 'error_col' : '';
		
		//if( $line['tipo'] == 'Heat' ) { $line['lettura'] = $line['lettura'] * 0.00000100; $line['lettura2'] = $line['lettura2'] * 0.00000100; } // wattora to megawatt 
		
		$value .= "\t<tr class='$color ' id='row_$counter'>\n";
		//$value .= "<td>" . $line['data_f'] . "</td>\n";
		$value .= "<td>" . $line['number_route'] . "</td>\n";
		$value .= "<td>" . $line['number_estate'] . "</td>\n";
		$value .= "<td>" . $line['description_estate'] . "</td>\n";
		$value .= "<td><b>" . $line['number_meter'] . "</b></td>\n";
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
<h1 class="h2">Apparecchi non letti</h1>
</div>
	
<div class='iw_subtitle'><span data-feather="search"></span> &nbsp; Filtri di ricerca</div>
<form id="form_search" method="get" action="?" class="border rounded mb-4 d-flex flex-column flex-md-row align-items-center">
	<div class="p-3">Data lettura<br>
	<select name="data11" class="form-select">
	<option value="" <?= @$_REQUEST['data11'] == $ultima ? 'selected' : '' ?>><?= date("d.m.Y H:i:s", strtotime($ultima))?> (ultima lettura)</option>
	<? foreach( array_reverse(iw_distinct( $con, 'letture2', 'data', "AND data != '$ultima' " ), true) as $s_data ) {
	echo "<option value='$s_data' ".( @$_REQUEST['data11'] == $s_data ? 'selected' : '').">".date("d.m.Y H:i:s", strtotime($s_data))."</option>\n"; } ?>
	</select></div>
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
<input type="hidden" name="id" value="<?php echo( $id ); ?>" />
</form>	
	
<div class="table-responsive">
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-hover iw-grid-table">
<tr class="iw-grid-column">


<td><?php echo column( "number_route", "asc", 'number_route', $column, $order ) ?></td>
<td><?php echo column( "number_estate", "asc", 'number_estate', $column, $order ) ?></td>
<td><?php echo column( "description_estate", "ASC", 'description_estate', $column, $order ) ?></td>
<td><?php echo column( "number_meter", "asc", 'CONTATORE', $column, $order ) ?></td>

</tr>
<?php echo $value; ?>
</table>
</div>
	

	
<form id="form_paging" name="form_paging"method="get" action="?" class="iw-grid-paging2 float-end">
<?php echo paging2( 'condomini', $where, $rows_sel, $rows, $page, $column, $order, $rel, $group ); ?>
<? foreach( array('search11','data11') AS $hiddenField ) { 
if( isset( $_REQUEST[ $hiddenField ] ) ) { echo "<input name='$hiddenField' type='hidden' value='".iw_echo($_REQUEST[$hiddenField],0)."' />"; } } ?>
<input type="hidden" name="id" value="<?php echo( $id ); ?>" />
</form>	

<? require ( '../inc/tpl/admin_footer.php' ); ?>
</body>
</html>