<?php
/**
 * @package Twitter-to-Wordpress
 */
/*
	Plugin Name: Twitter to Wordpress
	Plugin URI: https://plugincreators.com/plugins/twitter-to-wordpress
	Description: 
	Version: 1.0.0
	Author: Plugin Creators
	Author URI: http://plugincreators.com/
	Text Domain: plcr
*/

	if ( !function_exists( 'add_action' ) ) {
		_e('Cannot call this plugin directly - install it in Wordpress and use it from there','plcr');
		exit;
	}

	if (defined('PCTW_PLUGIN_URL')) {
	   wp_die('It seems that other version of TSFO Calendar is active. Please deactivate it before use this version');
	}

/*---------------------------------------------------------------------------------------------------
	DEFINE CONSTANTS
---------------------------------------------------------------------------------------------------*/
	define('PCTW_VERSION', '1.0.0');
	define('PCTW_DB_VERSION', '1.0.0');
	define('PCTW_PLUGIN_URL', plugin_dir_url(__FILE__));
	define('PCTW_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('PCTW_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
	define('PCTW_PLUGIN_FILE', basename(__FILE__));
	define('PCTW_PLUGIN_FULL_PATH', __FILE__);
	define('PCTW_IMAGE_PATH', plugin_dir_url( __FILE__ ) . 'img/');
	define('PCTW_LOG_DIR', plugin_dir_path( __FILE__ ) . '/log');
	define('PCTW_SITE_HOME', str_replace('/wp-content/themes','',get_theme_root()) );
	define('PCTW_GOOGLE_APIKEY', 'AIzaSyB00a38JDH8KbQ0gAnnaBY348HUNItMdoI');

/*---------------------------------------------------------------------------------------------------
	INCLUDE THE PLUGIN FILES
---------------------------------------------------------------------------------------------------*/
	require_once(PCTW_PLUGIN_DIR .'include/pctw_generic_functions.php'); // generic helper functions

	require_once(PCTW_PLUGIN_DIR .'include/pctw_posttypes.php'); 		// normal posttype definition
	require_once(PCTW_PLUGIN_DIR .'include/pctw_shortcodes.php'); 		// normal posttype definition	
	require_once(PCTW_PLUGIN_DIR .'include/pctw_autorisations.php'); 	// autorisation functions - init roles and capabilities
	require_once(PCTW_PLUGIN_DIR .'include/pctw_load_scripts.php' ); 	// load plugin js and css files
	require_once(PCTW_PLUGIN_DIR .'include/pctw_admin_page.php' ); 	// setup the admin page
	require_once(PCTW_PLUGIN_DIR .'include/pctw_init_plugin.php' ); 	// initial init

	// include metabox plugin
	require_once(PCTW_PLUGIN_DIR . '/library/meta-box/meta-box.php'); // Path to the metabox library

	// include twitter oauth library
	require_once (PCTW_PLUGIN_DIR . '/library/twitteroauth/twitteroauth.php');

	// include meta box definitions for custom posttypes
	require_once(PCTW_PLUGIN_DIR . 'include/pctw_meta_fields.php'); 	// all fields


	// prevent errors with user functions
	require_once(PCTW_SITE_HOME . '/wp-includes/pluggable.php');

/*---------------------------------------------------------------------------------------------------
	ACTIONS & HOOKS
---------------------------------------------------------------------------------------------------*/	
	register_activation_hook( __FILE__, 'pctw_activate' );
	register_deactivation_hook( __FILE__, 'pctw_deactivate' );	

	add_action( 'admin_enqueue_scripts', 'pctw_admin_load_scripts' );
	add_action( 'wp_enqueue_scripts', 'pctw_app_scripts' );	

	add_action( 'init', 'register_pctw_posttypes');	 // register posttypes
//	add_action( 'init', 'pctw_init_functions');		

	// Translations
	load_plugin_textdomain( 'plcr', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	// taxonomies meta
	add_action( 'trip_edit_form_fields', 'trip_taxonomy_custom_fields', 10, 2 );
	add_action( 'edited_trip', 'save_taxonomy_custom_fields', 10, 2 );

/*---------------------------------------------------------------------------------------------------
	AJAX CALLS
---------------------------------------------------------------------------------------------------*/

	// add_action( 'wp_ajax_pctw_ajax_add_task', 'pctw_ajax_add_task' );
 	// add_action( 'wp_ajax_nopriv_pctw_ajax_add_task', 'pctw_ajax_add_task' );	
 	
/*---------------------------------------------------------------------------------------------------
	FILTERS
---------------------------------------------------------------------------------------------------*/	

	// add_filter();
	
/*---------------------------------------------------------------------------------------------------
	DECLARE SHORTCODES
---------------------------------------------------------------------------------------------------*/	

	function pctw_init_functions() {

	}

?>