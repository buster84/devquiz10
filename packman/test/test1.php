<?php
class field {
  public $init_data;
  public $map;
  public $limit_time;
  public $time;
  public $width;
  public $height;

  public $my;
  public $dot_ar   = array();
  public $dot_cnt  = 0;
  public $enemy_ar = array();

  public $finish_flg = 0;
  public $clear_flg = 0;


  public function __construct( $file ){
    $lines = file( $file );
    $this->limit_time = (int) rtrim( array_shift( $lines ) );
    list( $width, $height )  =  explode( ' ', rtrim( array_shift( $lines ) ) );
    $this->width = ( int ) $width;
    $this->height = ( int ) $height;
    
    $this->init_data = array();
    foreach( $lines as $i => $line ){
      $line = rtrim( $line );
      foreach( str_split( $line ) as $n => $str ){
        if( !isset($this->init_data[$n + 1]) ){
          $this->init_data[$n + 1] = array();
        }
        $this->init_data[$n + 1][$i + 1] = array(
                                             'disp' => $str
                                           );
                                           
      }
    }

    for( $w = 1; $w <= $this->width; $w++ ){
      for( $h = 1; $h <= $this->height; $h++ ){
        $move_array = array();
        if( '#' === $this->init_data[$w][$h]['disp'] ){
          continue;
        }
        if( isset( $this->init_data[$w][$h + 1] ) 
           && '#' !== $this->init_data[$w][$h + 1]['disp'] ){
          $move_array[] = array(
                            'can_move'    => array( $w, $h + 1 ),
                            'can_command' => 'j',
                          );
        }
        if( isset( $this->init_data[$w - 1][$h] ) 
           && '#' !== $this->init_data[$w - 1][$h]['disp'] ){
          $move_array[] = array(
                            'can_move'    => array( $w - 1, $h ),
                            'can_command' => 'h',
                          );
        }
        if( isset( $this->init_data[$w][$h - 1] ) 
           && '#' !== $this->init_data[$w][$h - 1]['disp'] ){
          $move_array[] = array(
                            'can_move'    => array( $w, $h - 1 ),
                            'can_command' => 'k',
                          );
        }
        if( isset( $this->init_data[$w + 1][$h] ) 
           && '#' !== $this->init_data[$w + 1][$h]['disp'] ){
          $move_array[] = array(
                            'can_move'    => array( $w + 1, $h ),
                            'can_command' => 'l',
                          );
        }
        //$move_array[] = array(
        //                  'can_move'    => array( $w, $h ),
        //                  'can_command' => '.',
        //                );
        
        $this->init_data[$w][$h]['can_way'] = $move_array;
      }
    }
    for( $w = 1; $w <= $this->width; $w++ ){
      for( $h = 1; $h <= $this->height; $h++ ){
        if( 2 == count( $this->init_data[$w][$h]['can_way'] ) ){
          foreach( $this->init_data[$w][$h]['can_way'] as $data ){
            if( 4 === count( $this->init_data[$data['can_move'][0]][$data['can_move'][1]]['can_way'] ) ){
              $this->init_data[$w][$h]['can_way'][] = array(
                                                         'can_move'    => array( $w, $h ),
                                                         'can_command' => '.',
                                                       );
              continue 2;
            }
            if( 3 === count( $this->init_data[$data['can_move'][0]][$data['can_move'][1]]['can_way'] )
                && '.' !== $this->init_data[$data['can_move'][0]][$data['can_move'][1]]['can_way'][2]['can_command'] ){
              $this->init_data[$w][$h]['can_way'][] = array(
                                                         'can_move'    => array( $w, $h ),
                                                         'can_command' => '.',
                                                       );
              continue 2;
            }
          }
        }
      }
    }



  }

  

