<?php 

/**
 * Plugin Name:       Ultra Excerpts
 * Plugin URI:        http://wordpress.org/plugins/ultra-excerpts
 * Description:       Customize your wordpress excerpts.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Exsamp Inc
 * Author URI:        http://www.exsamp.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
 
 // Define Global Variables for the plugin.
 
 $GLOBALS['ultra_excerpt_version'] = '1.0.0';
 $GLOBALS['ultra_excerpt_slug'] = 'ultra_excerpt';


 // Activation Hook
 function ultra_excerpt_activate(){
    register_uninstall_hook( __FILE__, 'ultra_excerpt_uninstall' );

 }
 
 register_activation_hook( __FILE__, 'ultra_excerpt_activate' );
 
 
 
 require_once 'includes/classes/ultra_excerpts.php';

// Ultra Excerpts instance initialization

function ultra_excerpts_init() {
	global $ultra_excerpts;
	$ultra_excerpts = new ultra_excerpts( __FILE__ );
}
add_action( 'init', 'ultra_excerpts_init', 5 );


 // Uninstall Hook
function ultra_excerpt_uninstall(){
     delete_option( 'ultra_excerpt_fields' );
}