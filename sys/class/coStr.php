<?php

__k_define( 'COBRA_STRING_SIZE', COBRA_KERNEL_STRING_SIZE );
__k_define( 'COBRA_STRING_REGEX_ALPHA', '[^[:alpha:]_:./ -]' );
__k_define( 'COBRA_STRING_REGEX_NAME', '[^[:alpha:] -]' );
__k_define( 'COBRA_STRING_REGEX_ALNUM', '[^[:alnum:]_:./ -]' );
__k_define( 'COBRA_STRING_REGEX_EMAIL', '[^[:alnum:]@_./ -]' );
__k_define( 'COBRA_STRING_REGEX_PREURL', '^.*:\/\/' );
__k_define( 'COBRA_STRING_REGEX_NUM', '[^[:digit:].-]' );
__k_define( 'COBRA_STRING_REGEX_TEL', '[^[:digit:].+-]' );

// begin static class coStr
class coStr {
  public function __construct() { }

  public static function lower( $str = '' ) {
    return COBRA_MB ? mb_strtolower( $str ) : strtolower( $str);
  }

  public static function trunc( $str = '', $size = COBRA_STRING_SIZE ) {
    if( $size == 0 ) return $str;

    return COBRA_MB ? mb_substr( trim( $str ), 0, $size ) : substr( trim( $str ), 0, $size );
  }

  public static function sqlf( $str = '' ) {
    return '\''.addslashes( $str ).'\'';
  }

  public static function a2f( $arr = NULL, $default = '*', $sqlf = false ) {
    if( empty( $arr ) ) return $default;

    $size = count( $arr );
    if( $size < 1 ) return $default;

    $i = 1;
    $str = '';
    foreach( $arr as $a ) {
      $str .= ( $sqlf ? self::sqlf( $a ) : $a );
      if( $i < $size ) $str .= ',';

      ++$i;
    }
    return $str;
  }


  // begin Regexp filter
  public static function alpha( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_ALPHA, '', $str ) : ereg_replace( COBRA_STRING_REGEX_ALPHA, '', $str );
  }

  public static function num( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_NUM, '', $str ) : ereg_replace( COBRA_STRING_REGEX_NUM, '', $str );
  }

  public static function alnum( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $str ) : ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $str );
  }

  public static function name( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_NAME, '', $str ) : ereg_replace( COBRA_STRING_REGEX_NAME, '', $str );
  }

  public static function email( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_EMAIL, '', $str ) : ereg_replace( COBRA_STRING_REGEX_EMAIL, '', $str );
  }

  public static function tel( $str ) {
    return COBRA_MB ? mb_ereg_replace( COBRA_STRING_REGEX_TEL, '', $str ) : ereg_replace( COBRA_STRING_REGEX_TEL, '', $str );
  }

  public static function url( $str, $clean = true ) {
    if( COBRA_MB ) {
      $rstr = $clean ? ereg_replace( COBRA_STRING_REGEX_PREURL, '', $str ) : $str;
      return ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $rstr );
    }
    $rstr = $clean ? mb_ereg_replace( COBRA_STRING_REGEX_PREURL, '', $str ) : $str;
    return mb_ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $rstr );
  }

  public static function subalpha( $str, $size = COBRA_STRING_SIZE ) {
    return self::alpha( self::trunc( $str, $size ) );
  }

  public static function subalnum( $str, $size = COBRA_STRING_SIZE ) {
    return self::alnum( self::trunc( $str, $size )  );
  }

  public static function subnum( $str, $size = COBRA_STRING_SIZE ) {
    return self::num( self::trunc( $str, $size ) );
  }
  // end Regexp filter

  // begin conversion
  public static function int( $str, $ll = 0, $ul = 100 ) {
    $str = self::num( $str );
    $v = intval( $str );
    if( $v < $ll ) return $ll;

    if( $v > $ul ) return $ul;

    return $v;
  }

  public static function float( $str ) {
    $str = self::num( $str );
    return floatval( $str );
  }

  public static function tof( $str ) {
    if( empty( $str ) ) return false;

    $str = self::subalnum( $str, 4 );
    if( $str == 't' || $str == 'true' ) return true;

    return false;
  }
  // end conversion
} // end static class coStr

?>
