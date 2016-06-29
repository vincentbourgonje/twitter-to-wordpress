<?php
/**
 * @package Twitter-to-Wordpress
 *
 * pctw_init_plugin.php
 * Scripts for initialization when (de)activated 
 */

	function pctw_activate() {
		// init hook for scheduling tweet retrieval every 15 mins
	    if ( !wp_next_scheduled( 'pctw_read_lastest_tweets' ) ) {
	        wp_schedule_event( time(), 'every_fifteen_minutes', 'pctw_read_lastest_tweets' );
	    }
	    // init hook for reset trip start- and enddates once a day
	    if ( !wp_next_scheduled( 'pctw_reset_tripdates' ) ) {
	        wp_schedule_event( time(), 'daily', 'pctw_reset_tripdates' );
	    }	    
	    	        
	}

	function pctw_deactivate() {
		// remove hook for tweet retrieval scheduler
	    if ( wp_next_scheduled( 'pctw_read_lastest_tweets' ) !== false ) {
	        wp_unschedule_event( wp_next_scheduled( 'pctw_read_lastest_tweets' ), 'pctw_read_lastest_tweets' );
	    }
	    // remove hook for reset trip start- and enddates
	    if ( wp_next_scheduled( 'pctw_reset_tripdates' ) !== false ) {
	        wp_unschedule_event( wp_next_scheduled( 'pctw_reset_tripdates' ), 'pctw_reset_tripdates' );
	    }	    
	}


?>