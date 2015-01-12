<?php
/**
*   admin file for Anketa plugin.
* @package   Anketa
*/

/**
 * Initialize admin for anketa.
 */
function admin_init(){
  	add_meta_box("odpovede_meta", __("Answers",'anketa'), "odpovede_meta", "anketa", "normal", "low");
    add_meta_box("datum_meta", __("Planning",'anketa'), "datum_meta", "anketa", "side", "low");
}
add_action( 'admin_init', 'register_anketa_settings' );

/**
 * Register settings for anketa.
 */
function register_anketa_settings(){
    register_setting( 'anketa-settings', 'anketa_page' );
}

/**
 * Add javascript to admin.
 */
function custom_admin_js() {
    //$url = get_option('siteurl');
    //$url = get_bloginfo('template_directory') . '/js/wp-admin.js';
    $screen = get_current_screen();
    if($screen->post_type == 'anketa'){
       $url = plugins_url( 'admin.js' , __FILE__ ) ;
       echo '<script type="text/javascript" src="'. $url . '"></script>';
    }
    
}
add_action('admin_footer', 'custom_admin_js');


/**
 * Admin settings page ti admin menu.
 */
function admin_menu() {
    add_submenu_page( 'edit.php?post_type=anketa',
      __( 'Nastavenia', 'anketa' ),
			__( 'Nastavenia', 'anketa' ),
      'manage_options',
      'anketa_settings',
       'anketa_options' ); 
}

/**
 * Anketa admin settings form.
 */
function anketa_options(){
 
  echo '<div class="wrap">';
	echo '<h2>'. esc_html( get_admin_page_title() ).'</h2>';
  echo '<div class="wrap"> <form method="post" action="options.php">';
  settings_fields( 'anketa-settings' ); 
  do_settings_sections( 'anketa-settings' );
  echo '<table class="form-table"> <tr valign="top"><th scope="row">'.__('Page for polls and archive','anketa').'</th>';
  echo '<td>';
  $pages = get_pages();
  $page_array = array( 0 => __('Choose page','anketa') );
  foreach ($pages as $page)
  {
    $page_array[$page->ID] = $page->post_title;
  }
  $html = '<select class="regular" name="anketa_page" id="anketa_page">';
  foreach ( $page_array as $key => $label ) {
      $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( esc_attr( get_option( 'anketa_page' ) ), $key, false ), $label );
  }
  $html .= sprintf( '</select>' );
   echo $html;
  echo '</td> ';
  echo '</tr></table>';
  submit_button();
  echo '</form></div>';
}

add_action( 'admin_menu', 'admin_menu' );

/**
 * Form for anketa answers.
 */
function odpovede_meta() {
  global $post;
  $custom = get_post_custom($post->ID);

  foreach ($custom as $key => $value) {
  	if(substr($key, 0, 7) == "odpoved"){
 		$odpoved[substr($key, 8)] =  $value[0];

	}
}
	$i = count($odpoved);
	if($i == 0){
		echo '<p><label>'.($i+1).'. Odpoved:</label><br>
	  <input type="text" class="regular-text" name="odpoved['.$i.']" id="odpoved_'.$i.'" size="35" value="" /><a href="javascript:void(0)" id="input-_'.$i.'" class="del_input_field" onclick="del_input_fields('.$i.')">-</a>
	  </p>';
	  $i++;
	}else{
    ksort($odpoved);
	  foreach ($odpoved as $key => $value) {
	  	echo '<p><label>'.($key+1).'. '.__('Odpoveď','anketa').':</label><br>
		  <input type="text" class="regular-text" name="odpoved['.$key.']" id="odpoved_'.$key.'" size="35" value="'.$value.'" /><a href="javascript:void(0)" id="input-_'.$key.'" class="del_input_field" onclick="del_input_fields('.$key.')">-</a>
		  </p>';
	  }
	}
  echo '<span id="num_inputs" style="display:none;">'.$i.'</span><div id="add_container" ></div><a href="javascript:void(0)" id="input+" class="add_input_field" onclick="add_input_fields()">+</a><br>';
//alternative answer
    echo'<input type="checkbox" name="alta" id="alta" '.((isset($custom['alta'][0]) && $custom['alta'][0] == '0') ? "" : "checked").' />
			<label for="alta">'.__('Show alternative answer:','anketa').'</label><br>';

echo '<p><label>'.__('Popis pre alternatívnu odpoveď','anketa').'</label><br>
		  <input type="text" class="regular-text" name="alt_odpoved" id="alt_odpoved" size="35" value="'.((isset($custom['alt_odpoved'][0]) && $custom['alt_odpoved'][0] != '') ? $custom ['alt_odpoved'][0] : "Another:").'" />
		  </p>'; 
}


