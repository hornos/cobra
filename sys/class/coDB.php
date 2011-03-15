<?php

// Global cobra defines
__k_define( 'COBRA_DB_FUNCTION_PREFIX', 'f_' );
__k_define( 'COBRA_DB_TABLE_PREFIX', 't_' );
__k_define( 'COBRA_DB_TABLEFUNCTION_PREFIX', COBRA_DB_FUNCTION_PREFIX . COBRA_DB_TABLE_PREFIX );
__k_define( 'COBRA_DB_SELECT_LIMIT', 500 );
__k_define( 'COBRA_DB_PROCEDURE_NAME_SIZE', 256 );
__k_define( 'COBRA_DB_SQL_TIME', true );
__k_define( 'COBRA_DB_TIMESTAMP_FORMAT', 'Y-m-d H:i:s' );
// PHP microtime bug?
__k_define( 'COBRA_DB_MICROTIME_FORMAT', 'Y-m-d H:i:s.u' );

// Exception class
class coDBEx extends coEx {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


// begin class coDB
class coDB implements ArrayAccess {
  private $__config;    /**< config array */
  private $__dbconn;    /**< database connection object */

  // Constructor
  public function __construct( $config = NULL ) {
    if( empty( $config ) )
      throw new coDBEx( __METHOD__ );
    /*!
      \param $config System profile which contains DB connection parameters
    */
    $this->__config = $config;
    $this->_set_dbconn( NULL );
  }


  // begin ArrayAccess interface
  public function offsetSet( $offset, $value ) {
    /*! ArrayAccess interface */
    $this->__config[ $offset ] = $value;
  }

  public function offsetExists( $offset ) {
    /*! ArrayAccess interface */
    return isset( $this->__config[$offset] );
  }

  public function offsetUnset( $offset ) {
    /*! ArrayAccess interface */
    unset( $this->__config[$offset] );
  }

  public function offsetGet( $offset ) {
    /*! ArrayAccess interface */
    if( isset( $this->__config[$offset] ) ) return $this->__config[$offset];

    throw new coDBEx( __METHOD__ . '::' . $offset );
  }
  // end ArrayAccess interface


  // begin Time
  public function time() {
    try {
      return COBRA_DB_SQL_TIME ? $this->CallProcedure( COBRA_DB_FUNCTION_PREFIX . 'time' ) : time();
    } catch( Exception $e ) {
      return time();
    }
  }

  public function microtime() {
    try {
      return COBRA_DB_SQL_TIME ? $this->CallProcedure( COBRA_DB_FUNCTION_PREFIX . 'microtime' ) : date( COBRA_DB_MICROTIME_FORMAT );
    } catch( Exception $e ) {
      return date( COBRA_DB_MICROTIME_FORMAT );
    }
  }

  public function timestamp() {
    try {
      return COBRA_DB_SQL_TIME ? $this->CallProcedure( COBRA_DB_FUNCTION_PREFIX . 'timestamp' ) : date( COBRA_DB_TIMESTAMP_FORMAT );
    } catch( Exception $e ) {
      return date( COBRA_DB_TIMESTAMP_FORMAT );
    }
  }
  // end Time

  // begin Connection
  protected function _get_dbconn() {
    /*! Checks and returns the valid connection object */
    if( ! $this->__dbconn ) throw new coDBEx( __METHOD__ );

    return $this->__dbconn;
  }

  protected function _set_dbconn( $dbconn = NULL ) {
    /*! Sets the connection object */
    $this->__dbconn = $dbconn;
  }

  public function Connect() {
    try {
      $this->_get_dbconn();
    } catch( Exception $e ) {
      $dsn  = $this['db.engine'].':';
      $dsn .= 'host='.$this['db.host'].';';
      $dsn .= 'port='.$this['db.port'].';';
      $dsn .= 'dbname='.$this['db.name'];
      // connect
      $this->_set_dbconn( new PDO( $dsn, $this['db.user'], $this['db.pass'], $this['db.pdo_attributes'] ) );
      // coDebug::message( 'Database connected' );
      return true;
    }
    return false;
  }

  public function Disconnect() {
    $this->_set_dbconn( NULL );
    // coDebug::message( 'Database disconnected' );
  }
  // end Connection

  // begin DB Access
  protected function _Query( $query = NULL, $select = true ) {
    $dbconn = $this->_get_dbconn();

    if( empty( $query ) ) throw new coDBEx( __METHOD__ );

    $statement = $dbconn->prepare( $query );
    if( ! $statement->execute() )
      throw new coDBEx( __METHOD__ . " " . implode( ",", $statement->errorInfo() ) );

    // coDebug::message( $query, 9, 'SQL' );

    // SELECT
    if( $select ) {
      $affrows = $statement->columnCount();
      if( $affrows < 1 ) throw new coDBEx( __METHOD__ );

      // coDebug::message( 'Affected Rows: '.$affrows );
      $statement->setFetchMode( PDO::FETCH_ASSOC ); 
      $rarray = $statement->fetchAll(); 
      if( ! $rarray ) throw new coDBEx( __METHOD__ );

      return $rarray;
    }

    // INSERT, UPDATE, DELETE
    $affrows = $statement->rowCount();
    if( $affrows < 1 ) throw new coDBEx( __METHOD__ );

    // coDebug::message( 'Affected Rows: '.$affrows );
    return $affrows;
  } // end _Query

  public function Execute( $query = NULL ) {
    return $this->_Query( $query, false );
  }

  public function Select( $query = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0 ) {
    $suffix  = ( $limit  > 0 ) ? ' LIMIT '  . $limit  : '';
    $suffix .= ( $offset > 0 ) ? ' OFFSET ' . $offset : '';
    return $this->_Query( $query );
  }

  public function SelectRow( $query = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0, $row = 0 ) {
    $results = $this->Select( $query, $limit, $offset );
    return $results[$row];
  }

  public function CallProcedure( $procedure = NULL, $arguments = NULL ) {
    if( empty( $procedure ) ) throw new coDBEx( __METHOD__ );

    $procedure = __k_safe_str( $procedure, COBRA_DB_PROCEDURE_NAME_SIZE );
    // begin query
    $query  = 'SELECT ' . $procedure;
    $query .= '(' . coStr::a2f( $arguments, '', true ) . ')';
    $result = $this->SelectRow( $query, 0, 0, 0 );
    if( isset( $result[$procedure] ) ) return $result[$procedure];

    return $result;
  }

  public function CallProcedureSelect( $procedure = NULL, $arguments = NULL, $fields = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0 ) {
    if( empty( $procedure ) ) throw new coDBEx( __METHOD__ );

    $procedure = __k_safe_str( $procedure, COBRA_DB_PROCEDURE_NAME_SIZE );
    $fields    = coStr::a2f( $fields );
    $query  = 'SELECT ' . $fields . ' FROM ' . $procedure;
    $query .= '(' . coStr::a2f( $arguments, '', true ) . ')';
    return $this->Select( $query, $limit, $offset );
  }

  public function CallProcedureSelectRow( $procedure = NULL, $arguments = NULL, $fields = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0, $row = 0 ) {
    $result = $this->CallProcedureSelect( $procedure, $arguments, $fields, $limit, $offset );
    return $result[$row];
  }

} // end class coDB

?>
