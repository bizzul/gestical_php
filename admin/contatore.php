<?php 

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// check id
if( !isset( $_REQUEST[ 'id' ] ) || empty( $_REQUEST[ 'id' ] ) ) { header( 'location: index.php?error=contatore1' ); exit; }
$id = iw_secure( $con, $_REQUEST[ 'id' ] );
if( !$row = iw_row( $con, 'letture2', "contatore = '$id'" ) ) { header( 'location: index.php?error=contatore2' ); exit; }

// data
$tipo_nome	 = array( 'ColdWater' => 'Acqua fredda', 'WWater' => 'Acqua calda', 'Heat' => 'Calore', 'Heat/CoolM' => 'Raffreddamento', 'HCA' => 'HCA' );
$tipo_colore = array( 'ColdWater' => 'success', 'WWater' => 'warning', 'Heat' => 'danger', 'Heat/CoolM' => 'info', 'HCA' => 'secondary' );


// grid

$where = " AND contatore = '$id' ";
$where .=" AND `data` IN ( SELECT MAX(`data`) FROM letture2 WHERE contatore = '$id' GROUP BY DATE(`data`) )";
$value = "";
$counter = 0;
$rel = "";
$group = '';//" GROUP BY contatore ";

$_REQUEST = iw_secure( $con, $_REQUEST, 0,0,1 );

// da/a
if( isset( $_REQUEST['from11'] ) && $_REQUEST['from11'] != "" && isset( $_REQUEST['to11'] ) && $_REQUEST['to11'] != '' ) {
	$where .= " AND ( data >= '$_REQUEST[from11] 00:00:00' AND data <= '$_REQUEST[to11] 23:59:59' ) "; }
// testo
if( isset( $_REQUEST['search11'] ) && $_REQUEST['search11'] != "" ) {
	$colls = array( 'data', 'contatore', 'tipo', 'meterid', 'lettura', 'data1', 'lettura2', 'data2', 'errore' );
	$collz = ""; foreach( $colls as $coll ) { $collz .= " LOWER(`$coll`) LIKE LOWER('%$_REQUEST[search11]%') OR ";  }
	$where .= " AND ( " . substr( $collz, 0, -3 ) . " ) "; }

if( isset( $_REQUEST['p'] ) && $_REQUEST['p'] != "" && is_numeric( $_REQUEST['p'] ) && $_REQUEST['p'] > 0 ) { $page = $_REQUEST['p']; } else { $page = 1; } 
if( isset( $_REQUEST['r'] ) && $_REQUEST['r'] != "" && is_numeric( $_REQUEST['r'] ) && $_REQUEST['r'] > 0 ) { $rows = $_REQUEST['r']; } else { $rows = 25; }
if( isset( $_REQUEST['c'] ) && ctype_alnum( str_replace( array( '.', '_'),'',$_REQUEST['c']) ) ) { $column = strtolower( $_REQUEST['c'] ); }  else { $column = "data"; } 
if( isset( $_REQUEST['o'] ) && strtolower( $_REQUEST['o'] ) == 'asc' ){ $order = "asc"; } else { $order = "desc"; }

$query  = "SELECT t.*, DATE_FORMAT( data, '%d.%m.%Y %H:%i:%s' ) AS data_f ";
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
		
		if( $row['tipo'] == 'Heat' ) { $line['lettura'] = $line['lettura'] * 0.00000100; $line['lettura2'] = $line['lettura2'] * 0.00000100; } // wattora to megawatt 
		
		$unita = '';
		if( $line['tipo'] == 'ColdWater' || $line['tipo'] == 'WWater' ) { $unita = " m3"; }
		elseif( $line['tipo'] != 'HCA' ) { $unita = " mw/h"; }
		
		$value .= "\t<tr class='$color $colore_errore' id='row_$counter'>\n";
		$value .= "<td>" . $line['data_f'] . "</td>\n";
		$value .= "<td><span class='badge bg-{$tipo_colore[$line['tipo']]}'>" . $tipo_nome[$line['tipo']] . "</span></td>\n";
		$value .= "<td>" . $line['meterid'] . "</td>\n";
		$value .= "<td><b>" . $line['lettura'] . " $unita</b></td>\n";
		$value .= "<td>" . $line['data1']  . "</td>\n";
		$value .= "<td>" . $line['lettura2'] . " $unita</td>\n";
		$value .= "<td>" . $line['data2'] . "</td>\n";
		$value .= "<td>" . $line['errore'] . "</td>\n";
		$value .= "</tr>\n";
	}
}

