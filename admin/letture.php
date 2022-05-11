<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login

if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// data
$tipo_nome	 = array( 'ColdWater' => 'Acqua fredda', 'WWater' => 'Acqua calda', 'Heat' => 'Calore', 'Heat/CoolM' => 'Raffreddamento', 'HCA' => 'HCA' );
$tipo_colore = array( 'ColdWater' => 'success', 'WWater' => 'warning', 'Heat' => 'danger', 'Heat/CoolM' => 'info', 'HCA' => 'secondary' );

// grid

$where = "";
$value = "";
$counter = 0;
$rel = "LEFT JOIN condomini AS c ON ( t.contatore = c.number_meter OR ( t.meterid != '' AND t.meterid != 0 AND t.meterid = c.number_meter ) )";
$group = '';//" GROUP BY contatore ";

$_REQUEST = iw_secure( $con, $_REQUEST, 0,0,1 );

// ultima data lettura
$ultima = iw_data( $con, 'letture2', 'data', "1=1 ORDER BY data DESC");

// lettura
if( isset( $_REQUEST['data11'] ) && $_REQUEST['data11'] != "" ) { $where .= "AND data = '$_REQUEST[data11]' "; } else { $where .= " AND data = '$ultima' "; }
// struttura 
if( isset( $_REQUEST['strut11'] ) && $_REQUEST['strut11'] != "" ) { 
	$condomini = iw_column( $con, 'condomini', 'number_meter', 'id', "AND number_route = '".iw_secure( $con, $_REQUEST['strut11'] )."'" );	
	$where .= "AND ( contatore IN ('".implode( "','", $condomini )."') OR ( meterid != '' AND meterid != 0 AND meterid IN ('".implode( "','", $condomini )."') ) )"; }
// condominio
if( isset( $_REQUEST['strut11'] ) && $_REQUEST['strut11'] != "" && isset( $_REQUEST['cond11'] ) && $_REQUEST['cond11'] != "" ) { 
	$where .= "AND c.number_estate = '$_REQUEST[cond11]' "; }
// errori
if( isset( $_REQUEST['err11'] ) && $_REQUEST['err11'] != "" ) { 
	if( $_REQUEST['err11'] == 1 ) { $where .=  "AND ( errore != 'vuoto' AND ( errore = '' OR errore = '0b' )) "; }
	elseif( $_REQUEST['err11'] == 2 ) { $where .=  "AND errore = 'vuoto' ";}
	else { $where .=  "AND ( errore != '' AND errore != '0b' AND errore != 'vuoto' ) "; } }
// tipo
if( isset( $_REQUEST['type11'] ) && $_REQUEST['type11'] != "" ) { $where .= "AND tipo = '$_REQUEST[type11]' "; }
// testo
if( isset( $_REQUEST['search11'] ) && $_REQUEST['search11'] != "" ) {
	$colls = array( 'data', 'contatore', 'tipo', 'meterid', 'lettura', 'data1', 'lettura2', 'data2', 'errore' );
	$collz = ""; foreach( $colls as $coll ) { $collz .= " LOWER(`$coll`) LIKE LOWER('%$_REQUEST[search11]%') OR ";  }
	$where .= " AND ( " . substr( $collz, 0, -3 ) . " ) "; }

if( isset( $_REQUEST['p'] ) && $_REQUEST['p'] != "" && is_numeric( $_REQUEST['p'] ) && $_REQUEST['p'] > 0 ) { $page = $_REQUEST['p']; } else { $page = 1; } 
if( isset( $_REQUEST['r'] ) && $_REQUEST['r'] != "" && is_numeric( $_REQUEST['r'] ) && $_REQUEST['r'] > 0 ) { $rows = $_REQUEST['r']; } else { $rows = 25; }
if( isset( $_REQUEST['c'] ) && ctype_alnum( str_replace( array( '.', '_'),'',$_REQUEST['c']) ) ) { $column = strtolower( $_REQUEST['c'] ); }  else { $column = "c.number_route ASC, c.number_estate"; } 
if( isset( $_REQUEST['o'] ) && strtolower( $_REQUEST['o'] ) == 'desc' ){ $order = "desc"; } else { $order = "asc"; }

