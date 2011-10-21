<?php
$try_cnt           = 1000;
$crossover_rate    = 900; // 交叉率 800 /1000
$mutation_rate     = 10;  // 突然変異率 10 / 1000
$population_size   = 50;  // 個体群の数
$population_length = 50; // 個体群の大きさ
$cross_kind        = 20;  //　交叉数
$ereate_flg        = true; // エリートフラグ
$try_flg           = false; // 試験
$select_patern     = 2;


$str_kinds = array(
               'h',
               'j',
               'k',
               'l',
               '.',
             );

// start packman game
$level1 = new field( $argv[1] );

function makeMasterData( $filename, $master ){
  $export = var_export( $master, true );
  $str = <<<EOF
<?php
\$next_populations = $export;
EOF;

  file_put_contents( $filename, $str );
}

function check_value( $population ){
  global $level1;
  $level1->reset();
  while( 1 ){
    foreach( str_split( $population ) as $str ){
      $level1->run( $str );
      if( 1 === $level1->finish_flg ){
        break 2;
      }
    }
  
  }

  if( 28 !== $level1->dot_cnt ){
    return $level1->dot_cnt;
  } else {
    return $level1->dot_cnt + $level1->limit_time - $level1->time;
  }
}


function get_best_population( $populations ){
  $best_value = 0;
  $best_no    = 0;
  foreach( $populations as $i => $data ){
    if( $data['value'] > $best_value ){
      $best_value = $data['value'];
      $best_no = $i;
    }
  }

  return $i;
}

if( 1 == $select_patern ){
  function select_population( $sort_populations ){
    $select_data = array();
  
    for( $i = 0; $i < 2; $i++ ){
      $sum_value   = 0;
      foreach( $sort_populations as $data ){
        $sum_value += $data['value'];
      }
      $rand_value = rand( 0 , $sum_value );
  
      $tmp_value = 0;
      foreach( $sort_populations as $key => $data ){
        $tmp_value += $data['value'];
        if( $tmp_value >= $rand_value ){
          $select_data[] = $data['population'];
          unset( $sort_populations[$key] );
          break;
        }
      }
  
    }
    
    return $select_data;
  
  }

} else if( 2 == $select_patern ) {
  function select_population( $sort_populations ){

    $select_data = array();
  
    for( $i = 0; $i < 2; $i++ ){
      $sum_value   = 0;
      $values      = array();
      $max_value = count( $sort_populations );
      $n = 0;
      foreach( $sort_populations as $key => $data ){
        $values[$key] = $max_value - $n;
        $sum_value += $max_value - $n;
        $n++;
      }
      $rand_value = rand( 1 , $sum_value );
  
      $tmp_value = 0;
      foreach( $sort_populations as $key => $data ){
        $tmp_value += $values[$key];
        if( $tmp_value >= $rand_value ){
          $select_data[] = $data['population'];
          unset( $sort_populations[$key] );
          break;
        }
      }
  
    }
    
    return $select_data;
  
  }
}




function is_check_rand( $rate ){
  return $rate >= rand( 1 , 1000 );
}

function get_crossover_length(){
  global $crossover_rate;
  global $population_length;
  global $cross_kind;
  $tmp_population_length = $population_length;
  $cross_lengths = array();
  for( $i = 0; $i < $cross_kind; $i++ ){
    if( is_check_rand( $crossover_rate ) ){
      $length = rand( 1, $tmp_population_length );
      $cross_lengths[] = $length;
      $tmp_population_length -= $length;
    }
  }
  $cross_lengths[] = $tmp_population_length;

  return $cross_lengths;
}

function crossover_children( $parent1, $parent2, $cross_lengths ){
  $children = "";
  $start_point = 0;
  foreach( $cross_lengths as $i => $length ){
    if( 0 === $i % 2){
      $children1 .= substr( $parent1, $start_point, $length );
      $children2 .= substr( $parent2, $start_point, $length );
    } else {
      $children1 .= substr( $parent2, $start_point, $length );
      $children2 .= substr( $parent1, $start_point, $length );
    }
    $start_point += $length;
  }

  return array( $children1, $children2 );
  
}

function mutation( $child ){
  global $mutation_rate;
  global $str_kinds;
  $mutation_child = "";
  foreach( str_split( $child ) as $str ){
    if( is_check_rand( $mutation_rate ) ){
      $mutation_child .= $str_kinds[array_rand($str_kinds)];
    } else {
      $mutation_child .= $str;
    }
  }
  
  return $mutation_child;
}





$pre_populations  = array();
$next_populations = array();

for( $i = 0; $i < $population_size; $i++ ){
  $population = "";
  for( $n = 0; $n < $population_length; $n++ ){
    $population .= $str_kinds[array_rand($str_kinds)];
  }
  $next_populations[] = $population;
}

if( !$try_flg ){
  include( 'master.php' );
}

