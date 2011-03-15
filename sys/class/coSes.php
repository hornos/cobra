<?php

__k_define( 'COBRA_SESSION_ID_SIZE', 128 );
__k_define( 'COBRA_SESSION_DEBUG_LEVEL', 9 );
__k_define( 'COBRA_SESSION_ID_ON_CLIENT', true );
__k_define( 'COBRA_SESSION_STORE_EXTINFO', false );

class coSesEx extends coEx {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


class coSes extends coDB {
  // time cache
  protected $_time;
  protected $_microtime;

  public function __construct( $config = NULL ) {
    parent::__construct( $config );
    $this->_time      = time();
    $this->_microtime = microtime();

    // register session handlers
    session_set_save_handler( 
      array( &$this, 'open' ), 
      array( &$this, 'close' ),
      array( &$this, 'read' ),
      array( &$this, 'write' ),
      array( &$this, 'destroy' ),
      array( &$this, 'gc' )
    );
  } // end construct

  // begin sid
  protected function _sid( $sid = NULL ) {
    return __k_safe_str( $sid, COBRA_SESSION_ID_SIZE );
  }
  // end sid


  // begin profile access
  // default session expiration time
  protected function _expiration() {
    return $this['session.expiration'];
  }

  // get session encryption key
  protected function _session_key() {
    $key = $this['session.key'];
    if( empty( $key ) ) throw new coSesEx( __METHOD__ );

    return $key;
  }

  // if strict check is on client ip and http user agent is stored and checked
  protected function _strict_client_check() {
    return $this['session.strict_client_check'];
  }

  protected function _session_name() {
    return $this['session.name'];
  }

  // key regeneration time
  protected function _id_expiration() {
    return $this['session.id_expiration'];
  }
  // end access profile


  // begin cookie
  public function cookie_destroy() {
    // outdate the cookie
    if( session_id() != "" || isset( $_COOKIE[session_name()] ) ) {
      setcookie( session_name(), '', 0 );
    }
  }
  // end cookie


  // begin session handlers
  // Open the session
  public function open( $save_path = NULL, $session_name = NULL ) {
    /*! Session interface */
    global $sess_save_path;
    $sess_save_path = $save_path;
    return true;
  }

  // Close the session
  public function close() {
    /*! Session interface */
    try {
      $this->Disconnect();
    } catch( Exception $e ) {
      __k_debug( __METHOD__ . '::' . $e->getMessage(), COBRA_SESSION_DEBUG_LEVEL );
      return false;
    }
    return true;
  }

  // Read session data
  public function read( $sid = NULL ) {
    /*! Session interface */
    try {
      $result = $this->CallProcedure( COBRA_DB_TABLEFUNCTION_PREFIX . 'session_read', array( $this->_sid( $sid ) ) );
    } catch( Exception $e ) {
      __k_debug( __METHOD__ . '::' . $e->getMessage(), COBRA_SESSION_DEBUG_LEVEL );
      return false;
    }
    return $result;
  }

  // Write session data
  // TODO: explicit client and host proxy ip write
  public function write( $sid = NULL, $data = NULL ) {
    /*! Session interface */
    $sid     = $this->_sid( $sid );
    $expires = $this->_expiration();
    $store = COBRA_STORE_EXTINFO ? array( $sid, $expires, $data, $client_ip, $server_ip ) : array( $sid, $expires, $data );
    try {
      $this->CallProcedure( COBRA_DB_TABLEFUNCTION_PREFIX . 'session_write', );
    } catch( Exception $e ) {
      __k_debug( __METHOD__ . '::' . $e->getMessage(), COBRA_SESSION_DEBUG_LEVEL );
      return false;
    }
    return true;
  }

  // Destroy session data
  public function destroy( $sid = NULL ) {
    /*! Session interface */
    $sid = $this->_sid( $sid );
    $this->cookie_destroy();
    try {
      $this->CallProcedure( COBRA_DB_TABLEFUNCTION_PREFIX . 'session_destroy', array( $sid ) );
    } catch( Exception $e ) {
      __k_debug( __METHOD__ . '::' . $e->getMessage(), COBRA_SESSION_DEBUG_LEVEL );
      return false;
    }
    return true;
  }

  // Garbage collector
  public function gc() {
    /*! Session interface */
    return true;
  }
  // end session handlers


  // begin session
  protected function _remote_addr() {
    return $_SERVER['REMOTE_ADDR'];
  }

  // TODO: handle proxy connection
  protected function _server_addr() {
    return $_SERVER['SERVER_ADDR'];
  }

  protected function _http_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'];
  }


  // user must contain a time field for session expiration for logout scripts
  protected function _expired() {
    $sid = session_id();
    try {
      $result = $this->CallProcedure( COBRA_DB_TABLEFUNCTION_PREFIX . 'session_expired', array( $sid ) );
    } catch( Exception $e ) {
      throw new coSesEx( __METHOD__ );
    }
    return $result;
  }

