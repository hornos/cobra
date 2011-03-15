<?php
$__home = dirname( __FILE__ );

// system paths
$core['path.home']  = $__home;
$core['path.class'] = $__home.'/class';

// system files
$core['path.bootstrap'] = __FILE__;
$core['path.kernel']    = $__home.'/k.php';
$core['path.config']    = $__home.'/co.php';

// load config
require_once( $core['path.config'] );

// load kickstart
require_once( $core['path.kernel'] );

// init app
?>
