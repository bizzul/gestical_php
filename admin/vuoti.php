<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// data
$tipo_nome	 = array( 'ColdWater' => 'Acqua fredda', 'WWater' => 'Acqua calda', 'Heat' => 'Calore', 'Heat/CoolM' => 'Raffreddamento', 'HCA' => 'HCA' );
$tipo_colore = array( 'ColdWater' => 'success', 'WWater' => 'warning', 'Heat' => 'danger', 'Heat/CoolM' => 'info', 'HCA' => 'secondary' );


// ultima data letture
$date = iw_data( $con, 'vuoti', 'data', "1=1 ORDER BY data DESC");






	
// search	
//$search  = iw_search_where( $con, 'id', 'search11' );
//$search .= iw_search_between( $con, 'login', 'from11', 'to11' );
$search='';

// grid
$grid = iw_grid_sql( $con, "SELECT data, contatore, tipo, frame, produttore, versione FROM vuoti AS t WHERE AND data LIKE '$date'  $search ORDER BY data ASC", '' );

$grid = iw_grid_replace( $grid, 'contatore', "<b>[contatore]</b>" );


?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>



		
	  <div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Errori</h1>
      </div>
	
	
<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link " aria-current="page" href="errori.php"><strong>Contatori con errori</strong></a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="vuoti.php"><strong>Contatori vuoti</strong></a>
  </li>
</ul>
	
	
	<?= iw_grid_table( $grid, 1, 0, '', 0, '' ); ?>

		<div class="float-end"><? iw_grid_paging2( $con, $grid, 0 ); ?></div>
	
<div class="clearfix"></div>
	

    
	


<? require ( '../inc/tpl/admin_footer.php' ); ?>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

	
	
</body>
</html>