  // TODO: add salt
  // TODO: on server
  protected function _generate_id() {
    if( COBRA_SESSION_ID_ON_CLIENT ) {
      return sha1( uniqid( $this->_microtime ).$this->_remote_addr().$this->_http_user_agent() );
    }
    else {
      return sha1( uniqid( $this->_microtime ).$this->_remote_addr().$this->_http_user_agent() );
    }
  }



  //
  // Cookies
  //
  protected function _renew_cookie() {
    $expires = $this->_time + $this->_expiration();
    unset( $_COOKIE[session_name()] );
	setcookie( session_name(), session_id(), $expires );  
  }


  //
  // Session
  //
  protected function _change_id() {
    $sid     = session_id();
	$new_sid = $this->_generate_id();
	
	try {
	  $result = $this->CallProcedure( 'sessions_change_id', array( $sid, $new_sid ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
      return false;
	}
	session_id( $new_sid );
	$this->_renew_cookie();
	$this->save( 'last_id_change_time', $this->_time );
	// coDebug::message( 'New session id: '.$new_sid );
	return true;
  }


  protected function _check_change_id() {
    if( $this->_id_expiration() == 0 )
      return true;
	$delta_time = $this->_time - $this->load( 'last_id_change_time' );
	if( $delta_time > $this->_id_expiration() ) {
	  return $this->_change_id();
	}
	return false;
  }


  //
  // Strict Client Check
  //  
  protected function _check_client_ip() {
 	if( $this->load( 'ip_address' ) != $this->_remote_addr() ) {
	  throw new coSessionException( __METHOD__ );
	}
	return true;
  }


  protected function _check_client_user_agent() {
	if( $this->load( 'user_agent' ) != $this->_http_user_agent() ) {
	  throw new coSessionException( __METHOD__ );
	}
	return true;
  }
  
  
  //
  // Low Level Read and Write Session Data
  //
  protected function _set( $id, $val ) {
    $_SESSION[$id] = $val;
	return true;
  }


  protected function _del( $id ) {
    if( isset( $_SESSION[$id] ) ) {
      unset( $_SESSION[$id] );
	  return true;
	}
	throw new coSessionException( __METHOD__ );
  }


  protected function _get( $id ) {
    if( isset( $_SESSION[$id] ) ) return $_SESSION[$id];

	throw new coSessionException( __METHOD__ . ' ' . $id );
  }


  //
  // Top Level Read and Write Session Data with Encryption
  //  
  public function save( $id, $data = false ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  // store data unencrypted
	  return $this->_set( $id, serialize( $data ) );
	}
	// store data encrypted
	$id   = coCrypt::encrypt( serialize( $id ), $key );
	$data = coCrypt::encrypt( serialize( $data ), $key );
	return $this->_set( $id, $data );
  }


  public function load( $id ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  // load data unencrypted
	  $result = unserialize( $this->_get( $id ) );
	  return $result;
	}
	// load data encrypted
	$id   = coCrypt::encrypt( serialize( $id ), $key );
	$data = coCrypt::decrypt( $this->_get( $id ), $key );
	$result = unserialize( $data );
	return $result;
  }


  public function erase( $id ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  return $this->_del( $id );
    }
	$id = coCrypt::encrypt( serialize( $id ), $key );
	return $this->_del( $id );	
  }
  
  
  //
  // Start and Stop the Cobra Session
  //

  public function start() {
    // connect to the db
    $this->Connect();
	// get the time
	$this->_time      = $this->time();
	$this->_microtime = $this->microtime();
	
	// set session name in the cookie
    session_name( $this->_session_name() );
	// start or continue the session
    session_start();

    try {
	  $this->_expired();
	} catch( Exception $e ) {
	  // coDebug::message( 'Start a new session' );
	  // expired or no such sessions therefore
	  // start a new session, generate and set the session id
	  session_id( $this->_generate_id() );
	  // store session data
	  $this->save( 'last_id_change_time', $this->_time );

      // store date for strict session checking
      if( $this->_strict_client_check() ) {	  
	    $this->save( 'ip_address', $this->_remote_addr() );
	    $this->save( 'user_agent', $this->_http_user_agent() );
	  }
	  // reset session cookie id and time
	  $this->_renew_cookie();
	  return true;
	}

    // continue an old session
	// coDebug::message( 'Continue old session' );
	if( $this->_strict_client_check() ) {
      // check ip address
	  $this->_check_client_ip();	  
	  // check user agent
	  $this->_check_client_user_agent();
    }
	// if needed regenerate session id
	$this->_check_change_id();
	return true;
  } // end start


  public function stop( $start_session = true ) {
    // connect to the db
    $this->Connect();	
	// set session name in the cookie
    session_name( $this->_session_name() );
	// start or continue the session
	
    if( $start_session ) session_start();

    try {
	  $this->_expired();
	} catch( Exception $e ) {
      session_destroy();
      return false;
    }
    return session_destroy();
  }  

} // end coSession

?>