  public function reset(){
    $this->time = 0;
    $this->finish_flg = 0;
    $this->clear_flg = 0;
    $this->dot_cnt = 0;
    $this->map = $this->init_data;
    foreach( $this->enemy_ar as $key => $enemy ){
      unset( $this->enemy_ar[$key] );
    }
    unset( $enemy );
    $this->enemy_ar = array();
    unset( $this->my );
    $this->dot_ar = array();
    for( $w = 1; $w <= $this->width; $w++ ){
      for( $h = 1; $h <= $this->height; $h++ ){
        $str = $this->map[$w][$h]['disp']; 
        switch( (string) $str ){
          case '.': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->dot_ar[$w][$h] = 1;
            break;
          case '#': 
          case ' ': 
            break;
          case '@': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->my = new my( $w , $h );
            break;
          case 'V': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->enemy_ar[] = new V( $w, $h );
            break;
          case 'H': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->enemy_ar[] = new H( $w, $h );
            break;
          case 'L': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->enemy_ar[] = new L( $w, $h );
            break;
          case 'R': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->enemy_ar[] = new R( $w, $h );
            break;
          case 'J': 
            $this->map[$w][$h]['disp'] = ' '; 
            $this->enemy_ar[] = new J( $w, $h );
            break;
          default:
            break;
        }
        
      }
    }

  }

  public function count_dot(){
    if( 0 === $this->finish_flg &&
         isset( $this->dot_ar[$this->my->w][$this->my->h] ) ){
      unset( $this->dot_ar[$this->my->w][$this->my->h] );
      if( empty( $this->dot_ar[$this->my->w] ) ){
        unset( $this->dot_ar[$this->my->w] );
      }
      $this->dot_cnt++;
      if( 0 === count( $this->dot_ar ) ){
        $this->clear_flg = 1;
        $this->finish_flg = 1;
      }
    }
  }
  
  public function echo_map(){
    for( $h = 1; $h <= $this->height; $h++ ){
      for( $w = 1; $w <= $this->width; $w++ ){
        foreach( $this->enemy_ar as $enemy ){
          if( $w == $enemy->w && 
               $h == $enemy->h ){
            echo $enemy->str;
            continue 2;
          }
        }
        if( $w == $this->my->w && 
             $h == $this->my->h ){
          echo $this->my->str;
          continue;
        }
        if( isset( $this->dot_ar[$w][$h] ) ){
          echo '.';
          continue;
        }
        echo $this->map[$w][$h]['disp'];
      }
      echo "\n";
    }
    $time = $this->limit_time - $this->time; 
    echo 'time[';
    echo $time . '] dot[' 
         . $this->dot_cnt . "]\n";
    echo implode( "", $this->my->select );
    echo "\n";
    
  }

  public function set_time_finish(){
    if( 0 >= $this->limit_time - $this->time ){
      $this->finish_flg = 1;
    }
  }

  public function set_encount( $enemy ){
    if( array( $this->my->w, $this->my->h ) 
         === array( $enemy->w, $enemy->h ) ){
      $this->finish_flg = 1;
      return true;
    }

    if( array( $this->my->before_w, $this->my->before_h ) 
         === array( $enemy->w, $enemy->h ) &&
        array( $this->my->w, $this->my->h ) 
         === array( $enemy->before_w, $enemy->before_h )
    ){
      $this->finish_flg = 1;
      return true;
    }

    return true;
  }

  public function run( $str ){
    $this->time++;
    $can_command = array();
    foreach( $this->map[$this->my->w][$this->my->h]['can_way'] as $i => $data ){
      $can_command[] = $data['can_command'];
    }
    $this->my->move( $str, $can_command );
    foreach( $this->enemy_ar as $enemy ){
      $can_move = array();
      foreach( $this->map[$enemy->w][$enemy->h]['can_way'] as $i => $data ){
        $can_move[] = $data['can_move'];
      }
      $enemy->move( $this->my->before_w, 
                    $this->my->before_h, 
                    $can_move );
      $this->set_encount( $enemy );
    }
    $this->count_dot();
    
    $this->set_time_finish();
  }


}

