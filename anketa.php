<?php
/**
* @package   Wp-anketa
* 
* Plugin Name: Wp-anketa
* Plugin URI: http://webikon.sk
* Description: Simple poll plugin for wordpress. Only one question and choose one answer. Can be planned to certain date. Use shortcode [anketa], or [anketa id=1]. In settings is nessesary set the anketa page. Working only with Easy Chart Builder v 1.3.
* Version: 1.0
* Author: Martin Sarvaš 
* martin.sarvas@webikon.sk
* License: GPL2
*/



global $anketa_db_version;
$anketa_db_version = "1.0";

/**
 * Install plugin.
 */
function anketa_install() {
   global $wpdb;
   global $anketa_db_version;

   $table_name = $wpdb->prefix . "anketa";
   
   $charset_collate = $wpdb->get_charset_collate();
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  aid mediumint(9) NOT NULL,
  uid mediumint(9) NOT NULL,
  answer text NOT NULL,
  date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY aid (aid)
    )$charset_collate;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option( "anketa_db_version", $anketa_db_version );
}

/**
 * Dummy data into db.
 */
function anketa_install_data() {
   global $wpdb;
   
   $user = wp_get_current_user();
   $aid = 3065;
   $welcome_text = "Congratulations, you just completed the installation!";
   $table_name = $wpdb->prefix . "anketa";
   $rows_affected = $wpdb->insert( $table_name, array( 'aid' => $aid, 'uid' => $user->ID, 'answer' => $welcome_text ) );
}

register_activation_hook( __FILE__, 'anketa_install' );
//register_activation_hook( __FILE__, 'anketa_install_data' );


/**
 * Register style sheet.
 */
function anketa_styles() {
	wp_register_style( 'anketa', plugins_url( 'wp-anketa/style.css' ) );
	wp_enqueue_style( 'anketa' );
}

add_action( 'admin_enqueue_scripts', 'anketa_styles' ); 
add_action( 'wp_enqueue_scripts', 'anketa_styles' );

/**
 * Enqueue javascript for checking fields.
 */
function front_js() {
   wp_enqueue_script( 'anketa-front', plugin_dir_url( __FILE__ ) .'front.js', array('jquery'));
}
add_action( 'wp_enqueue_scripts', 'front_js' );

/**
 * Initialize plugin, register anketa post type.
 */
function anketa_init() {
	$args = array(
      'public' => true,
      'label'  => 'Anketa',
      'publicly_queryable' => 'false',
      'menu_icon' => 'dashicons-yes',
      'supports' =>  array( 'title', 'editor', 'comments')
    );
    register_post_type( 'anketa', $args );
}
add_action( 'init', 'anketa_init' );


/**
 * Include admin file.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	   require_once( plugin_dir_path( __FILE__ ) . 'admin.php' );
	   add_action( 'admin_init', 'admin_init' ) ;
     
}
add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'add_anketa_action_links');   
/**
 * Add settings link to plugins page.
 */
function add_anketa_action_links ( $links ) {
  $linksetting = array(
    'settings' =>'<a href="' . admin_url( 'edit.php?post_type=anketa&page=anketa_settings') . '">'.__('Settings').'</a>'      
  );
  return array_merge( $links, $linksetting );
}  

/**
 * Main shortcode for anketa.
 */
