<?php
/// \file k.php
/// \brief Cobra kernel

// Cache Array
$__k_cache_arr = array();

// basic exception class
class coEx extends Exception {
  public function __construct( $e = NULL ) {
    parent::__construct( empty( $e ) ? __METHOD__ : $e );
  }
}


// begin class coCobra
class coCobra implements ArrayAccess {
  private $__cobra = NULL;

  public function __construct( $cobra = NULL, $config = NULL ) {
    if( empty( $cobra ) || empty( $config ) ) throw new coEx( __METHOD__ );
    // config & cache
    $this->__cobra = $cobra;
    $this->config( $config );
    $this->cache( COBRA_MAIN_ID, $this->__cobra['path.class'], true );
    // boot
    if( ! empty( $config['app.boot'] ) ) {
      foreach( $config['app.boot'] as $app ) {
        $this->boot( $app );
      }
    }
  }


  //// begin ArrayAccess interface
  public function offsetSet( $offset, $value ) {
    $this->__cobra[$offset] = $value;
  }

  public function offsetExists( $offset ) {
    return isset( $this->__cobra[$offset] );
  }

  public function offsetUnset( $offset ) {
    unset( $this->__cobra[$offset] );
  }

  public function offsetGet( $offset ) {
    if( isset( $this->__cobra[$offset] ) ) return $this->__cobra[$offset];

    throw new coEx( __METHOD__ );
  }
  //// end ArrayAccess interface


  public function config( $config = NULL ) {
    if( empty( $config ) ) throw new coEx( __METHOD__ );

    if( empty( $this->__cobra['sys.config'] ) )
      $this->__cobra['sys.config'] = $config;
    else
      $this->__cobra['sys.config'] = array_merge( $this->__cobra['sys.config'], $config );
    return true;
  }


  public function cache( $cache_id = NULL, $cache_path = NULL, $recursive = true ) {
    if( empty( $cache_id ) || empty( $cache_path ) )
      throw new coEx( __METHOD__ . '::' . $cache_id . '::' . $cache_path );

    $cache_arr = array( $cache_id => array( 'path' => $cache_path, 'recursive' => $recursive ) );
    if( isset( $this->__cobra['sys.cache'] ) )
      $this->__cobra['sys.cache'] = array_merge( $this->__cobra['sys.cache'], $cache_arr );
    else
      $this->__cobra['sys.cache'] = $cache_arr;
    return true;
  }


  function boot( $app_id = NULL ) {
    if( empty( $app_id ) || ! isset( $this->__cobra['path.app'] ) )
      throw new coEx( __METHOD__ . '::' . $app_id );

    $app_id = __k_safe_str( $app_id );
    $app_bootstrap = $this->__cobra['path.app'] . '/' . $app_id . '/' . COBRA_APP_BOOTSTRAP;
    if( ! is_readable( $app_bootstrap ) )
      throw new coEx( __METHOD__ . '::' . $app_id . '::' . COBRA_APP_BOOTSTRAP );

    require_once( $app_bootstrap );

    $app_sys = isset( ${$app_id} ) ? ${$app_id} : array();
    $app_cfg = 'config';
    $app_arr = array( $app_id => array( 'sys.app' => $app_sys,
                                        'sys.config' => isset( ${$app_cfg} ) ? ${$app_cfg} : array() ) );
    if( isset( $this->__cobra['sys.apps'] ) )
      $this->__cobra['sys.apps'] = array_merge( $app_arr, $this->__cobra['sys.apps'] );
    else
      $this->__cobra['sys.apps'] = $app_arr;

    // set cache
    if( isset( $app_sys['path.class'] ) )
      $this->cache( $app_id, $app_sys['path.class'], true );

    return true;
  }
} // end class coCobra



//// cobra kernel functions
function __k_define( $id = NULL, $v = NULL ) {
  return defined( $id ) ? false : define( $id, $v );
}

// begin Output buffering
function __k_ob_flush() {
  if( COBRA_OB && ! COBRA_CLI ) ob_flush();
}

function __k_ob_start() {
  if( COBRA_OB && ! COBRA_CLI ) {
    if( ! ob_start( "ob_gzhandler" ) ) ob_start();
  }
}

function __k_ob_clean() {
  if( COBRA_OB && ! COBRA_CLI ) ob_end_clean();
}

function __k_ob_restart() {
  cobra_ob_clean();
  cobra_ob_start();
}
// end Output buffering


// begin Output & Strings
function __k_encoding( $enc = 'UTF-8' ) {
  if( COBRA_MB ) {
    mb_internal_encoding( $enc );
    mb_regex_encoding( $enc );
  }
}

function __k_safe_str( $str = NULL, $size = COBRA_KERNEL_STRING_SIZE ) {
  if( COBRA_MB )
    return mb_substr( mb_ereg_replace( COBRA_KERNEL_STRING_REGEXP, '', $str ), 0, $size );

  return substr( ereg_replace( COBRA_KERNEL_STRING_REGEXP, '', $str ), 0, $size );
}


function __k_print( $str = '', $obclean = true ) {
  if( $obclean ) __k_ob_clean();

  echo $str . COBRA_EOL;
}