class chracter {
  public $left_cycle = array(
                         array( 1, 0 ),
                         array( 0, -1 ),
                         array( -1, 0 ),
                         array( 0, 1 ),
                       );
  public $cycle;
  public $w;
  public $h;

  public $before_w;
  public $before_h;

  public $history = array();

  public function __construct( $w , $h ){
    $this->w = $w;
    $this->h = $h;
  }
  public function first_move( $can_move ){
    list( $w , $h ) = $can_move[0];
    $this->record_before();
    $this->set_place( $w , $h );
  }
  public function common_move( $can_move ){
    $can_move_cnt = count( $can_move );
    if( 4 <= $can_move_cnt ){
      return false;
    }
    if( 3 === $can_move_cnt && array( $this->w, $this->h ) !== $can_move[2] ){
      return false;
    }
    foreach( $can_move as $w_h ){
      if( array( $this->before_w, $this->before_h ) !== $w_h ){
        $this->record_before();
        $this->set_place( $w_h[0] , $w_h[1] );
        return true;
      }
    }

    $this->record_before();
    $this->set_place( $can_move[0][0] , $can_move[0][1] );
    return true;

  }
  public function is_can_move( $w, $h, $can_move ){
    foreach( $can_move as $w_h ){
      if( array( $w, $h ) === $w_h ){
        return true;
      }
    }
    return false;
  }

  public function move(){
  }

  public function LR_move( $can_move ){
    $cycle = $this->cycle;
    $dx = $this->w - $this->before_w;
    $dy = $this->h - $this->before_h;
    $no = 0;
    
    foreach( $cycle as $i => $x_y ){
      if( array( $dx, $dy ) === $x_y ){
        $no = $i + 1;
        break;
      }
    }
    $no = $no % 4;

    if( $this->is_can_move( $this->w + $cycle[$no][0], $this->h + $cycle[$no][1], $can_move ) ){
      $this->record_before();
      $this->set_place( $this->w + $cycle[$no][0], $this->h + $cycle[$no][1] );
      return true;
    }

    if( $this->is_can_move( $this->w + $dx, $this->h + $dy, $can_move ) ){
      $this->record_before();
      $this->set_place( $this->w + $dx, $this->h + $dy );
      return true;
    }

    $cycle = array_reverse( $this->cycle );
    $no = 0;
    foreach( $cycle as $i => $x_y ){
      if( array( $dx, $dy ) === $x_y ){
        $no = $i + 1;
        break;
      }
    }
    $no = $no % 4;

    if( $this->is_can_move( $this->w + $cycle[$no][0], $this->h + $cycle[$no][1], $can_move ) ){
      $this->record_before();
      $this->set_place( $this->w + $cycle[$no][0], $this->h + $cycle[$no][1] );
      return true;
    }
    
  }

  public function record_before(){
    $this->before_w = $this->w;
    $this->before_h = $this->h;
  }
  public function set_place( $w, $h){
    $this->w = $w;
    $this->h = $h;
  }
}