function anketa($attributes, $content=null){
	extract(shortcode_atts(array("id" => null, "wid" => false), $attributes));

  $user = wp_get_current_user(); 
 // $ankety = get_posts(
 global $wp_query;
  query_posts( array('post_type' => 'anketa', 'orderby' => 'meta_value', 'meta_key' => 'date_end', 'order'=>'DESC' ));
   //var_dump($wp_query); 
  //var_dump(get_posts(array('post_type' => 'anketa')));      SELECT `hrclubdev_posts`.post_title,`hrclubdev_postmeta`.meta_value FROM `hrclubdev_posts` LEFT JOIN `hrclubdev_postmeta` ON `hrclubdev_posts`.ID = `hrclubdev_postmeta`.post_id WHERE `post_type` = 'anketa' AND `hrclubdev_postmeta`.meta_key = 'date_end' ORDER BY `hrclubdev_postmeta`.meta_value DESC
  /*if($user->ID == 0){
        echo '<div class="anketa_field" style="display: block;">'.__('Musíte sa prihlásiť, aby ste mohli hlasovať.','anketa').'</div>';
         return;
      }    */
  if($id == null){
    $first_time = true;
    $is_active = false;
    $first_time1 = true;
    $poc = 0;
    if ( have_posts() ) :
      while ( have_posts() ) : the_post();
    //foreach($ankety as $apost){
   
      $id = get_the_ID();//$apost->ID;
      $custom = get_post_custom($id);
      if(($custom['date_start'][0] <=  current_time( 'timestamp' ))&&(current_time( 'timestamp' ) <= $custom['date_end'][0])){
        zobraz_anketu($id, $wid);
        $is_active = true;
      }else{
         if($first_time1){
            echo '<div class="anketa_field" style="display: block; margin-bottom: 35px;">'.__('Sorry, there is no active poll.','anketa').'</div>';
         }
      }
      if((current_time( 'timestamp' ) > $custom['date_end'][0])&&(!$wid)){
        
           if($first_time){ 
              echo "<div class='content-article-title'><h2 class='anketa_archive'>".__('Archive','anketa').'</h2></div>';
           
           $ankt = get_post($id,ARRAY_A);
           echo "<div class='anketa_wrap' ";
          // echo ($poc > 0)? 'style="display:none;"' : '';
           echo " ><h2 class='anketa_week'>".__('Planned poll to week ','anketa').date_i18n( 'd.m.Y', $custom['date_start'][0]).' - '.date_i18n( 'd.m.Y', $custom['date_end'][0]).':</h2>';
           show_results($id, $ankt, $widget);
           echo '</div>';
           }else{
             echo '<div class="archive_line"><a href="'.get_permalink().'" >'.get_the_content().'</a></div>';
           }
       /* if(($poc % 10) == 0){     
           echo '<a href="javascript:void(0)" name="show" class="anketa_old_link">'.__('Staršie ankety','anketa').'</a><div class="anketa_wrap" style="display:none;">';
           if($first_time){ 
              echo '<h2 class="anketa_week">'.__('Staršie ankety','anketa').'</h2>';
           }
        }*/
        $first_time = false;
        $poc++;
      }
      $first_time1 = false;
   // } 
      endwhile;
    endif;
                           
    for($i = 0;(ceil($poc/10))>$i;$i++){
      echo('</div>');
    }
  }else{   
     $is_active = true;
     $custom = get_post_custom($id);
      if(($custom['date_start'][0] <=  current_time( 'timestamp' ))&&(current_time( 'timestamp' ) <= $custom['date_end'][0])){
        zobraz_anketu($id, $wid);
        //$is_active = true;
        wp_reset_query();
        comments_template();
      }
      if((current_time( 'timestamp' ) > $custom['date_end'][0])&&(!$wid)){
           $ankt = get_post($id,ARRAY_A);
              echo "<div class='content-article-title'><h2 class='anketa_archive'>".__('Archive','anketa').'</h2>
              <div class="right-title-side"><br/> ';
							echo sprintf(' <a href="%s"><span class="icon-text">&laquo;</span>' . __( ' Back' ) . '</a>', 'javascript:history.go(-1)');								
							echo '	<a href="',home_url(),'"><span class="icon-text">&#8962;</span>', _e("Back To Homepage",THEME_NAME),'</a></div></div>';
          
           echo "<div  class='anketa_wrap'><h2 class='anketa_week'>".__('Poll at week ','anketa').date_i18n( 'd.m.Y', $custom['date_start'][0]).' - '.date_i18n( 'd.m.Y', $custom['date_end'][0]).':</h2>';;
           show_results($id, $ankt, $widget);
           echo '</div>';
          wp_reset_query();
          comments_template();
      }
   
  }
 
  if($wid){
     echo '<a href="'.get_permalink(get_option( 'anketa_page' )).'" class="anketa_link">'.__('Archive of polls','anketa').'</a>';
  }
}

