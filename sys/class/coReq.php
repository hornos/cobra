<?php

__k_define( 'COBRA_REQUEST_SIZE', 128 );
__k_define( 'COBRA_REQUEST_POST', true );
__k_define( 'COBRA_REQUEST_JSON_OBCLEAN', COBRA_KERNEL_JSON_OBCLEAN );

// begin static class coReq
class coReq {
  public function __construct() { }

  public static function request( $id = NULL, $default = NULL, $post = COBRA_REQUEST_POST, $clear = true, $safe = true, $size = COBRA_REQUEST_SIZE ) {
    if( $post ) {
      $v = ( ! isset($_POST[$id] ) or $_POST[$id] == '' ) ? $default : $_POST[$id];
      if( $clear ) unset( $_POST[$id] );
    }
    else {
      $v = ( ! isset($_REQUEST[$id] ) or $_REQUEST[$id] == '' ) ? $default : $_REQUEST[$id];
      if( $clear ) unset( $_REQUEST[$id] );
    }

    if( $safe && ! empty( $v ) ) $v = coStr::subalnum( $v, $size );

    return $v;
  }

  public static function get( $id = NULL, $default = NULL, $clear = true, $safe = true, $size = COBRA_REQUEST_SIZE ) {
    return self::request( $id, $default, false, $clear, $safe, $size );
  }

  public static function request_unsafe( $id = NULL, $default = NULL, $post = COBRA_REQUEST_POST, $clear = true ) {
    return self::request( $id, $default, $post, $clear, false );
  }

  public static function jrequest( $id = NULL, $default = NULL, $size = COBRA_REQUEST_SIZE ) {
    return json_decode( self::request( $id, $default, true, true, true, $size ) );
  }

  public static function json( $obj = NULL, $obclean = COBRA_REQUEST_JSON_OBCLEAN ) {
    if( $obclean ) __k_ob_clean();

    echo json_encode( $obj );
  }

  public static function jresponse( $data = NULL, $type = 'return', $sleeptime = 0 ) {
    if( $sleeptime > 0 ) sleep( $sleeptime );

    return self::json( array( 'type' => $type, 'data' => $data ) );
  }

  public static function jexception( $e = NULL ) {
    return self::jresponse( $e->getMessage(), 'exception' );
  }
}

?>
