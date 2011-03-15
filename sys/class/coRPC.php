<?php

class coRPCEx extends coEx {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


// begin static class coRPC
class coRPC {
  public function __construct() { }

  protected function _rpc( $method = NULL, $argv = NULL ) {
    if( ! method_exists( $this, $method ) ) throw new coRPCEx( __CLASS__ . '::' . $method );

    return empty( $argv ) ? $this->$method() : $this->$method( $argv );
  }

  protected function _rpc_test() { return __METHOD__ . ' OK'; }


  public function rpc( $method = NULL, $argv = NULL ) {
    if( empty( $method ) ) throw new coRPCEx( __METHOD__ );

    return $this->_rpc( '_rpc_' . $method, $argv );
  }
}

?>
