#!/usr/bin/php -q
<?php
// begin header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'COBRA_BOOTSTRAP' );
require_once( $bootstrap );
// end header


function __c_cache_dir( $root = NULL, $recursive = true ) {
  $cache = array();

  if( empty( $root ) ) return false;

  switch( $recursive ) {
    case true:
      foreach( glob( $root . '/' . '*', GLOB_ONLYDIR ) as $dir ) {
        $cache = array_merge( $cache, __c_cache_dir( $dir, $recursive ) );
      }
    // end case

    case false:
      foreach( glob( $root . '/' . '*.' . COBRA_CLASS_EXTENSION ) as $file ) {
        if( is_readable( $file ) ) {
          $basename  = basename( $file );
          $classname = str_replace( '.' . COBRA_CLASS_EXTENSION, '', $basename );
          $cache = array_merge( $cache, array( $classname => $file ) );
        }
      }
      break;
    // end case
  }
  return $cache;
} // end


function __c_cache( $root = NULL, $cache_file = NULL, $recursive = true, $verbose = true ) {
  $cache = __c_cache_dir( $root, $recursive );
  if( empty( $cache ) ) return false;  

  if( $verbose ) {
    print_r( $cache );
  }
  
  if( ! $cache_handle = fopen( $cache_file, 'w' ) ) return false;

  fwrite( $cache_handle, serialize( $cache ) );
  fclose( $cache_handle );
}


//// BEGIN MAIN
if( ! $cobra = __k_cache_fetch() ) die( __FILE__ . '(' . __LINE__ . ')' );

foreach( $cobra['sys.cache'] as $cache => $desc ) {
  __c_cache( $desc['path'], $cobra['path.cache'] . '/' . $cache . '.' . COBRA_CACHE_EXTENSION, $desc['recursive'] );
}
//// END MAIN
?>