mysqli_free_result( $result );	
		
// struttura per titolo
$t_strut = iw_row($con,'condomini',"number_meter = '$id' OR number_meter = '$row[meterid]'" );
$t_title = isset( $t_strut['number_route']) ? " - $t_strut[description_estate] ($t_strut[number_estate])" : '';


// dati grafico
$grafico = iw_column( $con, 'letture2', 'lettura', 'data', " $where" );
foreach( $grafico as $gkey => $gval ){ 
	if( $row['tipo'] == 'Heat' ) { $gval = $gval * 0.00000100; } // wattora to megawatt 
	$grafico2[ date("d.m.Y", strtotime($gkey)) ] = $gval; }


// dati grafico NUOVI (menisle + differenza)
$grafico2 = array();
$ultima = 0;
for( $i = 13; $i >= 0; $i-- ) {
	$mese = date("Y-m",strtotime("-$i month"));
	$dati = iw_select($con,"SELECT data, lettura, tipo FROM letture2 WHERE contatore = '$id' AND data LIKE '$mese%' ORDER BY data DESC LIMIT 1 " );
	if( !$dati ) { $dati[0]['data'] = $mese.'-01'; $dati[0]['lettura'] = 0; $dati[0]['tipo'] = ''; }
	//$differenza = $dati[0]['lettura'] - $ultima;
	//$ultima = $dati[0]['lettura']; // next loop
	//$dati[0]['lettura'] = $differenza;
	//echo "mese $i ($mese) > ".$dati[0]['data']." > ".$dati[0]['lettura']."<br>";
	if( $dati[0]['tipo'] == 'Heat' ) { $dati[0]['lettura'] = $dati[0]['lettura'] * 0.00000100; } // wattora to megawatt 
	$grafico2[ date("Y-m", strtotime( $dati[0]['data'] )) ] = $dati[0]['lettura'];
}






// NUOVO grafico mensile
//$grafico_mensile = array();
$grafico_mensile_tmp = array();
$valore_minimo = 0;
$valore_ultimo = 0;
for( $i = 12; $i >= 0; $i-- ) {
	$mese = date("Y-m",strtotime("-$i month"));
	$dati = iw_select($con,"SELECT data, lettura, tipo FROM letture2 WHERE contatore = '$id' AND data LIKE '$mese%' ORDER BY data DESC LIMIT 1 " );
	if( !$dati ) { $dati[0] = array( 'data' => $mese.'-01', 'lettura' => 0, 'tipo' => '' ); }
	else { $dati[0]['data'] = substr( $dati[0]['data'], 0, 7 ).'-01'; }
	
	if( $dati[0]['tipo'] == 'Heat' ) { $dati[0]['lettura'] = $dati[0]['lettura'] * 0.00000100; } // wattora to megawatt 
	
	if( $valore_minimo == 0 && $dati[0]['lettura'] != 0 ) { $valore_minimo = $dati[0]['lettura']; } // x differenza
	//if( $dati[0]['lettura'] != 0 ) { $valore_ultimo = $dati[0]['lettura']; } // x differenza
	
	if( $valore_ultimo && $dati[0]['lettura'] == 0 ) { $dati[0]['lettura'] = $valore_ultimo; } // riempie letture mancanti

	$differenza = $dati[0]['lettura'] == 0 ? 0 : ( $dati[0]['lettura'] - $valore_ultimo );
	
	$grafico_mensile_tmp[ date("Y-m", strtotime( $dati[0]['data'] )) ] = round($differenza, 4);
	//$grafico_mensile_tmp[ date("Y-m", strtotime( $dati[0]['data'] )) ] = $dati[0]['lettura'];
		
	//echo var_dump( $dati ); 
	if( isset( $_GET['dati_mensili'] ) ) { echo "> mese: $mese - lettura: {$dati[0]['lettura']} - ultimo: $valore_ultimo - minimo: $valore_minimo - differenza: $differenza<br>"; }
	
	$valore_ultimo = $dati[0]['lettura'];
}
if( isset( $_GET['dati_mensili'] ) ) { exit; }
//echo var_dump( $grafico_mensile_tmp );
/*foreach( $grafico_mensile_tmp as $data => $lettura ) {
	$grafico_mensile_tmp[ $data ] = $lettura == 0 ? $valore_minimo : $lettura;
}*/
//echo var_dump( $grafico_mensile );