add_shortcode('anketa','anketa');


/**
 * Anketa form.
 */
function zobraz_anketu($id, $widget){
  global $wpdb,$wp_query;;
  $table_name = $wpdb->prefix . "anketa";
  $user = wp_get_current_user();
  $custom = get_post_custom($id);
  $page_id = $wp_query->post->ID;
  
      
      echo '<div class="anketa_wrap">';
        $myvote = $wpdb->get_row("SELECT * FROM $table_name WHERE aid = $id AND uid = $user->ID ");
        $is_ok = false;
        if(isset($_GET['process_anketa'])){
          if(is_numeric($_GET['process_anketa'])&&($_GET['process_anketa']==$id)){
            $ankt = get_post($_GET['process_anketa'],ARRAY_A);
              if(($ankt != null) && ($ankt['post_type']=='anketa')){
              
                 if(isset($_POST['odpoved']) != ($_POST['alt_odpoved'] != '')){
      
                    $odpoved = isset($_POST['odpoved']) ? $_POST['odpoved'] : $_POST['alt_odpoved'] ; 
                    if($myvote == NULL){
                      $rows_affected = $wpdb->insert( $table_name, array( 'aid' => $ankt['ID'], 'uid' => $user->ID, 'answer' => $odpoved ),array('%d','%d','%s')  ); 
                    }else{
                            $timestamp = current_time( 'mysql' );
                            $wpdb->update($table_name, array( 'aid' => $ankt['ID'], 'uid' => $user->ID, 'answer' => $odpoved, 'date' => $timestamp ),array( 'id' => $myvote->id ),array('%d','%d','%s','%s'), array( '%d' ));
                    }
                    $is_ok = true;
                 }
              }
              if($is_ok){    
                //echo '<div class="anketa_notice">'.__('Váš hlas bol uložený.','anketa').'</div><a href="'.get_permalink($page_id).'" class="anketa_link">'.__('Change vote','anketa').'</a>';   //submit_button
                show_results($id, $ankt, $widget);
              }else{ 
                echo '<div class="huge-message"><b class="small-title">'.__('There was an error in processing your voice.','anketa').'</b></div>' ;
              }
          } 
        }else{
        	foreach ($custom as $key => $value) {
        	  	if(substr($key, 0, 7) == "odpoved"){
        	 		$fields[substr($key, 8)] =  $value[0];
        
        		}
        	}
        	ksort($fields);
          if($myvote != NULL){
           //echo '<div class="anketa_notice">'.__('Za túto anketu ste už hlasovali. Chcete zmeniť svoju odpoveď?','anketa').'</div>';
            echo '<div class="anketa_notice">'.__('You have already voted for this poll.','anketa').'</div>';
           $ankt = get_post($myvote->aid,ARRAY_A);
           show_results($id, $ankt, $widget);
          }
          else
          {
                                          // ".(($widget)?'style="font-size: 15px;"':'' )."       ".(($widget)?'style="font-size: 17px; margin-left: 20px;margin-top: 7px;"':'' )."
            echo "<h2 class='anketa_week' >".__('Poll at week ','anketa').date_i18n( 'd.m.Y', $custom['date_start'][0]).' - '.date_i18n( 'd.m.Y', $custom['date_end'][0]).':</h2>';
            echo "<h2 class='anketa_name' >". get_post_field('post_title', $id)."</h2>";
            echo '<div class="anketa_field">'.__('Please enter just one answer.','anketa').'</div>';
          	echo '<form action="'.(($user->ID == 0)? wp_login_url():'?process_anketa='.$id).'" method="post" name="anketa_'.$id.'" id="anketa_'.$id.'">';   //     action="javascript:alert( \'success!\' );"
          	foreach ($fields as $key => $value) {
          		echo'<div class="anketa_row"><input type="radio" id="o_'.$id.'_'.$key.'"name="odpoved" value="'.$value.'"';
              if(($myvote!=null)&&($value == $myvote->answer)){ 
                echo ' checked ';
                $myvote = null;
              }
              echo'> <label for="o_'.$id.'_'.$key.'">'.$value.'</label></div>';
          	}
            if(isset($custom ['alta'][0])&&($custom ['alta'][0])){
          		echo '<div class="anketa_row"><label>'.$custom ['alt_odpoved'][0].'</label><input type="text" name="alt_odpoved" value="'.(($myvote!=null)?$myvote->answer:'').'"> </div>';
          	}
            echo '<input type="hidden" name="uid" value="'.$user->ID.'">';
          	
            if($user->ID == 0 ){
              echo '<div class="anketa_field" style="display: block;">'.sprintf( __('To vote you need to login.  <a href="%s"> Login or</a> or <a href="%s">go to registration.</a>','anketa'), wp_login_url(), wp_registration_url() ).'</div><br>';
              // return;
            }else{ 
              echo '<input class="submit_button" type="submit" value="'.__('Vote','anketa').'"></form>';
            }
          }
        }
        echo '</div>';
      
}

