<?php

use MaxMind\Db\Reader;

class iw_client {
	
	public static $data;
	
	public static function get ( ) {
		 
		self::$data[ 'ip' ] = self::ip();

		self::$data[ 'lang' ] = @$_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] != '' ? substr( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ], 0, 2 ) : '';
		
		self::$data[ 'useragent' ] = @$_SERVER[ 'HTTP_USER_AGENT' ] != '' ? $_SERVER[ 'HTTP_USER_AGENT' ] : '';	
		
		self::$data[ 'page' ] = @$_SERVER[ 'REQUEST_URI' ] != '' ? $_SERVER[ 'REQUEST_URI' ] : ''; // basename( $_SERVER[ 'REQUEST_URI' ] )

		self::$data[ 'ref' ] = @$_SERVER[ 'HTTP_REFERER' ] != '' ? $_SERVER[ 'HTTP_REFERER' ] : '';
		
		self::$data[ 'browser' ] = self::browser( self::$data[ 'useragent' ] );
		
		self::$data[ 'os' ] = self::os( self::$data[ 'useragent' ] );

		self::$data[ 'country' ] = '';

		return self::$data;
	}
	
	public static function ip ( ) {
		
		if( @$_SERVER[ 'HTTP_CF_CONNECTING_IP' ] != '' ) { return $_SERVER['HTTP_CF_CONNECTING_IP']; }
        
		elseif( @$_SERVER[ 'HTTP_CLIENT_IP' ] != '' ) { return $_SERVER[ 'HTTP_CLIENT_IP' ]; }
		
		elseif( @$_SERVER[ 'HTTP_X_FORWARDED_FOR' ] != '' ){ return $_SERVER[ 'HTTP_X_FORWARDED_FOR' ]; }
		
		elseif( @$_SERVER[ 'REMOTE_ADDR' ] != '' ) { return $_SERVER[ 'REMOTE_ADDR' ]; }
	}

	public static function browser ( $useragent ) {
		
		$browser = array( '/msie|trident/i' => 'i', '/firefox/i' => 'f', '/chrome/i' => 'c', '/safari/i' => 's', '/edge/i' => 'e', '/opera|opr/i' => 'o' ); 
		
		foreach( $browser as $regex => $name ) { 
			
			if( preg_match( $regex, $useragent ) ) { return $name; }
		}
	}
	
	public static function os ( $useragent ) {
		
		$os = array( '/win/i' => 'w', '/mac/i' => 'm', '/linux|ubuntu/i' => 'l', '/iphone|ipad|ipod/i' => 'i', '/android/i' => 'a' );
		
		foreach( $os as $regex => $name ) { 
			
			if( preg_match( $regex, $useragent ) ) { return $name; }
		}
	}

	public static function country ( $ip ) {
		
		if( !filter_var( $ip, FILTER_VALIDATE_IP ) ) { return ''; }
		
		$reader = new Reader( IW_INC_PATH . '/lib/maxmind/GeoLite2-Country.mmdb' );

		$res = $reader->get( $ip );

		$reader->close();
	
		return isset( $res[ 'country' ][ 'iso_code' ] ) ? strtolower( $res[ 'country' ][ 'iso_code' ] ) : '';
	}
}