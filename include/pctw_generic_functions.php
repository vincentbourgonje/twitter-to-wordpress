<?php

/**
 *	GENERIC LOGFUNCTION
 *	 
 *	@package Twitter-to-Wordpress
 *	@param 	 $logtext - The text to be logged
 *			 $logtype - The logfile where the text needs to be added to
 *  @return  none
 *			
 *	descr	This function writes log text to a specific logfile; Timestamps and linebreaks 
 * 			are being added
 */
	if (!function_exists('pctw_log')) {
		function pctw_log($logtext, $logtype = "default") {
		

			// CHECK IF THE TEMPLATE DIR EXISTS - IF NOT CREATE IT
			if (!is_dir(PCTW_LOG_DIR)) {
				mkdir(PCTW_LOG_DIR,0770,true);	
			}

			// logtype bepaalt naar welke logfile er geschreven wordt;
			switch($logtype) {
				case "default":
					$logfile = PCTW_LOG_DIR . '/pctw-log.txt';
					break;
				case "cron":
					$logfile = PCTW_LOG_DIR . '/pctw-cronlog.txt';
					break;
				case "ajax":
					$logfile = PCTW_LOG_DIR . '/pctw-ajaxlog.txt';
					break;
				case "geoapi":
					$logfile = PCTW_LOG_DIR . '/pctw-geoapi.txt';
					break;				
				default: 
					$logfile = PCTW_LOG_DIR . '/pctw-log.txt';
					break;
			}
	
			// add timestamp 
			$logtext = date("d-m-Y H:i:s") . ' - ' . $logtext . "\n"; // \n with double quotes is new line single quotes does not work!
			
			// Write the contents to the file, 
			// using the FILE_APPEND flag to append the content to the end of the file
			// and the LOCK_EX flag to prevent anyone else writing to the file at the same time
			file_put_contents($logfile, $logtext, FILE_APPEND | LOCK_EX);
						
		}
	}

	/**
	 * Check if role exists
	 **/

	if (!function_exists('role_exists')) {
		function role_exists( $role ) {

		  if( ! empty( $role ) ) {
		    return $GLOBALS['wp_roles']->is_role( $role );
		  }
		  
		  return false;
		}
	}

	/**
	 * Get All Tweets
	 */
	function pctw_clean_all_tweets() {

		$query_args = array( 'post_type' => 'pctw_tweets', 'order_by' => 'date', 'order' => 'DESC', 'posts_per_page' => '100' );

		// The Query
		$the_query = new WP_Query( $query_args );

		// The Loop
		if ( $the_query->have_posts() ) {
			echo '<ul style="list-style: none;">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$clean_title = get_the_title();

				$http_start_pos = strpos($clean_title,'http://');
				if ( $http_start_pos !== false ) {
					$http_end_pos = strpos($clean_title,' ',$http_start_pos);
					if ($http_end_pos == false) { $http_end_pos = strlen($clean_title); }

					$clean_title = substr_replace( $clean_title, '', $http_start_pos, ($http_end_pos-$http_start_pos) );

				}
				$post_date = get_the_date('l j F Y') . ' om ' . get_the_date('H:i');
				echo '<li style="float: left; border: 1px solid #efefef; padding: 10px;">' . get_the_post_thumbnail(get_the_id(),"full") . '<h3>' . $clean_title . '</h3><p>(' . $post_date . ')</p></li>';
			}
			echo '</ul>';
		} else {
			// no posts found
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	} 

	/**
	 * Get All Tweets
	 */
	function pctw_load_all_tweets() {


	    if (!function_exists('curl_init')) {
	        error_log('The DG Twitter to blog plugin require CURL libraries');
	        return;
	    }


	    echo '<h1>start import...</h1>';

	    // retrieve the settings
		$pctw_twitter_account 	= get_option("pctw_setting_twitteraccount", 0);
		$pctw_addtags 			= get_option("pctw_setting_addtags", 0); 
		$pctw_consumerkey 		= get_option("pctw_setting_consumer_key", 0);
		$pctw_consumersecret 	= get_option("pctw_setting_consumer_secret", 0);
		$pctw_accesstoken 		= get_option("pctw_setting_access_token", 0);
		$pctw_accesssecret 		= get_option("pctw_setting_access_tokensecret", 0);	    


	    if (!empty($pctw_consumerkey) && !empty($pctw_consumersecret) && !empty($pctw_accesstoken) && !empty($pctw_accesssecret)) {
	        $connection = new TwitterOAuth($pctw_consumerkey, $pctw_consumersecret, $pctw_accesstoken, $pctw_accesssecret);
	    }

	    $pctw_exclusions = array();
	    $mega_tweet = array();

        $parameters = array(
            'q' => $pctw_twitter_account,
            'include_entities' => true,
            'count' => 50
        );

        
        $lastid = get_option('pctw_last_tweet_id', false);
        if ($lastid == false) {
        	//$lastid = pctw_get_latest_tweet();	
			//$parameters['since_id'] = '0';
        } else {
        	$parameters['max_id'] = $lastid;
        }

        $count = 0;

        //echo '<pre><strong>parameters: </strong> '; var_dump($parameters); echo '</pre>';

        //$dg_tw_data = $connection->get('search/tweets', $parameters);
        $pctw_feed = $connection->get('statuses/user_timeline', $parameters);

		//echo '<pre><strong>pctw_feed: </strong> '; var_dump($pctw_feed); echo '</pre>';

		$goon = true;
        if ($goon) { // check here if object holds any data

	        foreach ($pctw_feed as $thistweet) {

	        	// for debug 
				pctw_log(print_r($thistweet, true));
				$count++;

				$tweet_id 			= $thistweet->id_str;
				$tweet_createdate 	= $thistweet->created_at;
				$tweet_text			= $thistweet->text;

				// check if there is a post already with this tweet id
				// if so skip and continue to the next tweet
				if (!pctw_tweet_exists($tweet_id)) {

					$clean_title = $tweet_text;
					$org_title = $tweet_text;

					$http_start_pos = strpos($clean_title,'http://');
					if ( $http_start_pos !== false ) {
						$http_end_pos = strpos($clean_title,' ',$http_start_pos);
						if ($http_end_pos == false) { $http_end_pos = strlen($clean_title); }
						$clean_title = substr_replace( $clean_title, '', $http_start_pos, ($http_end_pos-$http_start_pos) );
					}

					$http_start_pos = strpos($clean_title,'https://');
					if ( $http_start_pos !== false ) {
						$http_end_pos = strpos($clean_title,' ',$http_start_pos);
						if ($http_end_pos == false) { $http_end_pos = strlen($clean_title); }
						$clean_title = substr_replace( $clean_title, '', $http_start_pos, ($http_end_pos-$http_start_pos) );
					}				

					$tweet_text = $clean_title;


					// check if there is any media
					$tweet_entities = $thistweet->entities;
					$tweet_entities_array = (Array)$tweet_entities;
					if ( array_key_exists ( 'media',  $tweet_entities_array)) {
						$tweet_image = $thistweet->entities->media["0"]->media_url;
					} else {
						$tweet_image = false;
					}

					$tweet_place_name	= $thistweet->place->name;
					$tweet_place_countrycode = $thistweet->place->country_code;
					$tweet_place_country = $thistweet->place->country;

					$tweet_place_lng1	= $thistweet->place->bounding_box->coordinates[0][0][0];
					$tweet_place_lat1	= $thistweet->place->bounding_box->coordinates[0][0][1];

					$tweet_place_lng2	= $thistweet->place->bounding_box->coordinates[0][1][0];
					$tweet_place_lat2	= $thistweet->place->bounding_box->coordinates[0][1][1];

					$tweet_place_lng3	= $thistweet->place->bounding_box->coordinates[0][2][0];
					$tweet_place_lat3	= $thistweet->place->bounding_box->coordinates[0][2][1];

					$tweet_place_lng4	= $thistweet->place->bounding_box->coordinates[0][3][0];
					$tweet_place_lat4	= $thistweet->place->bounding_box->coordinates[0][3][1];

					$tweet_place_geo_tweet = $thistweet->coordinates;

					if (empty($tweet_place_geo_tweet)) {
						$tweet_place_geo = pctw_get_coordinates($tweet_place_name . ', ' . $tweet_place_countrycode);
					} else {
						$tweet_place_geo = array($tweet_place_geo_tweet->coordinates[1], $tweet_place_geo_tweet->coordinates[0]);
					}

					$location_array = array( 
							$tweet_place_lat1 . ',' . $tweet_place_lng1,
							$tweet_place_lat2 . ',' . $tweet_place_lng2,
							$tweet_place_lat3 . ',' . $tweet_place_lng3,
							$tweet_place_lat4 . ',' . $tweet_place_lng4,					
						);			

					$newformat = date('d-m-Y H:i',strtotime($tweet_createdate));

					echo ' ' . $newformat . ' / ' . $tweet_text . '<br>';
					echo '<hr>';

					// create the new post array
					$new_post = array(
						"post_date" => date('Y-m-d H:i:s',strtotime($tweet_createdate)),
						"post_title" => $tweet_text,
						'post_status' => 'publish',
						'post_type' => 'pctw_tweets',
						'comment_status' => 'open',
						);

					// create the new post and save the id
					$new_postid = wp_insert_post($new_post);

					// write all the new post meta data (if available)
					if(isset($tweet_id)) { update_post_meta($new_postid,'tweet_id', $tweet_id); }
					if(isset($tweet_place_name)) { update_post_meta($new_postid,'tweet_place_name', $tweet_place_name); }
					if(isset($tweet_place_countrycode)) { update_post_meta($new_postid,'tweet_place_countrycode', $tweet_place_countrycode); }
					if(isset($tweet_place_country)) { update_post_meta($new_postid,'tweet_place_country', $tweet_place_country); }
					if(isset($tweet_place_geo)) { update_post_meta($new_postid,'tweet_place_geo', $tweet_place_geo[0] . ',' . $tweet_place_geo[1]); }
					if(isset($location_array)) { update_post_meta($new_postid,'tweet_place_geo_poly', $location_array); }
					if(isset($org_title)) { update_post_meta($new_postid,'tweet_place_org_title', $org_title); }

					// prepare the image to write (if available)
					if($tweet_image !== false) {
						// Add Featured Image to Post
						$image_url  = $tweet_image;
						$upload_dir = wp_upload_dir(); // Set upload folder
						$image_data = file_get_contents($image_url); // Get image data
						$filename   = basename($image_url); // Create image file name

						// Check folder permission and define file location
						if( wp_mkdir_p( $upload_dir['path'] ) ) {
						    $file = $upload_dir['path'] . '/' . $filename;
						} else {
						    $file = $upload_dir['basedir'] . '/' . $filename;
						}

						// Create the image  file on the server
						file_put_contents( $file, $image_data );

						// Check image file type
						$wp_filetype = wp_check_filetype( $filename, null );

						// Set attachment data
						$attachment = array(
						    'post_mime_type' => $wp_filetype['type'],
						    'post_title'     => sanitize_file_name( $filename ),
						    'post_content'   => '',
						    'post_status'    => 'inherit'
						);

						// Create the attachment
						$attach_id = wp_insert_attachment( $attachment, $file, $new_postid );

						// Include image.php
						require_once(ABSPATH . 'wp-admin/includes/image.php');

						// Define attachment metadata
						$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

						// Assign metadata to attachment
						wp_update_attachment_metadata( $attach_id, $attach_data );

						// And finally assign featured image to post
						set_post_thumbnail( $new_postid, $attach_id );

					}
					echo $count;
				} else {
	        		echo '<strong>DOUBLE: </strong>' . $tweet_id . ' - ' . $tweet_text; 
	       		}

				$lastid = $thistweet->id_str;
	        } 


	    } else {
	    	echo 'no tweets found';
	    }

        if (strlen(trim($lastid))>0) {
            update_option('pctw_last_tweet_id', $lastid);
        }

	}


	/**
	 * Get latest tweet
	 * pctw_get_latest_tweet()
	 */
	function pctw_get_latest_tweet() {

	    if (!function_exists('curl_init')) {
	        error_log('The DG Twitter to blog plugin require CURL libraries');
	        return;
	    }

	    // retrieve the settings
		$pctw_twitter_account 	= get_option("pctw_setting_twitteraccount", 0);
		$pctw_addtags 			= get_option("pctw_setting_addtags", 0); 
		$pctw_consumerkey 		= get_option("pctw_setting_consumer_key", 0);
		$pctw_consumersecret 	= get_option("pctw_setting_consumer_secret", 0);
		$pctw_accesstoken 		= get_option("pctw_setting_access_token", 0);
		$pctw_accesssecret 		= get_option("pctw_setting_access_tokensecret", 0);	    


	    if (!empty($pctw_consumerkey) && !empty($pctw_consumersecret) && !empty($pctw_accesstoken) && !empty($pctw_accesssecret)) {
	        $connection = new TwitterOAuth($pctw_consumerkey, $pctw_consumersecret, $pctw_accesstoken, $pctw_accesssecret);
	    }

	    $pctw_exclusions = array();
	    $mega_tweet = array();
        $lastid = get_option('pctw_last_tweet_id', 0);

        $parameters = array(
            'q' => $pctw_twitter_account,
            'include_entities' => true,
            'count' => 1
        );

        $count = 0;

        $pctw_feed = $connection->get('statuses/user_timeline', $parameters);

        return $pctw_feed[0]->id_str;
	}

	function pctw_import_new_tweets() {
		pctw_log('Importing new tweets');
	}

	/**
	 * Delete a tweet
	 * pctw_delete_tweet()
	 * @param $tweet_id int
	 */
	function pctw_delete_tweet( $tweet_id ) {

	    if (!function_exists('curl_init')) {
	        error_log('The DG Twitter to blog plugin require CURL libraries');
	        return;
	    }

	    // retrieve the settings
		$pctw_twitter_account 	= get_option("pctw_setting_twitteraccount", 0);
		$pctw_addtags 			= get_option("pctw_setting_addtags", 0); 
		$pctw_consumerkey 		= get_option("pctw_setting_consumer_key", 0);
		$pctw_consumersecret 	= get_option("pctw_setting_consumer_secret", 0);
		$pctw_accesstoken 		= get_option("pctw_setting_access_token", 0);
		$pctw_accesssecret 		= get_option("pctw_setting_access_tokensecret", 0);	    


	    if (!empty($pctw_consumerkey) && !empty($pctw_consumersecret) && !empty($pctw_accesstoken) && !empty($pctw_accesssecret)) {
	        $connection = new TwitterOAuth($pctw_consumerkey, $pctw_consumersecret, $pctw_accesstoken, $pctw_accesssecret);
	    }

        $parameters = array(
            'id' => $tweet_id
        );

        $count = 0;

        $pctw_result = $connection->get('statuses/destroy', $parameters);

        return;
	}	

	/**
	 * Geocode an address passed 
	 * @param address - notation as: street housenr, zipcode, city
	 * @return array(lat,lng)
	 */
	function pctw_get_coordinates($address) {

 		//next example will recieve all messages for specific conversation
 		$address = str_replace(' ', '+', $address);

		$service_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . PCTW_GOOGLE_APIKEY;

		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);

		if ($curl_response === false) {
		    $info = curl_getinfo($curl);
		    curl_close($curl);
		    die('error occured during curl exec. Additional info: ' . var_export($info));
		}

		curl_close($curl);

		$decoded = json_decode($curl_response);

		// log results
		pctw_log(print_r($decoded, true),"geoapi");		

		if ( $decoded->status == "OK" ) {
			$latitude = $decoded->results[0]->geometry->location->lat;
			$longitude = $decoded->results[0]->geometry->location->lng;
			return array($latitude, $longitude);
		} else {
			return false;
		}
		
	}

	/**
	 * Check if a Tweet ID already has been stored
	 */
	function pctw_tweet_exists( $tweet_id ) {
		$tweet_args = array(
			'post_type' => 'pctw_tweets',
			'meta_key' => 'tweet_id',
			'meta_value' => $tweet_id,
			'meta_compare' => '=',
		);
		$tweet_query = new WP_Query( $tweet_args );
		if ( $tweet_query->have_posts() ) { $tweet_exists = true; } else { $tweet_exists = false; }

		wp_reset_postdata();
		return $tweet_exists;

	}
?>