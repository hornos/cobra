<?php
// begin header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'COBRA_BOOTSTRAP' );
require_once( $bootstrap );
// end header

$cosys = __k_cache_fetch();
$codb = new coDB( $cosys['sys.config'] );

__k_print( 'PHP Time' );
__k_print( 'time: ' . $codb->time() );
__k_print( 'microtime: ' . $codb->microtime() );
__k_print( 'timestamp: ' . $codb->timestamp() );
__k_print( 'DB Time' );
$codb->Connect();
__k_print( 'time: ' . $codb->time() );
__k_print( 'microtime: ' . $codb->microtime() );
__k_print( 'timestamp: ' . $codb->timestamp() );
$codb->Disconnect();

?>