$query  = "SELECT t.*, c.number_route, c.description_estate, c.number_estate, DATE_FORMAT( data, '%d.%m.%Y %H:%i:%s' ) AS data_f ";
$query .= "FROM letture2 AS t $rel WHERE 1 " . $where . " $group ORDER BY " . $column . " " . $order . " ";
$query .= "LIMIT ".( ( $page - 1 ) * $rows ).", " . $rows;
$result = mysqli_query( $con, $query ) or die( mysqli_error($con) );

$rows_sel = mysqli_num_rows( $result );
if( $rows_sel == 0 ) { $value = "<tr><td colspan='20'><br><b> &nbsp; Nessun risultato trovato</b><br><br></td></tr>"; }
else {

	while( $line = mysqli_fetch_array( $result ) ) {
		
		$counter++;
		if( $counter % 2 == 1 ) { $color = "row_pair"; } else { $color = "row_odd"; }
		
		
		$colore_errore = ( $line['errore'] != '' && $line['errore'] != '0b' ) || $line['errore'] == 'vuoto' ? 'error_col' : '';
		
		if( $line['tipo'] == 'Heat' ) { $line['lettura'] = $line['lettura'] * 0.00000100; $line['lettura2'] = $line['lettura2'] * 0.00000100; } // wattora to megawatt 
		
		$unita = '';
		if( $line['tipo'] == 'ColdWater' || $line['tipo'] == 'WWater' ) { $unita = " m3"; }
		elseif( $line['tipo'] != 'HCA' ) { $unita = " mw/h"; }
		
		$value .= "\t<tr class='$color $colore_errore' id='row_$counter'>\n";
		//$value .= "<td>" . $line['data_f'] . "</td>\n";
		$value .= "<td>" . $line['number_route'] . "</td>\n";
		$value .= "<td>" . $line['number_estate'] . "</td>\n";
		$value .= "<td>" . $line['description_estate'] . "</td>\n";
		$value .= "<td><a href='contatore.php?id=$line[contatore]'><b>" . $line['contatore'] . "</b></a></td>\n";
		$value .= "<td><span class='badge bg-{$tipo_colore[$line['tipo']]}'>" . $tipo_nome[$line['tipo']] . "</span></td>\n";
		$value .= "<td>" . $line['meterid'] . "</td>\n";
		$value .= "<td><b>" . $line['lettura'] . "$unita</b></td>\n";
		$value .= "<td>" . str_replace( ' invalid 0 summer time 0', '', $line['data1'] ) . "</td>\n";
		$value .= "<td>" . $line['lettura2'] . "$unita</td>\n";
		$value .= "<td>" . str_replace( ' invalid 0 summer time 0', '', $line['data2'] ) . "</td>\n";
		$value .= "<td>" . $line['errore'] . "</td>\n";
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
<h1 class="h2"><? if( isset( $_REQUEST['strut11'] ) && $t_strut = iw_row($con,'condomini',"number_route = '".iw_secure( $con, $_REQUEST['strut11'] )."'")  ) {
	echo "$t_strut[number_route] - $t_strut[description_route]"; } else { echo 'Ultime letture consumi'; } ?>
</h1>
</div>
	
<div class='iw_subtitle'><span data-feather="search"></span> &nbsp; Filtri di ricerca</div>
<form id="form_search" method="get" action="?" class="border rounded mb-4 d-flex flex-column flex-md-row align-items-center">
	<div class="p-3">Data lettura<br>
	<select name="data11" class="form-select">
	<option value="" <?= @$_REQUEST['data11'] == $ultima ? 'selected' : '' ?>><?= date("d.m.Y H:i:s", strtotime($ultima))?> (ultima lettura)</option>
	<? foreach( array_reverse(iw_distinct( $con, 'letture2', 'data', "AND data != '$ultima' " ), true) as $s_data ) {
	echo "<option value='$s_data' ".( @$_REQUEST['data11'] == $s_data ? 'selected' : '').">".date("d.m.Y H:i:s", strtotime($s_data))."</option>\n"; } ?>
	</select></div>
	<div class="p-3">Struttura<br>
	<select name="strut11" class="form-select">
	<option value="">Tutte</option>
	<? foreach( iw_distinct( $con, 'condomini', 'number_route', "" ) as $s_struttura ) {
	echo "<option value='$s_struttura' ".( @$_REQUEST['strut11'] == $s_struttura ? 'selected' : '').">
	$s_struttura - ".iw_data($con,'condomini','description_route',"number_route = '$s_struttura'")."</option>\n"; } ?>
	</select></div>
	<? if( isset( $_REQUEST['strut11'] ) && !empty( $_REQUEST['strut11'] ) ) { ?>
	<div class="p-3">Condominio<br>
	<select name="cond11" class="form-select">
	<option value="">Tutti</option>
	<? foreach( iw_distinct( $con, 'condomini', 'number_estate', "AND number_route = '".iw_secure( $con, $_REQUEST['strut11'] )."' " ) as $s_condominio ) {
	echo "<option value='$s_condominio' ".( @$_REQUEST['cond11'] == $s_condominio ? 'selected' : '').">
	$s_condominio - ".iw_data($con,'condomini','description_estate',"number_estate = '$s_condominio'")."</option>\n"; } ?>
	</select></div>
	<? } ?>
	<div class="p-3">Errore<br>
	<select name="err11" class="form-select">
	<option value="">Tutti</option>
	<option value="1" <?= @$_REQUEST['err11'] == 1 ? 'selected' : '' ?>>Funzionanti</option>
	<option value="3" <?= @$_REQUEST['err11'] == 3 ? 'selected' : '' ?>>Errori</option>
	<option value="2" <?= @$_REQUEST['err11'] == 2 ? 'selected' : '' ?>>Vuoti</option>
	</select></div>
	<div class="p-3">Tipo contatore<br>
	<select name="type11" class="form-select">
	<option value="">Tutti</option>
	<? foreach( iw_distinct( $con, 'letture2', 'tipo', "AND tipo != '' AND data = '".iw_secure( $con, isset( $_REQUEST['data11'] ) ? $_REQUEST['data11'] : $ultima )."' "  ) as $type ) {
	echo "<option value='$type' ".( @$_REQUEST['type11'] == $type ? 'selected' : '').">{$tipo_nome[$type]}</option>\n"; } ?>
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
</form>	
	
<div class="table-responsive">
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-hover iw-grid-table">
<tr class="iw-grid-column">
<!--td><?php echo column( "data", "desc", 'DATA', $column, $order ) ?></td-->
	
<td><?php echo column( "c.number_route", "asc", 'number_route', $column, $order ) ?></td>
<td><?php echo column( "c.number_estate", "asc", 'number_estate', $column, $order ) ?></td>
<td><?php echo column( "c.description_estate", "ASC", 'description_estate', $column, $order ) ?></td>
	
<td><?php echo column( "contatore", "asc", 'CONTATORE', $column, $order ) ?></td>
<td><?php echo column( "tipo", "asc", 'TIPO', $column, $order ) ?></td>
<td><?php echo column( "meterid", "asc", 'METERID', $column, $order ) ?></td>
<td><?php echo column( "lettura", "asc", 'LETTURA', $column, $order ) ?></td>
<td><?php echo column( "data1", "asc", 'DATA1', $column, $order ) ?></td>
<td><?php echo column( "lettura2", "asc", 'LETTURA2', $column, $order ) ?></td>
<td><?php echo column( "data2", "asc", 'DATA2', $column, $order ) ?></td>
<td><?php echo column( "errore", "asc", 'ERRORE', $column, $order ) ?></td>
</tr>
<?php echo $value; ?>
</table>
</div>
	
<div class="float-start">
<input type="button" class='btn btn-primary float-start mt-4' value='Esporta CSV' onClick="location.href='letture_export.php';">
</div>
	
<form id="form_paging" name="form_paging"method="get" action="?" class="iw-grid-paging2 float-end">
<?php echo paging2( 'letture2', $where, $rows_sel, $rows, $page, $column, $order, $rel, $group ); ?>
<? foreach( array('search11','type11','strut11','data11','err11', 'cond11') AS $hiddenField ) { 
if( isset( $_REQUEST[ $hiddenField ] ) ) { echo "<input name='$hiddenField' type='hidden' value='".iw_echo($_REQUEST[$hiddenField],0)."' />"; } } ?>
</form>	

<? require ( '../inc/tpl/admin_footer.php' ); ?>
</body>
</html>