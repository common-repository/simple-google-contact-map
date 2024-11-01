<?php
/*
Plugin Name: Simple Google Conatct Map
Plugin URI: http://www.phpboys.in/simple-google-contact-map-plugin-wordpress.html
Description: Simple Google map for conatct page
Version: 1.0
Author: Praveen Punniyamoorthy
Author URI: http://www.phpboys.in
Licenses: GPL2
*/

// Define constant for plugin path
define('GOOGLE_MAP_PATH', plugin_dir_url( __FILE__ ));

function sgc_map_menu() 
{
    add_options_page( 'Sgc map Plugin Options', 'Google Map - SGCM', 'manage_options', 'sgc-map-option', 'sgc_map_plugin_options' );
}

function sgc_map_plugin_options() 
{
    if ( !current_user_can( 'manage_options' ) )  
    {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    include __DIR__."/options.php";
}

//inline settings menu on admin section
function sgcm_settings_link( $links ) 
{
    $settings_link = '<a href="'.admin_url( 'options-general.php?page=sgc-map-option' ).'">Settings</a>';
    array_push( $links, $settings_link );
    return $links;
}

$plugin = plugin_basename( __FILE__ );

add_action( 'admin_menu', 'sgc_map_menu');
add_filter( "plugin_action_links_$plugin", 'sgcm_settings_link' );

function sgcm_shortcode($atts=array('src'=>'shortcode')) 
{
	$map_info	=	unserialize(get_option('sgcm_options'));
    $address	=	urlencode(
                    $map_info['address1'].' '.$map_info['address2'].' '.$map_info['city'].' '.$map_info['state'].' '.$map_info['pincode'].' '.$map_info['country'] );									                      	
    $lang = substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
     if (!$lang) 
     {
       $lang = 'en';
     }
	 
	 if($_SERVER['SERVER_PROTOCOL']=='https')
	 {
		$protocal	=	'https://';
	 }
	 else
	 {
		$protocal	=	'http://';
	 }
     
    $map_url	=	$protocal.'maps.google.com/maps??hl='.$lang.'&amp;ie=utf8&amp;output=embed&amp;iwd=1&amp;mrt=loc&amp;t='.$map_info['map_type'].'&amp;q='.$address.'&amp;ll='.$map_info['map_lat'].','.$map_info['map_long'].'&amp;z='.$map_info['map_zoom'].'';																							        
	if($atts['src']	!= 'widget')
    {	
        $output		=	'<iframe width="'.$map_info['map_width'].'" height="'.$map_info['map_height'].'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$map_url.'"></iframe>';
    }
    else
    {
        $output		=	'<iframe width="'.$atts['width'].'" height="'.$atts['height'].'"frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$map_url.'"></iframe>';
    }
	
	return $output;
}
 
add_shortcode( 'sgcm_map', 'sgcm_shortcode');

function sgcm_backend_styles()
{
    wp_enqueue_style( 'sgcm', GOOGLE_MAP_PATH . 'css/sgcm-map.css'); 
}

add_action('admin_init', 'sgcm_backend_styles');



/*Widget For Simple google contact Map      */
class Sgcm_Widget extends WP_Widget {
 
  public function __construct() 
  {
      $widget_ops = array('classname' => 'Sgcm_Widget', 'description' => 'Simple Widget for contact map' );
      $this->WP_Widget('Sgcm_Widget', 'Google Map Widget - SGCM', $widget_ops);
  }
  
  function widget($args, $instance) 
  {
    //  Extracting the arguments + getting the values
    extract($args, EXTR_SKIP);
    $title	= empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
    $width	= empty($instance['map_width']) ? 300	: $instance['map_width'];
    $height = empty($instance['map_height']) ? 300	: $instance['map_height'];
    
    // Before widget code, if any
    echo (isset($before_widget)?$before_widget:'');
   
    // The title and the text output
    if (!empty($title))
      echo $before_title . $title . $after_title;
    
        // Call widget method
        $arr = array();
        $arr["title"] 		= $title;
        $arr["width"]		= $width;
        $arr["height"] 		= $height;
        $arr["src"] 		= 'widget';
        
        echo do_shortcode( '[sgcm_map width='.$arr["width"].' height='.$arr["height"].' 													                                src='.$arr["src"].']' );
   
    // After widget code, if any  
    echo (isset($after_widget)?$after_widget:'');
  }
 
 public function form( $instance ) 
 {
    // Extract the data from the instance variable
     $instance	= wp_parse_args( (array) $instance, array( 'title' => '' ) );
     $title		= $instance['title'];
     $wg_width	= $instance['map_width'];
     $wg_height	= $instance['map_height'];
   
     
     ?>
     <!-- Widget Title field START -->
     <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Title: 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
               name="<?php echo $this->get_field_name('title'); ?>" type="text" 
               value="<?php echo attribute_escape($title); ?>" />
      </label>
      </p>
      <!-- Widget Title field END -->
   
     <!-- Widget Text field START -->
     
      <p>
      <label for="<?php echo $this->get_field_id('map_width'); ?>">Map Width: 
        <input class="widefat" id="<?php echo $this->get_field_id('map_width'); ?>" 
               name="<?php echo $this->get_field_name('map_width'); ?>" type="text" 
               value="<?php echo attribute_escape($wg_width); ?>" />
      </label>
      </p>
      
      
       <p>
      <label for="<?php echo $this->get_field_id('map_height'); ?>">Map Height: 
        <input class="widefat" id="<?php echo $this->get_field_id('map_height'); ?>" 
               name="<?php echo $this->get_field_name('map_height'); ?>" type="text" 
               value="<?php echo attribute_escape($wg_height); ?>" />
      </label>
      </p>
      <!-- Widget Text field END -->
     <?php
   
  }
 
  function update($new_instance, $old_instance) 
  {
    $instance = $old_instance;
    $instance['title']		= $new_instance['title'];
    $instance['map_width']	= $new_instance['map_width'];
    $instance['map_height']	= $new_instance['map_height'];
    
    return $instance;
  }
  
}

add_action( 'widgets_init', create_function('', 'return register_widget("Sgcm_Widget");'));
