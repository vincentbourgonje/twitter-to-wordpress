<?php
/**
 * @package Twitter-to-Wordpress
 *
 * pctw_load_scripts.php
 * Load frontend and backend css + javascript files 
 */

	
/*---------------------------------------------------------------------------------------------------
	LOAD JAVASCRIPT FOR PLUGIN WHEN ADMIN
---------------------------------------------------------------------------------------------------*/		
	function pctw_admin_load_scripts() {
	   // wp_enqueue_script( 'tsce-js', plugins_url() . "/tsfo-extranet/js/pctw_scripts.js", array(), pctw_VERSION, true );
		wp_enqueue_style( 'pctw-admin-styles', plugins_url() . "/twitter-to-wordpress/css/pctw_admin_styles.css", array(), PCTW_VERSION, "all" );

		// enqueue scripts for the calendar
	    wp_enqueue_script( 'air-datepicker-js', plugins_url() . "/twitter-to-wordpress/library/air-datepicker/js/datepicker.min.js", array(), PCTW_VERSION, true );
		wp_enqueue_style('air-datepicker-css', plugins_url() . '/twitter-to-wordpress/library/air-datepicker/css/datepicker.min.css', array(), PCTW_VERSION, 'all');
		// include language file for date picker
	    wp_enqueue_script( 'air-datepicker-en-js', plugins_url() . "/twitter-to-wordpress/library/air-datepicker/js/i18n/datepicker.en.js", array(), PCTW_VERSION, true );

	}

	function pctw_app_scripts() {
	    wp_enqueue_script( 'pctw-js', plugins_url() . "/twitter-to-wordpress/js/pctw_scripts.js", array(), PCTW_VERSION, true );
		wp_enqueue_style('pctw-styles', plugins_url() . '/twitter-to-wordpress/css/pctw_styles.css', array(), PCTW_VERSION, 'all');
	}
?>