// NUOVO grafico giornaliero
if( @$_GET['ctime'] == 'Giornaliero' ) {
	$grafico_mensile_tmp = array();
	$valore_minimo = 0;
	$valore_ultimo = 0;
	for( $i = 30; $i >= 0; $i-- ) {
		$giorno = date("Y-m-d",strtotime("-$i day"));
		$dati = iw_select($con,"SELECT data, lettura, tipo FROM letture2 WHERE contatore = '$id' AND data LIKE '$giorno%' ORDER BY data DESC LIMIT 1 " );
		if( !$dati ) { $dati[0] = array( 'data' => $giorno, 'lettura' => 0, 'tipo' => '' ); }
		else { $dati[0]['data'] = substr( $dati[0]['data'], 0, 10 ); }

		if( $dati[0]['tipo'] == 'Heat' ) { $dati[0]['lettura'] = $dati[0]['lettura'] * 0.00000100; } // wattora to megawatt 

		if( $valore_minimo == 0 && $dati[0]['lettura'] != 0 ) { $valore_minimo = $dati[0]['lettura']; } // x differenza
		//if( $dati[0]['lettura'] != 0 ) { $valore_ultimo = $dati[0]['lettura']; } // x differenza

		if( $valore_ultimo && $dati[0]['lettura'] == 0 ) { $dati[0]['lettura'] = $valore_ultimo; } // riempie letture mancanti

		$differenza = $dati[0]['lettura'] == 0 ? 0 : ( $dati[0]['lettura'] - $valore_ultimo );

		$grafico_mensile_tmp[ date("d.m.y", strtotime( $dati[0]['data'] )) ] = round($differenza, 4);
		//$grafico_mensile_tmp[ date("Y-m", strtotime( $dati[0]['data'] )) ] = $dati[0]['lettura'];

		//echo var_dump( $dati ); echo "$giorno ($valore_minimo)<br>";
		if( isset( $_GET['dati_giorno'] ) ) { echo "> giorno: $giorno - lettura: {$dati[0]['lettura']} - ultimo: $valore_ultimo - minimo: $valore_minimo - differenza: $differenza<br>"; }
		
		$valore_ultimo = $dati[0]['lettura'];
	}	
	
}
if( isset( $_GET['dati_giorno'] ) ) { exit; }


