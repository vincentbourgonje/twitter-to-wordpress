<?php
/***************************************************************************************
 * DOCCARE: pctw_all_tweets_fn
 * @param 	amount 	code
 * @param 	title 	code
 */
	function pctw_all_tweets_fn($atts){
		extract(shortcode_atts(array(
			'amount' => '-1',
			'showtitles' => '1',
			'title' => 'Updates',
		), $atts));

		$return_html = '';

		// Retrieve clients order by name
		$query_args = array( 'post_type' => 'pctw_tweets', 'posts_per_page' => $amount, 'orderby' => 'date', 'order' => 'desc', 'post_status' => 'publish' );

		$tweetsquery = new WP_Query( $query_args );

		if( $tweetsquery->have_posts() ):

	    	if (strlen(trim($title))>0 && $showtitles !== '0') {
	    		$return_html .= '<h2>' . $title . '</h2>';
	    	}

	        $teller = 1;
	        $current_year = 0;
	        $current_category_name = '';

	        // start loop
			while ( $tweetsquery->have_posts() ) :  $tweetsquery->the_post();

				$post_year = get_the_date("Y");

				if ($current_year !== $post_year) {
					if ($teller > 1) {
						$return_html .= '</div>';
					}
					if ($showtitles !== '0') {
						$return_html .= '<h3 class="pctw_year">' . $post_year . '</h3>';
					}
					$return_html .= '<div class="pctw_tweetslist">';
					$current_year = $post_year;
				}


				// get custom post information
				$post_date = get_the_date('d-m-Y H:i');
				$post_title = get_the_title();
				$post_category = get_the_terms(get_the_id(), 'trip');

				$home_coords = get_option('pctw_setting_home_coords',false);
				$home_radius = get_option('pctw_setting_homeradius',false);
				if ($home_coords !== false) {
					$a_home_coords = explode(',', $home_coords);
				}				

				$post_location = trim(get_post_meta(get_the_id(),'tweet_place_name', true)) . ', ' . trim(get_post_meta(get_the_id(),'tweet_place_country', true));
				$post_geo = get_post_meta(get_the_id(),'tweet_place_geo',true);
				if (strlen(trim($post_geo))>1) {
					$a_post_geo = explode(',',$post_geo);
					$distance_from_home = round(calc_distance($a_home_coords[0],$a_home_coords[1],$a_post_geo[0],$a_post_geo[1],'K'),0);
				} else {
					$distance_from_home = 'n/a';
				}
				
				if(trim($post_location) == ',') { $post_location = ''; }

				if ($post_category!==false) {
					$post_category_name = $post_category[0]->name;
				} else {
					$post_category_name = '';
				}

				if ($current_category_name !== $post_category_name) {
					if ($teller > 1) {
						$return_html .= '</div>';
					}					
					$current_category_name = $post_category_name;
					if (strlen(trim($current_category_name))>0 && $showtitles !== '0') {
						$return_html .= '<div class="pctw_category_name">' . $post_category_name . '</div>';
					}
					$return_html .= '<div class="pctw_tweetslist">';
				}

				//echo '<pre>'; var_dump($post_category); echo '</pre>';

				$post_location = trim(get_post_meta(get_the_id(),'tweet_place_name', true)) . ', ' . trim(get_post_meta(get_the_id(),'tweet_place_country', true));
				if(trim($post_location) == ',') { $post_location = ''; }

				// find and remove urls from the title
				$thumb_url_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large', true);
				$thumb_url_large = $thumb_url_array[0];
				$thumb_url_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full', true);
				$thumb_url_full  = $thumb_url_array[0];

				$return_html .= '<div class="pctw_tweet tweet_' . get_the_id() . '">';
				if (strlen(trim($thumb_url_large))>0 && strpos($thumb_url_large,'default.png',0)===false) {
					$return_html .= '<a href="' . $thumb_url_full . '" rel="avlightbox" title="' . $post_date . ': ' . $post_title . '">';
					$return_html .= '<img src="' . $thumb_url_large . '" alt="' . $post_title . '" class="tweetimage">';
					$return_html .= '</a>';
				}


				$return_html .= '<p><strong>' . $post_date . '</strong><br><a href="' .  get_the_permalink() . '" class="tweet_title">' . $post_title . '</a> <a href="' .  get_the_permalink() . '" class="morebtn"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a></p>';


				if (strlen($post_location)>0 && intval($distance_from_home) > intval($home_radius)) {

					$return_html .= '<p style="margin-top: 0;"><strong><span class="pctw_location_info"><i class="fa fa-map-marker" aria-hidden="true"></i> <a href="https://www.google.nl/maps/?q=' . $post_geo . '&zoom=7" target="_blank">' . $post_location . '</a></span></strong>';

					if($distance_from_home !== 'n/a') { 
						$return_html .= '<br><span class="pctw_distance_to_home">' . $distance_from_home . 'KM <i class="fa fa-arrow-right" aria-hidden="true"></i> <i class="fa fa-home" aria-hidden="true"></i></span>';
					}

					$return_html .= '</p>';
				} // endif 

				$return_html .= '</div>';

				$teller++;
			
			endwhile;

			$return_html .= '</div>';

		else:
			$return_html .= __('No Tweets found','webvise');
		endif;

		wp_reset_postdata();

		return $return_html;

	}

