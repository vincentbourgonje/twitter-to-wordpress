<?php

	// Hook for adding admin menus
	add_action('admin_menu', 'pctw_add_admin_pages');
	
	function pctw_add_admin_pages() {
		// create the page in the menu
		add_options_page( 'Twitter to Wordpress', 'Twitter to Wordpress', 'manage_options', 'tw-to-wp', 'pctw_settings_page');
	}

	function pctw_settings_page() {

		// set adminpage template
		$file = PCTW_PLUGIN_DIR . 'views/pctw_admin_options.php';

		if ( !empty($_POST["issubmitted"]) ) {

			

			// if form has been submitted save the options
			if ( !empty($_POST["twitteraccount"]) ) { update_option("pctw_setting_twitteraccount", trim($_POST["twitteraccount"]), false); }
			if ( !empty($_POST["addtags"]) ) { update_option("pctw_setting_addtags", trim($_POST["addtags"]), false); }

			if ( !empty($_POST["homeaddress"]) ) { 
				update_option("pctw_setting_homeaddress", trim($_POST["homeaddress"]), false); 
				$home_coords = implode(',', pctw_get_coordinates($_POST["homeaddress"]));

				//echo '<pre>'; var_dump($home_coords); echo '</pre>';
				if ($home_coords !== false) {
					update_option("pctw_setting_home_coords", trim($home_coords), false); 
				}
			}
			if ( !empty($_POST["homeradius"]) ) { update_option("pctw_setting_homeradius", trim($_POST["homeradius"]), false); }			



			if ( !empty($_POST["consumerkey"]) ) { update_option("pctw_setting_consumer_key", trim($_POST["consumerkey"]), false); }
			if ( !empty($_POST["consumersecret"]) ) { update_option("pctw_setting_consumer_secret", trim($_POST["consumersecret"]), false); }
			if ( !empty($_POST["accesstoken"]) ) { update_option("pctw_setting_access_token", trim($_POST["accesstoken"]), false); }
			if ( !empty($_POST["accesstokensecret"]) ) { update_option("pctw_setting_access_tokensecret", trim($_POST["accesstokensecret"]), false); }


		}

		include( $file );
	}

?>