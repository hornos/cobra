<?php

// get out global home path
$__home = dirname( __FILE__ );

// system paths  
$cobra['path.home']   = $__home;
$cobra['path.class']  = $__home.'/class';
$cobra['path.app']    = $__home.'/apps';
$cobra['path.cache']  = $__home.'/cache';
$cobra['path.log']    = $__home.'/log';
$cobra['path.error']  = $__home.'/error';
$cobra['path.htdocs'] = $__home.'/../htdocs';

// system files
$cobra['path.boot']      = __FILE__;
$cobra['path.kernel']    = $__home.'/k.php';
$cobra['path.config']    = $__home.'/co.ses.php';


// load kernel
if( ! is_readable( $cobra['path.kernel'] ) )
  die( 'boot::kernel' ) ;
// else
require_once( $cobra['path.kernel'] );


// load config
if( ! is_readable( $cobra['path.config'] ) )
  die( 'boot::config' );
// else
require_once( $cobra['path.config'] );

// custom defines
// __k_define( 'COBRA_SITE_LOCK', $cobra['path.htdocs'] . '/nologin' );
__k_define( 'COBRA_OB', false );
__k_define( 'COBRA_RECURSIVE_CACHE', true );
// init cobra
__k_init( 'UTF-8', '__k_exception_handler' );
// cache cobra
__k_cache_store( COBRA_MAIN_ID, new coCobra( $cobra, $config ) );
// clean up
unset( $__home, $cobra, $config );
?>