/***************************************************************************************
 * DOCCARE: pctw_all_tweet_categories_fn
 * @param 	amount 	code
 * @param 	title 	code
 */
	function pctw_all_tweet_categories_fn($atts){

		global $wpdb;

		extract(shortcode_atts(array(
			'amount' => '-1',
			'title' => 'Updates',
		), $atts));

		// get the current category
		$current_trip = get_option("pctw_current_trip_id", 0);

		if ($amount > 0) { $maxitems = " LIMIT " . $amount; } else { $maxitems = ''; }
		// Inner join terms with terms meta so we can sort on date
		$triplist_query = "SELECT $wpdb->terms.term_id, 
			$wpdb->terms.name, 
			$wpdb->terms.slug, 
			$wpdb->termmeta.meta_value, 
			$wpdb->termmeta.meta_key
			FROM $wpdb->terms INNER JOIN $wpdb->termmeta ON $wpdb->terms.term_id = $wpdb->termmeta.term_id
			WHERE $wpdb->termmeta.meta_key='pctw_term_meta_date_from' ORDER BY $wpdb->termmeta.meta_value DESC" . $maxitems;

		$mytriplist = $wpdb->get_results( $triplist_query );

		$return_html = '';

		if (!empty($mytriplist)) {
			$return_html .= '<ul class="pctw_trip_list">';
			foreach($mytriplist as $thistrip) {
				$from_date = date("d-m-Y",strtotime($thistrip->meta_value));
				$until_date = date("d-m-Y",strtotime(get_term_meta( $thistrip->term_id, 'pctw_term_meta_date_until', true )));
				if ($thistrip->term_id == $current_trip) { $iscurrent = ' <span class="current_trip"><i class="fa fa-certificate" aria-hidden="true"></i></span>'; } else { $iscurrent = ''; }			
				$return_html .= '<li class="pctw_trip_item"><a href="/trip/' . $thistrip->slug . '">' . $thistrip->name . '</a>' . $iscurrent . '<br>
<div class="pctw_trip_meta"><i class="fa fa-calendar-o" aria-hidden="true"></i> ' . $from_date . ' - ' . $until_date . '</div></li>';
			}
			$return_html .= '</ul>';
		}

		$wpdb->flush;

		return $return_html;
	}


/***************************************************************************************
 * INIT SHORTCODES
 */ 
	function pctw_register_shortcodes(){
		add_shortcode('pctw_all_tweets', 'pctw_all_tweets_fn');
		add_shortcode('pctw_all_tweet_categories', 'pctw_all_tweet_categories_fn');
	}

	add_action( 'init', 'pctw_register_shortcodes');

?>