/**
 * Anketa result graph.
 */
function show_results($id, $post, $widget){
   global $wpdb;
   $skrat = ($widget)? 10 : 31;
   $table_name = $wpdb->prefix . "anketa";
   $postmeta = $wpdb->prefix ."postmeta";
   
   $sum = $wpdb->get_row("SELECT COUNT(answer) AS sum FROM $table_name WHERE aid = $id");
   //$anketa = $wpdb->get_results("SELECT * FROM $table_name WHERE aid = $id GROUP BY uid ", 'ARRAY_A');
   //SELECT COUNT(result) AS NoA FROM $table_name
   $sql = "SELECT $postmeta.meta_value, COUNT($table_name.answer) AS NoA FROM $table_name LEFT JOIN $postmeta
    ON $table_name.answer=$postmeta.meta_value WHERE $postmeta.post_id=$id AND $table_name.aid=$id GROUP BY $postmeta.meta_value";
   //var_dump($sql);
  $anketa = $wpdb->get_results($sql, 'ARRAY_A');
    if($anketa && $sum){
      $firsttime = true;
      $toobig = false;
      $wid = count($anketa) ;
      $ine = get_post_meta($id,'alta',true) == 1;
      if($ine){$wid++;}   
      
      foreach($anketa as $key => $odp){
           $toobig = $toobig || (strlen($odp['meta_value']) > $skrat) || ($wid > 8);
      }
      foreach($anketa as $key => $row){ 
        if(!$firsttime){
          $answers .= ',';
          $counts .= ',';
        } 
        /* if($widget && ($wid > 4)){ 
           $answers .=  ($key+1).'.' ;
         }else{
           if($toobig){ //ked je moc dlha odpoved    strlen($row['meta_value']) > $skrat ||
             $answers .=  __('Odpoveď','anketa').' '.($key+1) ;
           }else{
             
              $answers .=  $row['meta_value'];
           }  
         }     */
        $answers .= str_replace (',','',((strlen($row['meta_value']) > $skrat)? substr($row['meta_value'], 0, $skrat).'...':$row['meta_value'])) ;  //skratenie 
          $counts .= (($row['NoA']/ $sum->sum) * 100);
          $firsttime = false;
      }  
      if($ine){
         $sql = "SELECT $table_name.answer, $table_name.date, {$wpdb->users}.display_name, {$wpdb->users}.ID, {$wpdb->users}.user_url  FROM $table_name LEFT JOIN {$wpdb->users} ON {$wpdb->users}.ID = $table_name.uid WHERE $table_name.aid=$id AND answer NOT IN(SELECT $postmeta.meta_value AS answer FROM $postmeta WHERE $postmeta.post_id=$id)";
         $ine_answers = $wpdb->get_results($sql, 'ARRAY_A');
         $answers .= ','.__('iné','anketa');
         $counts .= ','.((count($ine_answers)/ $sum->sum) * 100);
      }                                   
      echo('<h2>'.$post['post_title'].'</h2><div class="anketa_graf">');        // title="'.$post['post_title'].'"        height="'.(($widget)? '400' : (200+(5*$wid))).'" width="'.($wid*200).'"
    
     // $answers = str_replace (',','',$answers);
      //var_dump('[easychart type="vertbar" '.(($widget)? 'height="200" width="400"':'') .' groupnames="'.__("Počet odpovedí v percentách",'anketa').'" valuenames="'.$answers.'" group1values="'.$counts.'" ] ');
      echo do_shortcode('[easychart type="vertbar" '.(($widget)? 'height="200" width="400"':'') .' groupnames="'.__("Počet odpovedí v percentách",'anketa').'" valuenames="'.$answers.'" group1values="'.$counts.'" ] ');
      if($toobig ||($widget && ($wid > 4))){
        foreach($anketa as $key => $answ){
           echo'<a href="javascript:void(0)" class="anketa_odp" style="width:'.floor(90/$wid).'%;" title="'.$answ['meta_value'].'">&nbsp;</a>';
        }
      }
      echo"</div>";
      
      if($ine && $ine_answers){
        echo '<h4>'.__('Custom answers of respondents','anketa').'</h4><ul class="comments comment-block">';
        foreach($ine_answers as $answ){
        
          ?><li class="comment anketa_ine">
            <div class="commment-content">
              <div class="user-avatar">
			         <img src="<?php echo get_avatar_url(get_avatar( $answ['ID'], 60));?>"  alt="<?php printf(__('%1$s', THEME_NAME), $answ['display_name']);?>" title="<?php printf(__('%1$s', THEME_NAME), $answ['display_name']);?>" class="user-avatar setborder"/>
		          </div>
              <strong class="user-nick">
                 <?php//<a href=" if($answ['user_url']) { echo $answ['user_url'];} else { echo "#"; } ">  ?>
                <?php echo $answ['display_name']; ?>
                <!-- </a>  -->
              </strong>
              <span class="time-stamp"><?php printf(__(' %1$s, %2$s', THEME_NAME), date_i18n("F d", strtotime($answ['date'])) , date_i18n("H:i", strtotime($answ['date'])));?></span>
              <div class="comment-text">
                <?php echo $answ['answer']; ?>
              </div>
            </div>
            </li>
            <?php
           //echo'<p class="anketa_ine"><strong>'.$answ['display_name'].':</strong> '.$answ['answer'].'</p>';
        }
        echo '</ul>';
      }
    }
}