// NUOVO grafico settimanale
if( @$_GET['ctime'] == 'Settimanale' ) {
	$grafico_mensile_tmp = array();
	$valore_minimo = 0;
	$valore_ultimo = 0;
	for( $i = 12; $i >= 0; $i-- ) {
		$first_day_of_week = date('Y-m-d', strtotime('Last Monday', strtotime("-$i week")));
		$last_day_of_week = date('Y-m-d', strtotime('Next Sunday', strtotime("-$i week")));
		//echo "$first_day_of_week / $last_day_of_week<br>";
		$week_label = date('d.m', strtotime('Last Monday', strtotime("-$i week"))) . "/" . date('d.m', strtotime('Next Sunday', strtotime("-$i week")));
		
		$dati = iw_select($con,"SELECT data, lettura, tipo FROM letture2 WHERE contatore = '$id' AND (data BETWEEN '$first_day_of_week 00:00:00' AND '$last_day_of_week 23:59:59') ORDER BY data DESC LIMIT 1 " );
		if( !$dati ) { $dati[0] = array( 'data' => $week_label, 'lettura' => 0, 'tipo' => '' ); }
		else { $dati[0]['data'] = $week_label; } //substr( $dati[0]['data'], 0, 10 ); }

		if( $dati[0]['tipo'] == 'Heat' ) { $dati[0]['lettura'] = $dati[0]['lettura'] * 0.00000100; } // wattora to megawatt 

		if( $valore_minimo == 0 && $dati[0]['lettura'] != 0 ) { $valore_minimo = $dati[0]['lettura']; } // x differenza
		//if( $dati[0]['lettura'] != 0 ) { $valore_ultimo = $dati[0]['lettura']; } // x differenza

		if( $valore_ultimo && $dati[0]['lettura'] == 0 ) { $dati[0]['lettura'] = $valore_ultimo; } // riempie letture mancanti

		$differenza = $dati[0]['lettura'] == 0 ? 0 : ( $dati[0]['lettura'] - $valore_ultimo );

		$grafico_mensile_tmp[ $week_label ] = round($differenza, 4);
		//$grafico_mensile_tmp[ date("Y-m", strtotime( $dati[0]['data'] )) ] = $dati[0]['lettura'];

		//echo var_dump( $dati ); echo "$giorno ($valore_minimo)<br>";
		if( isset( $_GET['dati_settimana'] ) ) { echo "> settimana: $week_label - lettura: {$dati[0]['lettura']} - ultimo: $valore_ultimo - minimo: $valore_minimo - differenza: $differenza<br>"; }
		
		$valore_ultimo = $dati[0]['lettura'];
	}	
	
}
if( isset( $_GET['dati_settimana'] ) ) { exit; }

// NUOVO grafico annuale

?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>
		
<div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
<h1 class="h2">Contatore <?= $id . $t_title ?></h1>
</div>
	
<div class='iw_subtitle'><span data-feather="search"></span> &nbsp; Filtri di ricerca</div>
<form id="form_search" method="get" action="" class="border rounded mb-4 d-flex flex-column flex-md-row align-items-center">
	<div class="p-3">
	Periodo<br>
	<input type="text" class="form-control" id="from11" name="from11" value="<?= iw_echo( @$_REQUEST['from11'] ) ?>" placeholder="Da">
	</div>
	<div class="p-3">
	<br>
	<input type="text" class="form-control" id="to11" name="to11" value="<?= iw_echo( @$_REQUEST['to11'] ) ?>" placeholder="A">
	</div>
	<div class="p-3">
	Cerca Testo<br>
	<input type="text" class="form-control" name="search11" value="<?= iw_echo( @$_REQUEST['search11'] ) ?>">
	</div>
	<div class="p-3 ms-auto">
	<br><input type="submit" value="Cerca" class="btn btn-primary me-2">
	</div>
<input type="hidden" name="id" value="<?php echo( $id ); ?>" />
<input type="hidden" name="o" value="<?php echo( $order ); ?>" />
<input type="hidden" name="c" value="<?php echo( $column ); ?>" />
<input type="hidden" name="p" value="1" />
</form>
	
