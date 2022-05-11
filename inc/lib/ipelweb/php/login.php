<?php

define( 'IW_LOGIN_DAYS', 30 );
define( 'IW_LOGIN_OLDPW', 0 );
define( 'IW_LOGIN_STATUS', 'status' );
define( 'IW_LOGIN_HISTORY', 'iw_logins' );
define( 'IW_LOGIN_COL', array( 'email', 'passkey', 'authkey', 'login_ip' ) );
if( !defined( 'IW_SALTKEY' ) ) { define( 'IW_SALTKEY', '' ); }

function iw_password( $string ) {
	
	if( defined( 'IW_LOGIN_OLDPW' ) && IW_LOGIN_OLDPW ) { return md5( sha1( md5( IW_SALTKEY . $string ) ) ); }
	
	return hash( 'sha256', IW_SALTKEY . $string );
}

function iw_login ( $con, $table, $username, $password, $cookie = 0, $keyname = 'login', $role = 0 ) {

	if( session_status() == PHP_SESSION_NONE ) { die( 'Error PHP session not started' ); }
	
	session_regenerate_id( true ); // session fixation
		
	$username = iw_secure( $con, $username );

	$passkey = iw_password( $password );

	if( empty( $username ) || empty( $password ) ) { return false; }

	elseif( !$usr = iw_row( $con, $table, IW_LOGIN_COL[0] . " = '$username' AND " . IW_LOGIN_COL[1] . " = '$passkey'" ) ) { return false; }

	elseif( defined( 'IW_LOGIN_STATUS' ) && IW_LOGIN_STATUS != '' && $usr[ IW_LOGIN_STATUS ] != 1 ) { return false; }

	else {

		$authkey = iw_unique( $con, $table, IW_LOGIN_COL[2], 64 );

		iw_update( $con, $table, array( IW_LOGIN_COL[2] => $authkey, IW_LOGIN_COL[3] => iw_secure( $con, iw_client::ip() ) ), $usr['id'] );

		$_SESSION[ $keyname ] = iw_password( $authkey );

		if( $cookie ) { setcookie( $keyname, iw_password( $authkey ), time() + ( 86400 * IW_LOGIN_DAYS ), '/', IW_DOMAIN, IW_HTTPS, 1 ); }

		if( defined( 'IW_LOGIN_HISTORY' ) && IW_LOGIN_HISTORY != '' ) { iw_login_history( $con, IW_LOGIN_HISTORY, $usr['id'], $role ); }
		
		return $usr['id']; //if( $redirect ) { die( "<script>window.top.location.href='" . $redirect . "';</script>" ); } 
	}
}

function iw_login_check ( $con, $table, $keyname = 'login', $role = 0 ) { // , $redirect = '?notlogged'
	
	if( session_status() == PHP_SESSION_NONE ) { die( 'Error PHP session not started' ); }

	if( isset( $_SESSION[ $keyname ] ) && $_SESSION[ $keyname ] != '' ) { $authkey = iw_secure( $con, $_SESSION[ $keyname ] ); }

	elseif( isset( $_COOKIE[ $keyname ] ) && $_COOKIE[ $keyname ] != '' ) { $authkey = iw_secure( $con, $_COOKIE[ $keyname ] ); } 

	else { return false; } // header( "location: $redirect" ); exit; }

	$sql = "SHA2( CONCAT( '" . IW_SALTKEY . "', " . IW_LOGIN_COL[2] . " ), 0 ) = '$authkey' AND " . IW_LOGIN_COL[3] . " = '" . iw_secure( $con, iw_client::ip() ). "' ";
	
	if( defined( 'IW_LOGIN_STATUS' ) && IW_LOGIN_STATUS != '' ) { $sql .= "AND " . IW_LOGIN_STATUS . " = 1"; }

	if( !$usr = iw_row( $con, $table, $sql ) ) { return false; } // header( "location: $redirect" ); exit; }

	if( !isset( $_SESSION[ $keyname ] ) ) { 

		session_regenerate_id( true ); // session fixation

		$_SESSION[ $keyname ] = iw_password( $usr['authkey'] );
		
		if( defined( 'IW_LOGIN_HISTORY' ) && IW_LOGIN_HISTORY != '' ) { iw_login_history( $con, IW_LOGIN_HISTORY, $usr['id'], $role ); }
	}

	if( defined( 'IW_LOGIN_HISTORY' ) && IW_LOGIN_HISTORY != '' ) { 
		
		iw_update( $con, IW_LOGIN_HISTORY, array( 'request' => IW_NOW, 'pageview' => '++' ), "uid = '$usr[id]' AND role = '$role' ORDER BY login DESC" );
	}
	
	unset( $usr[ IW_LOGIN_COL[1] ], $usr[ IW_LOGIN_COL[2] ] );

	return $usr;
}