class my extends chracter {
  public $select  = array();
  public $str     = '@';
  public function move( $str, $can_command ){
    $this->select[] = $str;
    $this->record_before();

    $w = $this->w;
    $h = $this->h;

    if( !in_array( $str, $can_command ) ){
      $this->set_place( $w , $h );
      return true;
    }
    switch( $str ){
      case 'h':
        $this->set_place( $w - 1 , $h );
        break;
      case 'j':
        $this->set_place( $w , $h + 1 );
        break;
      case 'k':
        $this->set_place( $w , $h - 1 );
        break;
      case 'l':
        $this->set_place( $w + 1 , $h );
        break;
      case '.':
        $this->set_place( $w , $h );
        return true;
        break;
      default:
        break;
    }
  }

}
class V extends chracter {
  public $str = 'V';
  public function move( $my_w, $my_h, $can_move ){
    if( null === $this->before_h || null === $this->before_w ){
      $this->first_move( $can_move );
      return true;
    }

    if( $this->common_move( $can_move ) ){
      return true;
    }

    $this->record_before();
    $dx = $my_w - $this->w;
    $dy = $my_h - $this->h;

    if( 0 < $dy && $this->is_can_move( $this->w, $this->h + 1, $can_move ) ){
      $this->set_place( $this->w, $this->h + 1 );
      return true;
    }
    if( 0 > $dy && $this->is_can_move( $this->w, $this->h - 1, $can_move ) ){
      $this->set_place( $this->w, $this->h - 1 );
      return true;
    }

    if( 0 < $dx && $this->is_can_move( $this->w + 1, $this->h, $can_move ) ){
      $this->set_place( $this->w + 1, $this->h );
      return true;
    }
    if( 0 > $dx && $this->is_can_move( $this->w - 1, $this->h, $can_move ) ){
      $this->set_place( $this->w - 1, $this->h );
      return true;
    }

    
    $this->set_place( $can_move[0][0] , $can_move[0][1] );
    return true;
  }
}
class H extends chracter {
  public $str = 'H';
  public function move( $my_w, $my_h, $can_move ){
    if( null === $this->before_h || null === $this->before_w ){
      $this->first_move( $can_move );
      return true;
    }
 
    if( $this->common_move( $can_move ) ){
      return true;
    }

    $this->record_before();
    $dx = $my_w - $this->w;
    $dy = $my_h - $this->h;

    if( 0 < $dx && $this->is_can_move( $this->w + 1, $this->h, $can_move ) ){
      $this->set_place( $this->w + 1, $this->h );
      return true;
    }
    if( 0 > $dx && $this->is_can_move( $this->w - 1, $this->h, $can_move ) ){
      $this->set_place( $this->w - 1, $this->h );
      return true;
    }

    if( 0 < $dy && $this->is_can_move( $this->w, $this->h + 1, $can_move ) ){
      $this->set_place( $this->w, $this->h + 1 );
      return true;
    }
    if( 0 > $dy && $this->is_can_move( $this->w, $this->h - 1, $can_move ) ){
      $this->set_place( $this->w, $this->h - 1 );
      return true;
    }


    
    $this->set_place( $can_move[0][0] , $can_move[0][1] );
    return true;
  }
}
class L extends chracter {
  public $str = 'L';
  public function move( $my_w, $my_h , $can_move ){
    if( null === $this->before_h || null === $this->before_w ){
      $this->cycle = $this->left_cycle;
      $this->first_move( $can_move );
      return true;
    }
 
    if( $this->common_move( $can_move ) ){
      return true;
    }
    
    $this->LR_move( $can_move );

  }
}
class R extends chracter {
  public $str = 'R';
  public function move( $my_w, $my_h , $can_move ){
    if( null === $this->before_h || null === $this->before_w ){
      $this->cycle = array_reverse( $this->left_cycle );
      $this->first_move( $can_move );
      return true;
    }
 
    if( $this->common_move( $can_move ) ){
      return true;
    }
    
    $this->LR_move( $can_move );

  }
}
class J extends chracter {
  public $str = 'J';
  public function move( $my_w, $my_h , $can_move ){
    if( null === $this->before_h || null === $this->before_w ){
      $this->cycle = array_reverse( $this->left_cycle );
      $this->first_move( $can_move );
      return true;
    }
 
    if( $this->common_move( $can_move ) ){
      return true;
    }
    
    $this->cycle = array_reverse( $this->cycle );
    $this->LR_move( $can_move );

  }
}





// start packman game
$level1 = new field( $argv[1] );
$level1->reset();

$level1->echo_map();

//var_dump( $level1 );

while( 1 ){
  $input = fgets(STDIN,4096);
  $input = trim( $input, " \t\n\r" );

  foreach( str_split( $input ) as $str ){
    $level1->run( $str );
    $level1->echo_map();
    if( 1 === $level1->finish_flg ){
      break 2;
    }
  }

}