/**
 * Form for anketa dates.
 */
function datum_meta() {
  global $post;
  $custom = get_post_custom($post->ID);
  
  if(isset($custom['date_start'][0])&& $custom['date_start'][0] != ''){
     $day = date_i18n( 'd', $custom['date_start'][0]);
     $month = date_i18n( 'm', $custom['date_start'][0]);
     $year = date_i18n( 'Y', $custom['date_start'][0]);
  }
  echo '<p><label>'.__('Dátum začatia ankety','anketa').'</label><br>
		  <input type="text" name="date_start_d" id="date_start_d" size="2" value="'.((isset($day) ) ? $day : get_the_date( 'd' )).'" />.
      <input type="text" name="date_start_m" id="date_start_m" size="2" value="'.((isset($month)) ? $month : get_the_date( 'm' )).'" />.
      <input type="text" name="date_start_y" id="date_start_y" size="4" value="'.((isset($year) ) ? $year : get_the_date( 'Y' )).'" />
		  </p>';
  if(isset($custom['date_end'][0])&& $custom['date_end'][0] != ''){
     $day = date_i18n( 'd', $custom['date_end'][0]);
     $month = date_i18n( 'm', $custom['date_end'][0]);
     $year = date_i18n( 'Y', $custom['date_end'][0]);
  }
      
  echo '<p><label>'.__('Dátum skončenia ankety','anketa').'</label><br>
		  <input type="text" name="date_end_d" id="date_end_d" size="2" value="'.((isset($day)) ? $day : get_the_date( 'd' )).'" />.
      <input type="text" name="date_end_m" id="date_end_m" size="2" value="'.((isset($month)) ? $month : get_the_date( 'm' )).'" />.
      <input type="text" name="date_end_y" id="date_end_y" size="4" value="'.((isset($year) ) ? $year : get_the_date( 'Y' )).'" />
		  </p>';
}

/**
 * Anketa save function.
 */
function save_details(){
  global $post;
  //update_post_meta($post->ID, "odpoved", $_POST["odpoved"]);
 $custom = get_post_custom($post->ID);
  $odpoved = $custom["odpoved"];
$del = 0;
if($post->post_type == "anketa"){
   foreach ($_POST["odpoved"] as $key => $value) {
   	
     	if($value == ""){
     		 delete_post_meta($post->ID, "odpoved_".$key);
     		 $del++;
     	}else{
    		update_post_meta($post->ID, "odpoved_".($key-$del), $value);
     	}
    }
    if(isset($_POST["alta"])){
    	update_post_meta($post->ID, "alta", 1);
      update_post_meta($post->ID, "alt_odpoved", $_POST["alt_odpoved"]);
    }else{
    	update_post_meta($post->ID, "alta", 0);
    }
    
    update_post_meta($post->ID, "date_start", strtotime($_POST["date_start_d"].'.'.$_POST["date_start_m"].'.'.$_POST["date_start_y"]));
     
    update_post_meta($post->ID, "date_end", strtotime($_POST["date_end_d"].'.'.$_POST["date_end_m"].'.'.$_POST["date_end_y"]));
  
  }
  
}
add_action('save_post', 'save_details');
?>