<?php
/**
 * @package Twitter-to-Wordpress
 *
 * pctw_posttypes.php
 * Register custom posttypes for this plugin
 */

	function register_pctw_posttypes() { 
		// creating (registering) the custom type 
		register_post_type( 'pctw_tweets',
			array( 'labels' => array(
					'name' => __( 'Tweets', 'webvise' ), /* This is the Title of the Group */
					'singular_name' => __( 'Tweet', 'webvise' ), /* This is the individual type */
					'all_items' => __( 'All Tweets', 'webvise' ), /* the all items menu item */
					'add_new' => __( 'Add New Tweet', 'webvise' ), /* The add new menu item */
					'add_new_item' => __( 'Add New Tweet', 'webvise' ), /* Add New Display Title */
					'edit' => __( 'Edit', 'webvise' ), /* Edit Dialog */
					'edit_item' => __( 'Edit Tweet', 'webvise' ), /* Edit Display Title */
					'new_item' => __( 'New Tweet', 'webvise' ), /* New Display Title */
					'view_item' => __( 'View Tweets', 'webvise' ), /* View Display Title */
					'search_items' => __( 'Search Tweets', 'webvise' ), /* Search Custom Type Title */ 
					'not_found' =>  __( 'No Tweets found.', 'webvise' ), /* This displays if there are no entries yet */ 
					'not_found_in_trash' => __( 'No Tweets found in Trash', 'webvise' ), /* This displays if there is nothing in the trash */
					'parent_item_colon' => ''
				), /* end of arrays */
				'description' => __( 'Imported Tweets are stored here', 'webvise' ), /* Custom Type Description */
				'public' => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'show_ui' => true,
				'query_var' => true,
				'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */ 
				'menu_icon' => 'dashicons-twitter', /* the icon for the custom post type menu */
				'rewrite'	=> array( 'slug' => 'tweet', 'with_front' => false ), /* you can specify its url slug */
				'has_archive' => 'tweets', /* you can rename the slug here */
				'capability_type' => 'post',
				'hierarchical' => false,
				//'taxonomies' => array('category','post_tag'),
				/* the next one is important, it tells what's enabled in the post editor */
				'supports' => array( 'title', 'author', 'thumbnail', 'comments', 'sticky')
			) /* end of options */
		); /* end of register post type */

		// ADD TAXONOMY
		$labels = array(
			'name'                       => _x( 'Trips', 'Taxonomy General Name', 'webvise' ),
			'singular_name'              => _x( 'Trip', 'Taxonomy Singular Name', 'webvise' ),
			'menu_name'                  => __( 'Trips', 'webvise' ),
			'all_items'                  => __( 'All trips', 'webvise' ),
			'parent_item'                => __( 'Parent Item', 'webvise' ),
			'parent_item_colon'          => __( 'Parent Item:', 'webvise' ),
			'new_item_name'              => __( 'New trip name', 'webvise' ),
			'add_new_item'               => __( 'Add new trip', 'webvise' ),
			'edit_item'                  => __( 'Edit trip', 'webvise' ),
			'update_item'                => __( 'Update trip', 'webvise' ),
			'view_item'                  => __( 'View trip', 'webvise' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'webvise' ),
			'add_or_remove_items'        => __( 'Add or remove trips', 'webvise' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'webvise' ),
			'popular_items'              => __( 'Popular trips', 'webvise' ),
			'search_items'               => __( 'Search trips', 'webvise' ),
			'not_found'                  => __( 'Not Found', 'webvise' ),
			'no_terms'                   => __( 'No items', 'webvise' ),
			'items_list'                 => __( 'Trips list', 'webvise' ),
			'items_list_navigation'      => __( 'Trips list navigation', 'webvise' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'trip', array( 'pctw_tweets' ), $args );		

	}

