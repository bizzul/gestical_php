<?php 

// page speed
$iw_timestart = microtime(true);

// exclude $_COOKIE
$_REQUEST = array_merge( $_GET, $_POST );

// include files
define( 'IW_INC_PATH',  dirname( dirname( __FILE__ ) ) );
require( IW_INC_PATH . '/lib/maxmind/autoload.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/data.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/functions.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/client.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/mysql.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/login.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/gridOLD.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/grid.php' );
require( IW_INC_PATH . '/lib/ipelweb/php/search.php' );

// connect to db
$con = mysqli_connect( IW_MYSQL_HOST, IW_MYSQL_USER, IW_MYSQL_PASS, IW_MYSQL_DB )
	or die( 'Error: Database connection failed' );

// set charset
mysqli_set_charset( $con, "utf8mb4" ) or die( 'Error: character set failed' );
header( 'Content-Type: text/html; charset=utf-8' );
mb_internal_encoding( 'UTF-8' );

// datetime
date_default_timezone_set( $stg['site_timezone'] );
define( 'IW_NOW', date ( "Y-m-d H:i:s" , time() ) );

// compress
ob_start("ob_gzhandler");

// cache
if( defined( 'IW_NOCACHE' ) ) {
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
	header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
	header( 'Cache-Control: post-check=0, pre-check=0', false ); 
	header( 'Pragma: no-cache' );
}

// session
if( !defined( 'IW_NOSESSION' ) ) {
	
	// get user data
	$client = iw_client::get(); 
	if( !filter_var( $client['ip'], FILTER_VALIDATE_IP ) ) { die( 'Error: Invalid IP address' ); }
	$client['country'] = iw_client::country( $client['ip'] ); 
	
	// session
	session_set_cookie_params( 0, '/', IW_DOMAIN, IW_HTTPS, 1 );
	session_start();
	
	// csrf
	if( !isset( $_SESSION[ 'iw_csrf' ] ) ) { $_SESSION[ 'iw_csrf' ] = md5( uniqid( rand(), true ) ); }
	if( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && ( !isset( $_POST[ 'csrf' ] ) || $_POST[ 'csrf' ] != $_SESSION[ 'iw_csrf' ] ) ) { die( 'Error: Session expired' ); }
	
	// failed logins ban
	if( iw_login_ban( $con, 'iw_attempts', 5 ) ) { die( 'Error: Too many login attempts, please wait a few minutes' ); }
}

?>