for( $i = 0; $i < $try_cnt; $i++ ){
  echo "no. : $i \n";

  $pre_populations = array();
  foreach( $next_populations as $population ){
    $pre_populations[] = array(
                           'population' => $population,
                           'value' => check_value( $population ),
                         );
    
  }

  $next_populations = array();
  usort($pre_populations, create_function('$a,$b', 
  'return($b[\'value\'] - $a[\'value\']);'));

  echo "strs: " . $pre_populations[0]['population'] . "\n";
  echo "point: " . $pre_populations[0]['value'] . "\n";
   
  if( $ereate_flg ){
    $next_populations[] = $pre_populations[0]['population'];
  }
  while( count( $next_populations ) <= $population_size ){
    $lengths =  get_crossover_length();
    $parents = select_population( $pre_populations );

    $children = crossover_children( $parents[0], $parents[1], $lengths );
    
    $next_populations[] = mutation( $children[0] );
    $next_populations[] = mutation( $children[1] );
  }

}

$pre_populations = array();
foreach( $next_populations as $population ){
  $pre_populations[] = array(
                         'population' => $population,
                         'value' => check_value( $population ),
                       );
  
}
usort($pre_populations, create_function('$a,$b', 
'return($b[\'value\'] - $a[\'value\']);'));
echo "strs: " . $pre_populations[0]['population'] . "\n";
echo "point: " . $pre_populations[0]['value'] . "\n";

makeMasterData( 'master.php', $next_populations );
















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

  public $my_select = array();
  public $finish_flg = 0;


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
        if( isset( $this->init_data[$w][$h + 1] ) 
           && '#' !== $this->init_data[$w][$h + 1]['disp'] ){
          $move_array[] = array( $w, $h + 1 );
        }
        if( isset( $this->init_data[$w - 1][$h] ) 
           && '#' !== $this->init_data[$w - 1][$h]['disp'] ){
          $move_array[] = array( $w - 1 , $h );
        }
        if( isset( $this->init_data[$w][$h - 1] ) 
           && '#' !== $this->init_data[$w][$h - 1]['disp'] ){
          $move_array[] = array( $w, $h - 1 );
        }
        if( isset( $this->init_data[$w + 1][$h] ) 
           && '#' !== $this->init_data[$w + 1][$h]['disp'] ){
          $move_array[] = array( $w + 1 , $h );
        }
        if( '#' !== $this->init_data[$w][$h]['disp'] ){
          $move_array[] = array( $w, $h );
        }
        
        $this->init_data[$w][$h]['can_move'] = $move_array;
      }
    }
  }

  

  public function reset(){
    $this->finish_flg = 0;
    $this->time = 0;
    $this->dot_cnt = 0;
    $this->map = $this->init_data;
    $this->my_select = array();
    foreach( $this->enemy_ar as $key => $enemy ){
      unset( $this->enemy_ar[$key] );
    }
    unset( $enemy );
    $this->enemy_ar = array();
    unset( $this->my );
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
      $this->dot_cnt++;
    }
    if( 0 === count( $this->dot_ar ) ){
      $this->finish_flg = 0;
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
    echo implode( "", $this->my_select );
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
    $this->my_select[] = $str;
    $this->my->move( $str, $this->map[$this->my->w][$this->my->h]['can_move'] );
    foreach( $this->enemy_ar as $enemy ){
      $enemy->move( $this->my->before_w, 
                    $this->my->before_h, 
                    $this->map[$enemy->w][$enemy->h]['can_move'] );
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
    if( 3 === $can_move_cnt ){
      foreach( $can_move as $w_h ){
        if( array( $this->before_w, $this->before_h ) !== $w_h ){
          $this->record_before();
          $this->set_place( $w_h[0] , $w_h[1] );
          return true;
        }
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
  public $str     = '@';
  public function move( $str, $can_move ){
    $this->record_before();
    $this->cycle = $this->left_cycle;

    $w = $this->w;
    $h = $this->h;
    switch( $str ){
      case 'h':
        if( $this->is_can_move( $w - 1, $h, $can_move ) ){
          $this->set_place( $w - 1 , $h );
          return true;
        }
        break;
      case 'j':
        if( $this->is_can_move( $w, $h + 1, $can_move ) ){
          $this->set_place( $w , $h + 1 );
          return true;
        }
        break;
      case 'k':
        if( $this->is_can_move( $w, $h - 1, $can_move ) ){
          $this->set_place( $w , $h - 1 );
          return true;
        }
        break;
      case 'l':
        if( $this->is_can_move( $w + 1, $h, $can_move ) ){
          $this->set_place( $w + 1 , $h );
          return true;
        }
        break;
      case '.':
        $this->set_place( $w , $h );
        return true;
        break;
      default:
        break;
    }

    $this->set_place( $w , $h );
    return true;
    if( $this->common_move( $can_move ) ){
      return true;
    }
    
    $this->LR_move( $can_move );
    $this->set_place( $can_move[0][0] , $can_move[0][1] );
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

