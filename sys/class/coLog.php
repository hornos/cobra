<?php

class coLog {
  private $__logfile;

  public function __construct( $logfile = NULL ) {
    if( is_null( $logfile ) ) throw new coEx( __METHOD__ );

    $cobra = __k_cache_fetch();
    $this->__logfile = $cobra['path.log'] . '/' . basename( $logfile ) . '.log';
  }

  public function record( $str = '', $append = true ) {
    return file_put_contents( $this->__logfile, $str, $append ? FILE_APPEND | FILE_TEXT : FILE_TEXT );
  }
}

?>
