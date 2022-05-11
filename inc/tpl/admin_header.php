<header class="navbar navbar-light sticky-top flex-md-nowrap p-0 shadow bg-white border-bottom">

	<a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="index.php"><img  src="../inc/img/logo_gestical.png" alt="" srcset=""></a>

	<button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
	<span class="navbar-toggler-icon"></span>
	</button>
	
	<div class="d-none d-lg-flex w-100 ">

	<form action='contatore.php' method="get" class="w-100 me-5 pe-5 ms-2">  
	<div class="input-group">
		<input type="search" class="form-control border" placeholder="Ricerca contatore" name="id" value="<?= iw_echo( @$_REQUEST['id'] ) ?>" >
		<button class="btn btn-primary px-4" type="submit"  value="Submit"><span data-feather="search"></span></button>
	</div>
	</form>

	<ul class="navbar-nav px-3 mt-1 d-flex flex-row">
	<li class="nav-item text-nowrap"><a class="nav-link me-5" href='account.php' ><span data-feather="user"></span>&nbsp; <?= $usr['name'] ?></a></li>
	<li class="nav-item text-nowrap"><a class="nav-link me-5" href="login.php?logout"><span data-feather="power"></span>&nbsp; Esci</a></li>
	<li class="nav-item text-nowrap">
		<button class="navbar-toggler iw_hamburger" type="button" onClick="toggle_desktop_menu()"><span class="navbar-toggler-icon"></span></button>
	</li>
	</ul>
		
	</div>
	
</header>

<div class="container-fluid">
<div class="row">
	
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
		 
		<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 text-muted">
          <span>Contatori </span> <span data-feather="chevron-down"></span>
        </h6>
		  
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" aria-current="page" href="index.php"><span data-feather="home"></span>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="condomini.php"><span data-feather="folder"></span>Strutture</a></li>
          <li class="nav-item"><a class="nav-link" href="letture.php"><span data-feather="pie-chart"></span>Letture </a></li>
          <li class="nav-item"><a class="nav-link" href="errori.php"><span data-feather="alert-triangle"></span>Errori</a></li>
		<li class="nav-item"><a class="nav-link mt-2" href="lora.php"><span data-feather="activity"></span>Lora</a></li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 text-muted">
          <span>Account</span> <span data-feather="chevron-down"></span>
        </h6>

        <ul class="nav flex-column mb-2">
          <li class="nav-item"><a class="nav-link" href="account.php"><span data-feather="user"></span>Profilo</a></li>
		  <li class="nav-item d-md-block d-lg-none"><a class="nav-link" href="login.php?logout"><span data-feather="power"></span>Esci</a></li>
        </ul>
		  
    </div>
    </nav>

	<main id="iw_main" class="col-md-9 ms-sm-auto col-lg-10 p-0">
	<div id="iw_wrapper1">