/**
 * Anketa widget class.
 */
class Anketa_widget extends WP_Widget {

    function __construct() {
  		parent::__construct(
  			'Anketa_widget', // Base ID
  			__('Anketa_widget', 'anketa'), // Name
  			array( 'description' => __( 'Polls widget, which shows the individual polls.', 'anketa' ), 'aid' => '' ) // Args
  		);
	  }

    public function widget( $args, $instance )
    {

        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
            $str = '[anketa wid=true '.((isset($instance['aid']))? 'id='.$instance['aid'] :'').']';
            echo do_shortcode($str);
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance )
    {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Poll', 'anketa' );
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'aid' ); ?>"><?php _e( 'ID:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'aid' ); ?>" name="<?php echo $this->get_field_name( 'aid' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'aid' ] ); ?>" />
        </p>
    <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance )
    {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['aid'] = ( ! empty( $new_instance['aid'] ) ) ? strip_tags( $new_instance['aid'] ) : '';

        return $instance;
    }
}
    
function register_anketa_widget() {
    register_widget( 'Anketa_widget' );
}
add_action( 'widgets_init', 'register_anketa_widget' );


/*add_filter( 'bbp_get_topic_post_type', 'add_another_post_type');

function add_another_post_type($topic_post_type){
  var_dump($topic_post_type);
   return array_merge( (array)$topic_post_type, array('post'));
}     */
//remove_action( 'bbp_register', 'bbp_register_taxonomies', 6 );


 
?>