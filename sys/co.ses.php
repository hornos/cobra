<?php

// applications
$config['app.boot'] = array( 'core' );

// db access
$config['db.host']   = '127.0.0.1';
$config['db.name']   = 'cobra';
$config['db.user']   = 'u_session';
$config['db.port']   = 5432;
$config['db.pass']   = 'cobra_session';
$config['db.engine'] = 'pgsql';
// $config['db.pdo_attributes'] = array( PDO::ATTR_PERSISTENT => true );
$config['db.pdo_attributes'] = array();

// session
// $config['session.key']           = '';
$config['session.key']           = '';
//$config['session.salt_vector']   = array( 0, 3, 5 );
//$config['session.salt_size']     = 4;   // chars
$config['session.name']          = 'CSID';
$config['session.expiration']    = 60000; // sec 3600
// rekeying is not OK with ajax
$config['session.id_expiration'] = 0;  // sec
$config['session.strict_client_check'] = true;

$config['system.user_expiration']     = 60;  // sec
$config['system.max_login_tries']     = 3;

?>