function __k_json( $str = '', $type = 'exception', $obclean = COBRA_KERNEL_JSON_OBCLEAN ) {
  if( $obclean ) __k_ob_clean();

  echo json_encode( array( 'type' => $type, 'data' => $str ) );
}

function __k_debug( $msg = '', $level = 9, $clean = true ) {
  if( $level >= COBRA_DEBUG ) {
    if( COBRA_CLI ) {
      __k_print( $msg );
    }
    else {
      __k_json( $msg );
    }
  }
}
// end Output & Strings


// begin Cache
function __k_cache_fetch( $id = COBRA_MAIN_ID ) {
  global $__k_cache_arr;

  if( empty( $id ) || ! isset( $__k_cache_arr[$id] ) )
    throw new coEx( __FUNCTION__ . '::' . $id );

  return $__k_cache_arr[$id];
}


function __k_cache_store( $id = NULL, $var = NULL ) {
  global $__k_cache_arr;

  if( empty( $id ) || empty( $var ) ) throw new coEx( __FUNCTION__ );

  $__k_cache_arr[$id] = $var;
  if( isset( $var['sys.cache'] ) ) {
    foreach( $var['sys.cache'] as $cache => $desc ) {
      $cache_file = $cache . '.' . COBRA_CACHE_EXTENSION;
      $cache_path = $var['path.cache'] . '/' . $cache_file;
      if( is_readable( $cache_path ) )
        $__k_cache_arr[$cache_file] = file( $cache_path );
    }
  }
  return true;
}


function __k_class_autoload( $class = NULL, $id = COBRA_MAIN_CACHE_ID ) {
  $cache = __k_cache_fetch( $id . '.' . COBRA_CACHE_EXTENSION );
  $cache = unserialize( $cache[0] );

  if( ! isset( $cache[$class] ) ) throw new coEx( __FUNCTION__ . '::' . $class . '::' . $id );

  return require_once( $cache[$class] );
}


function __k_autoload( $class = NULL, $id = COBRA_MAIN_CACHE_ID, $recursive = COBRA_RECURSIVE_CACHE ) {
  $class = __k_safe_str( $class );
  $id    = __k_safe_str( $id );

  try {
    return __k_class_autoload( $class, $id );
  } catch( Exception $e ) {
    if( ! $recursive ) throw $e;

    $cobra = __k_cache_fetch( COBRA_MAIN_ID );
    foreach( $cobra['sys.cache'] as $id => $desc ) {
      try {
        return __k_class_autoload( $class, $id );
      } catch( Exception $e ) {}
    }
    throw new coEx( __FUNCTION__ . '::' . $class . '::' . $id );
  }
}


function __autoload( $class = NULL ) {
  try {
    return __k_autoload( $class );
  } catch( Exception $e ) {
    return false;
  }
}
// end Cache


// begin Exception handling
function __k_die( $str = '', $exit = 1, $obclean = true ) {
  if( COBRA_CLI ) {
    __k_print( $str, $obclean );
  }
  else {
    $cobra  = __k_cache_fetch();
    $error_html = $cobra['path.error'] . '/' . __k_safe_str( $str ) . COBRA_ERROR_EXTENSION;
    if( is_readable( $error_html ) ) {
      readfile( $error_html );
    }
    else {
      __k_json( $str, $obclean );
    }
  }
  exit( $exit );
}


function __k_exception_handler( $e = NULL ) {
  __k_die( $e->getMessage() );
}
// end Exception handling


// begin Kernel init
function __k_init( $encoding = 'UTF-8', $error_handler = NULL ) {
  __k_define( 'COBRA_MAIN_ID', 'cobra' );
  __k_define( 'COBRA_KERNEL_STRING_SIZE', 64 );
  __k_define( 'COBRA_KERNEL_STRING_REGEXP', '[^[:alpha:]_.-]' );
  __k_define( 'COBRA_KERNEL_JSON_OBCLEAN', false );
  __k_define( 'COBRA_MAIN_CACHE_ID', 'cobra' );
  __k_define( 'COBRA_RECURSIVE_CACHE', false );
  __k_define( 'COBRA_CLASS_EXTENSION', 'php' );
  __k_define( 'COBRA_APP_BOOTSTRAP', 'bs.php' );
  __k_define( 'COBRA_CACHE_EXTENSION', 'cache' );
  __k_define( 'COBRA_ERROR_EXTENSION', 'html' );
  __k_define( 'COBRA_DEBUG', 9 );
  __k_define( 'COBRA_CLI', ( PHP_SAPI == 'cli' ? true : false ) );
  __k_define( 'COBRA_OB', true );
  __k_define( 'COBRA_EOL', COBRA_CLI ? PHP_EOL : '<br>'.PHP_EOL );
  __k_define( 'COBRA_MB', extension_loaded( 'mbstring' ) );
  // Time
  __k_define( 'COBRA_DEFAULT_TIMEZONE', 'CET' );
  date_default_timezone_set( COBRA_DEFAULT_TIMEZONE );

  __k_ob_start();
  __k_encoding( $encoding );

  // exception handling
  if( ! empty( $error_handler ) ) set_exception_handler( $error_handler );

  return true;
}
// end Kernel init
?>
