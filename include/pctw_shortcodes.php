<?php
/***************************************************************************************
 * DOCCARE: pctw_all_tweets_fn
 * @param 	amount 	code
 * @param 	title 	code
 */
	function pctw_all_tweets_fn($atts){
		extract(shortcode_atts(array(
			'amount' => '-1',
			'title' => 'Updates',
		), $atts));

		$return_html = '';

		// Retrieve clients order by name
		$query_args = array( 'post_type' => 'pctw_tweets', 'posts_per_page' => $amount, 'orderby' => 'date', 'order' => 'desc', 'post_status' => 'publish' );

		$tweetsquery = new WP_Query( $query_args );

		if( $tweetsquery->have_posts() ):

	    	if (strlen(trim($title))>0) {
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
					$return_html .= '<h3 class="pctw_year">' . $post_year . '</h3>';
					$return_html .= '<div class="pctw_tweetslist">';
					$current_year = $post_year;
				}


				// get custom post information
				$post_date = get_the_date('d-m-Y H:i');
				$post_title = get_the_title();
				$post_category = get_the_terms(get_the_id(), 'trip');

				//echo '<pre>'; var_dump($post_category); echo '</pre>';

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
					if (strlen(trim($current_category_name))>0) {
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
				$return_html .= '<p><strong>' . $post_date . '</strong><br>' . $post_title . '</p>';
				if (strlen($post_location)>0) {
					$return_html .= '<p style="margin-top: 0;"><strong><i class="fa fa-map-marker" aria-hidden="true"></i> <a href="https://www.google.nl/maps/?q=' . $post_location . '&zoom=7" target="_blank">' . $post_location . '</a></strong></p>';
				}
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

		extract(shortcode_atts(array(
			'amount' => '-1',
			'title' => 'Updates',
		), $atts));

		$triplist = get_terms(array(
    		'taxonomy' => 'trip',
    		'hide_empty' => false,
    		'order' => 'ASC',
    		'orderby' => 'term_id'
		));

		$return_html = '';

		if (!empty($triplist)) {
			$return_html .= '<ul class="pctw_trip_list">';
			foreach($triplist as $thistrip) {
				$return_html .= '<li class="pctw_trip_item"><a href="/trip/' . $thistrip->slug . '">' . $thistrip->name . '</a></li>';
			}
			$return_html .= '</ul>';
		}

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