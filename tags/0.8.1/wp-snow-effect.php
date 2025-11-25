<?php
/*
Plugin Name: WP Snow Effect
Plugin URI: http://www.wpmaniax.com
Description: Add nice looking animation effect of falling snow to your WP site and enjoy winter.
Author: Csaba Kissi
Version: 0.8.1
Author URI: http://www.wpmaniax.com/ 
*/

function snow_scripts() {
   wp_enqueue_script('jsnow', plugins_url('js/jsnow.js', __FILE__), array('jquery'), '1.3');
   wp_enqueue_script('snow-script', plugins_url('/js/script.js',__FILE__), array('jquery'), '1.0.0');
}
add_action('wp_enqueue_scripts', 'snow_scripts');
?>