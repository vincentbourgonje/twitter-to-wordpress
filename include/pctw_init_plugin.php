<?php
/**
 * @package Twitter-to-Wordpress
 *
 * pctw_init_plugin.php
 * Scripts for initialization when (de)activated 
 */

	function pctw_activate() {
	    if ( !wp_next_scheduled( 'pctw_cron_hook' ) ) {
	         //schedule the event to run hourly
	        wp_schedule_event( time(), 'hourly', 'pctw_cron_hook' );
	    }
	}

	function pctw_deactivate() {
	    if ( wp_next_scheduled( 'pctw_cron_hook' ) ) {
	         //schedule the event to run hourly
	        wp_unschedule_event( time(), 'pctw_cron_hook' );
	    }
	}

?>