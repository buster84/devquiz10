<?php
$lines = file( 'field.txt' );
$time = (int) rtrim( array_shift( $lines ) );
list( $width, $height )  =  explode( ' ', rtrim( array_shift( $lines ) ) );
$width = ( int ) $width;
$height = ( int ) $height;

$field = array();
foreach( $lines as $i => $line ){
  $line = rtrim( $line );
  foreach( str_split( $line ) as $n => $str ){
    if( !isset($field[$n + 1]) ){
      $field[$n + 1] = array();
    }
    $field[$n + 1][$i + 1] = array(
                               'init' => $str,
                             );
  }
}

for( $h = 1; $h <= $height; $h++ ){
  for( $w = 1; $w <= $width; $w++ ){
    
  }
}