function iw_logout ( $con, $table, $keyname = 'login', $role = 0 ) { // , $redirect = '?loggedout'
	
	if( session_status() == PHP_SESSION_NONE ) { die( 'Error PHP session not started' ); }

	if( isset( $_SESSION[ $keyname ] ) ) {

		$sql = "SHA2( CONCAT( '" . IW_SALTKEY . "', " . IW_LOGIN_COL[2] . " ), 0 ) = '" . iw_secure( $con, $_SESSION[ $keyname ] ) . "' ";
		
		if( $usr = iw_row( $con, $table, $sql ) ) {

			$authkey = iw_unique( $con, $table, IW_LOGIN_COL[2], 64 );

			iw_update( $con, $table, array( IW_LOGIN_COL[2] => $authkey ), $usr['id'] );
			
			if( defined( 'IW_LOGIN_HISTORY' ) && IW_LOGIN_HISTORY != '' ) {
				
				iw_update( $con, IW_LOGIN_HISTORY, array( 'logout' => IW_NOW ), "uid = '$usr[id]' AND role = '$role' ORDER BY login DESC" ); 
			}
		}
		
		$_SESSION[ $keyname ] = ''; unset( $_SESSION[ $keyname ] );
	}
	
	if( isset( $_COOKIE[ $keyname ] ) ) { setcookie( $keyname, "", time() - 3600, '/', IW_DOMAIN, IW_HTTPS, 1 ); unset( $_COOKIE[ $keyname ] ); }
	
	return true;//if( $redirect ) { header( "location: $redirect" ); exit; } 
}

function iw_login_history ( $con, $table, $uid, $role = 0 ) {

	$client = iw_client::get();

	$data = array( 'login' => IW_NOW, 'uid' => $uid, 'role' => $role, 'country' => iw_client::country( $client['ip'] ) );

	foreach( array( 'ip', 'lang', 'os', 'browser', 'useragent' ) AS $key ) { $data[ $key ] = $client[ $key ]; }

	iw_insert( $con, $table, iw_secure( $con, $data ) ); 
}	

function iw_login_attempt ( $con, $table, $role = 0, $email = NULL, $password = NULL ) {

	$client = iw_client::get();

	$data = array( 'date' => IW_NOW, 'role' => $role, 'email' => $email, 'password' => $password, 'country' => iw_client::country( $client['ip'] ) );

	foreach( array( 'ip', 'lang', 'os', 'browser', 'useragent' ) AS $key ) { $data[ $key ] = $client[ $key ]; }

	iw_insert( $con, $table, iw_secure( $con, $data ) ); 
}

function iw_login_ban ( $con, $table, $max = 5 ) {
	
	$data = array( 'ip' => iw_secure( $con, iw_client::ip() ), 'now' => iw_secure( $con, substr( IW_NOW, 0, 13 ) ) );

	$check = iw_total( $con, $table, "AND ip = '$data[ip]' AND DATE_FORMAT( date, '%Y-%m-%d %H' ) = '$data[now]'" );

	if( $check > $max ) { return true; }

}