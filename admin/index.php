<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }

// ultima data letture
$date = iw_data( $con, 'letture2', 'data', "1=1 ORDER BY data DESC");

// contatori
$conta_vuoti = iw_total( $con, 'letture2', "AND data = '$date' AND errore = 'vuoto' ORDER BY data DESC");
$conta_errori = iw_total( $con, 'letture2', "AND data = '$date' AND errore != 'vuoto' AND errore != '' AND errore != '0b'  ORDER BY data DESC");
$conta_attivi = iw_total( $con, 'letture2', "AND data = '$date' AND errore != 'vuoto' AND ( errore = '0b' OR errore = '' ) ORDER BY data DESC");
$conta_tutti = iw_total( $con, 'letture2', "AND data = '$date' ORDER BY data DESC");
//echo var_dump( iw_select($con, "SELECT contatore FROM letture2 WHERE data = '$date' ORDER BY contatore DESC"));

if( isset( $_GET['error'] ) ) { $error = "Numero di contatore non valido"; }

?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>



		
	  <div id="iw_title" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Pannello amministrazione letture</h1>
      </div>
	
	
	<?php if( isset( $error ) ) { ?>
	<div class="alert alert-danger my-4 mt-5" role="alert">
	<span data-feather="alert-circle"></span> &nbsp; <strong>Errore</strong>: <?= $error ?>
	</div>
	<?php } ?>
	

	
	
<form action='contatore.php' method="get" class=" d-block d-md-none mx-2 me-3">  
	<div class="input-group">
		<input type="search" class="form-control border" placeholder="Ricerca contatore" name="id" value="<?= iw_echo( @$_REQUEST['id'] ) ?>" >
		<button class="btn btn-primary px-4" type="submit"  value="Submit"><span data-feather="search"></span></button>
	</div>
	<br>
	</form>
	
<div class="container-fluid px-2">
  <div class="row g-md-4 g-5">
    <div class="col-12 col-md-6">
               <a href="letture.php"  class="card border-info  mx-sm-1 p-3 text-decoration-none iw_card">
              <div class="text-info  text-center mt-3">
                <span data-feather="radio" style="width: 50px; height: 50px;" class="float-start mt-2 ms-3"></span>
                <h4>Apparecchi totali</h4>
                <div class="text-info text-center mt-0">
                <h1><?= $conta_tutti ?></h1>
              </div>
            </div>
          </a>
    </div>
    <div class="col-12 col-md-6"> 
		
             <a href="letture.php" class="card border-success mx-sm-1 p-3 text-decoration-none iw_card">
              <div class="text-success text-center mt-3">
                <span data-feather="battery-charging" style="width: 50px; height: 50px;" class="float-start mt-2 ms-3"></span>
                <h4>Apparecchi attivi</h4>
                <div class="text-success text-center mt-0">
                <h1><?= $conta_attivi ?></h1>
              </div>
            </div>
          </a>
    </div>

	

    <div class="col-12 col-md-6">
                <a href="errori.php"  class="card border-danger mx-sm-1 p-3  text-decoration-none iw_card">
              <div class="text-danger text-center mt-3">
                <span data-feather="alert-octagon" style="width: 50px; height: 50px;" class="float-start mt-2 ms-3"></span>
                <h4>Apparecchi in errore</h4>
             
              <div class="text-danger text-center mt-0">
                <h1 class="errori"><?= $conta_errori ?></h1>
              </div> 
				</div>
            </a>
    </div>
    <div class="col-12 col-md-6">
                 <a href="vuoti.php"  class="card border-warning mx-sm-1 p-3  text-decoration-none iw_card">
              <div class="text-warning text-center mt-3">

                <span data-feather="x-octagon" style="width: 50px; height: 50px;" class="float-start mt-2 ms-3"></span>
                <h4>Apparecchi non ricevuti</h4>
              
              <div class="text-warning text-center mt-0">
                <h1 class="offline"><?= $conta_vuoti ?></h1>
              </div>
				  </div>
            </a>
    </div>
  </div>
</div>
	


		

<p class="text-muted text-center mt-5">Ultimo aggiornamento: <?= date("d.m.Y H:i:s", strtotime($date)); ?></p>

        <!-- Modal -->
        <!--div class="modal fade" id="modalAlert" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Errori presenti</h5>
                
              </div>
              <div class="modal-body">
                Vi sono $errori e $apparecchioffline dati non ricevuti!
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary closemodal" data-dismiss="modal">Chiudi</button>
              </div>
            </div>
          </div>
        </div-->

<? require ( '../inc/tpl/admin_footer.php' ); ?>

	  <script>
    var errori = parseInt($(".errori").text());
    var offline = parseInt($(".offline").text());

    $(document).ready(function () {
      if(errori > 0 || offline > 0 ){
      $('#modalAlert').modal('show')
      }
    });

    $(".closemodal").on('click', function(){
      $('#modalAlert').modal('hide')
    });

    </script>
	
	
</body>
</html>