<div class="row">
	
	
<div class="col-12">
	<div class='iw_subtitle'><span data-feather="bar-chart"></span> &nbsp; Grafico consumi 
		<form class="float-end" id="form_chart1" name="form_chart1"  method="get" action="?">
		<input type="hidden" name="id" value="<?= $id ?>">
		<select name="ctime" onChange="this.form.submit()">
			<option value="">Mensile</option>
			<option value="Settimanale" <?= @$_GET['ctime'] == 'Settimanale' ? 'selected' : '' ?>>Settimanale</option>
			<option value="Giornaliero" <?= @$_GET['ctime'] == 'Giornaliero' ? 'selected' : '' ?>>Giornaliero</option>
		</select>
		
		</form>
	
	</div>
	<div class="border rounded p-3 mb-3">
		
	<? /*<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Mese', 'Lettura'],
		<? 	foreach( $grafico_mensile_tmp as $data => $lettura ) {
			echo "[ '$data', $lettura ], ";
		} ?>
			
        // ['2013',  1000,],          ['2014',  1170],          ['2015',  660],
   
        ]);

        var options = {
          //title: 'Company Performance',
          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    <div id="chart_div" style="width: 100%; height: 500px;"></div>
	 */ ?>
		
		<!--canvas class="mt-1 w-100" id="graphMensile" width="1200" height="400" style="max-height: 400px;"></canvas-->
		
		<canvas class="mt-1 w-100" id="graphConsumi" width="1200" height="400" style="max-height: 400px;"></canvas>
	</div>	
</div>	
	
	
	
<!--div class="col-12 col-md-6">
	
	<div class='iw_subtitle'><span data-feather="bar-chart"></span> &nbsp; Consumi giornalieri</div>
	<div class="border rounded p-3 mb-3">
		
	</div>
	
</div>
<div class="col-12 col-md-6">

	<div class='iw_subtitle'><span data-feather="bar-chart"></span> &nbsp; Consumi giornalieri </div>
	<div class="border rounded p-3 mb-3">
		<canvas class="mt-1 w-100" id="graphConsumi" width="600" height="300"></canvas>
	</div>
	
</div-->	
</div>
	
<div class="table-responsive">
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-hover iw-grid-table">
<tr class="iw-grid-column">
<td><?php echo column( "data", "desc", 'GIORNO', $column, $order ) ?></td>
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
	
<form id="form_paging" name="form_paging"method="get" action="?" class="iw-grid-paging2 float-end">
<?php echo paging2( 'letture2', $where, $rows_sel, $rows, $page, $column, $order, $rel, $group ); ?>
<? foreach( array('search11','from11','to11') AS $hiddenField ) { 
if( isset( $_REQUEST[ $hiddenField ] ) ) { echo "<input name='$hiddenField' type='hidden' value='".iw_echo($_REQUEST[$hiddenField],0)."' />"; } } ?>
<input type="hidden" name="id" value="<?php echo( $id ); ?>" />
</form>	
	
<? require ( '../inc/tpl/admin_footer.php' ); ?>	
<script>
/*/ jquery ui date piker
$( function() { $( "#from11,#to11" ).datepicker({ dateFormat: 'yy-mm-dd', maxDate: '+0d' }); });
 
 // Line Graphs
  var ctx = document.getElementById('graphMensile')
  // eslint-disable-next-line no-unused-vars
  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [
		  <?= "'". implode( "','", array_keys( $grafico_mensile_tmp ) ) ."'" // implode( "','", $grafico['data'] ) ?>
      ],
      datasets: [{
        data: [
			 <?= "'". implode( "','", array_values( $grafico_mensile_tmp ) ) ."'" ?>
        ],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#e68d07',
        borderWidth: 5,
        pointBackgroundColor: '#e68d07',
		fill: true,
      }]
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false
          }
        }]
      },
      legend: {
        display: false
      }
    }
  });
 */ 
	
	
// bar Graphs
  var ctx = document.getElementById('graphConsumi');
  // eslint-disable-next-line no-unused-vars
  var myChart2 = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [
		  <?= "'". implode( "','", array_keys( $grafico_mensile_tmp ) ) ."'" // implode( "','", $grafico['data'] ) ?>
      ],
      datasets: [{
        data: [
			 <?= "'". implode( "','", array_values( $grafico_mensile_tmp ) ) ."'" ?>
        ],
        /*lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#e68d07',
        borderWidth: 5,
        pointBackgroundColor: '#e68d07',*/
		backgroundColor: 'rgba(255, 159, 64, 0.5)',
		borderColor: 'rgb(255, 159, 64)',
		borderWidth: 1,
      }]
    },
	options: {
		legend: {
			display: false
		}
	}
  });
</script>	
</body>
</html>