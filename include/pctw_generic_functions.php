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
			$logtext = date_i18n("d-m-Y H:i:s") . ' - ' . $logtext . "\n"; // \n with double quotes is new line single quotes does not work!
			
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
	function pctw_load_all_tweets($method = 'since') {


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
		$pctw_current_trip_id   = get_option("pctw_current_trip_id", 0);

		// retrieve default tags
		$pctw_default_tags		= get_option("pctw_setting_addtags" ,0);

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

		if ($method == 'since') {
			$sinceid = get_option('pctw_since_tweet_id', false);
			if ($sinceid == false) {
				// if the latest since id was not saved in an option get the latest 
				// tweet id from our database
				$sinceid = pctw_get_latest_tweetid();
				if ($sinceid > 0) {
					$parameters['since_id'] = $sinceid;	
				}
			} else {
				$parameters['since_id'] = $sinceid;
			}
		} else {
        	$lastid = get_option('pctw_last_tweet_id', false);
	        if ($lastid == false) {
	        	//$lastid = pctw_get_latest_tweet();	
				//$parameters['since_id'] = '0';
	        } else {
	        	$parameters['max_id'] = $lastid;
	        }			
		}     
	
		// init counter: this function returns counter to show how many tweets 
		// have been processed
	    $count = 0;
        $pctw_feed = $connection->get('statuses/user_timeline', $parameters);

		if ($method == 'since') {
			// reverse the array to be sure the oldest will be read first
			$pctw_feed = array_reverse($pctw_feed);
		}

		$b_newtrip = false;

		$goon = true;
        if ($goon) { // check here if object holds any data

	        foreach ($pctw_feed as $thistweet) {

	        	// tweet has been found so add 1 to the counter
				$count++;

				// debug: log tweet object
				pctw_log(print_r($thistweet,true));

				// get the basic information for this tweet
				$tweet_id 			= $thistweet->id_str;
				$tweet_createdate 	= $thistweet->created_at;
				$tweet_text			= $thistweet->text;

				// check if the tweet text contains any commands
				$process_tweet 		= true;

				// check if the command newtrip has been found in this tweet
				if (substr($tweet_text,0,9) === 'NEWTRIP: ') {
					// create a new taxonomy trip, set it as current and delete the tweet;
					$a_termids = wp_insert_term(str_replace('NEWTRIP: ','',$tweet_text),'trip');
					update_option('pctw_current_trip_id', $a_termids['term_id']);

					// reload new trip id for this batch
					$pctw_current_trip_id = get_option("pctw_current_trip_id", 0);
					pctw_log('Write and activate new trip: ' . str_replace('NEWTRIP: ','',$tweet_text));
					pctw_delete_tweet($tweet_id);
					$process_tweet = false;
					$b_newtrip = true;
				}


				// check if there is a post already with this tweet id
				// if so skip and continue to the next tweet
				if (!pctw_tweet_exists($tweet_id) && $process_tweet) {

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

					// write the new tweet tags
					wp_set_post_tags( $new_postid, $pctw_default_tags, true );

					// add the tweet to the current trip-id
					if ($pctw_current_trip_id !== false) {
						wp_set_post_terms( $new_postid, $pctw_current_trip_id, 'trip', true );
					}

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
				} else {
					if (!$b_newtrip) {
	        			pctw_log('DOUBLE: #' . $tweet_id . ' - ' . $tweet_text . ' WILL NOT BE WRITTEN'); 
	        		}
	       		}

				$lastid = $thistweet->id_str;
	        } 


	    } else {
	    	pctw_log('no tweets found since last id');
	    }

        if (strlen(trim($lastid))>0) {
        	if ($method == 'since') {
            	update_option('pctw_since_tweet_id', $lastid);
            } else {
            	update_option('pctw_last_tweet_id', $lastid);
            }
        }

        return $count;

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

	/**
	 * Import new tweets
	 * pctw_import_new_tweets()
	 * launched by scheduler
	 */
	function pctw_import_new_tweets() {
		// load all new tweets since the last
		pctw_log('Start importing new tweets if available');
		$nbr_tweets = pctw_load_all_tweets('since');
		pctw_log($nbr_tweets . ' tweet(s) have been processed');
	}

	/**
	 * Correct GEO coordinates in database
	 */
	function pctw_correct_coordinates() {

		// Retrieve all tweets
		$query_args = array( 'post_type' => 'pctw_tweets', 'posts_per_page' => '-1', 'orderby' => 'date', 'order' => 'desc', 'post_status' => 'publish' );

		$tweetsquery = new WP_Query( $query_args );

		if( $tweetsquery->have_posts() ):

	        // start loop
			while ( $tweetsquery->have_posts() ):

				$tweetsquery->the_post();

				// retrieve meta
				$s_tweet_place_geo = get_post_meta(get_the_id(),'tweet_place_geo',true);
				$a_tweet_place_geo = explode(',',$s_tweet_place_geo);

				if ($a_tweet_place_geo[0]<$a_tweet_place_geo[1]) {
					$place_lat = $a_tweet_place_geo[1];
					$place_lng = $a_tweet_place_geo[0];
					update_post_meta(get_the_id(),'tweet_place_geo', $place_lat . ',' . $place_lng);
					pctw_log('Update geo for post ' . get_the_title());
				}

			endwhile;
		endif;
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

        pctw_log('Tweet deleted:');
        pctw_log(print_r($pctw_result,true));

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

/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at http://www.geodatasource.com                          :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: http://www.geodatasource.com                        :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2015		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
	function calc_distance($lat1, $lon1, $lat2, $lon2, $unit) {

	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "K") {
	    return ($miles * 1.609344);
	  } else if ($unit == "N") {
	      return ($miles * 0.8684);
	    } else {
	        return $miles;
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

	/**
	 * Retrieve the latest tweet id in the database
	 */
	function pctw_get_latest_tweetid() {

		global $wpdb;

		$gettweet_query = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='tweet_id' ORDER BY 'tweet_id' DESC LIMIT 1";

		$latest_tweet_id = $wpdb->get_var( $wpdb->prepare( $gettweet_query ) );
		$wpdb->flush();

		if (is_null($latest_tweet_id)) { $latest_tweet_id = 0; }

		return $latest_tweet_id;
	}

	/**
	 * Find start and enddates for all trips
	 */
	function pctw_set_trip_dates() {
		$all_trips = get_terms( 'trip', array('hide_empty' => true,) );

		//echo '<pre>'; var_dump($all_trips); echo '</pre>';
		foreach($all_trips as $current_trip) {
			$from_date = pctw_get_tweet_in_term_date($current_trip->term_id,'oldest');
			update_term_meta($current_trip->term_id, 'pctw_term_meta_date_from', $from_date);
			$until_date = pctw_get_tweet_in_term_date($current_trip->term_id,'newest');
			update_term_meta($current_trip->term_id, 'pctw_term_meta_date_until', $until_date);
		}
	}

	/**
	 * Get the oldest / newest date from tweets belonging to a term
	 */
	function pctw_get_tweet_in_term_date($termid, $tweet_age = 'oldest') {
		global $wpdb;

		if($tweet_age == 'oldest') {
			$sortorder = 'ASC';
		} else {
			$sortorder = 'DESC';
		}

		$getdate_query = "SELECT aev_posts.post_date
			FROM aev_posts INNER JOIN aev_term_relationships ON aev_posts.ID = aev_term_relationships.object_id
	 		INNER JOIN aev_terms ON aev_term_relationships.term_taxonomy_id = aev_terms.term_id
	 		INNER JOIN aev_term_taxonomy ON aev_term_relationships.term_taxonomy_id = aev_term_taxonomy.term_taxonomy_id
			WHERE (aev_term_taxonomy.taxonomy = 'trip' AND aev_term_taxonomy.term_id = " . $termid . ") ORDER BY aev_posts.post_date " . $sortorder . " LIMIT 1";

		$founddate = $wpdb->get_var( $wpdb->prepare( $getdate_query ) );
		$wpdb->flush();

		return $founddate;
	}

	/**
	 * Add some custom cron schedule time intervals
	 */
	function pctw_add_schedule_intervals( $schedules ) {
	 
	    $schedules['every_five_minutes'] = array(
	            'interval'  => 300,
	            'display'   => __( 'Every 5 Minutes', 'pctw' )
	    );
	    $schedules['every_fifteen_minutes'] = array(
	            'interval'  => 900,
	            'display'   => __( 'Every 15 Minutes', 'pctw' )
	    );	    
	     
	    return $schedules;
	}	
?>