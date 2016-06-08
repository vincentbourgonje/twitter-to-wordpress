<?php

	add_filter( 'rwmb_meta_boxes', 'pctw_register_meta_boxes' );

	function pctw_register_meta_boxes( $meta_boxes ) {
		$tsce_prefix = 'tweet_place_';

		// Base information
	    $meta_boxes[] = array(
	        'id'         => 'pctw_tweet_info',
	        'title'      => __('Additional Tweet information', 'plcr'),
	        'post_types' => array('pctw_tweets'),
	        'context'    => 'normal',
	        'priority'   => 'high',
	        'fields' => array(
	        	array(
	        		'name' 	=> __('Tweet #','plcr'),
	        		'id'	=> 'tweet_id',
	        		'type'	=> 'text',
	        		'attributes' => array(
					    'disabled'  => false,
					    'required'  => false,
					    'readonly'  => true,
					    'maxlength' => false,
					    'pattern'   => false,
					),
	        	),
	            array(
	                'name'  => __( 'Cityname', 'plcr' ),
	                'id'    => $tsce_prefix . 'name',
	                'type'  => 'text',
	            ),
	            array(
	                'name'  => __( 'Country', 'plcr' ),
	                'id'    => $tsce_prefix . 'country',
	                'type'  => 'text',
	            ),
	            array(
	                'name'  => __( 'Countrycode', 'plcr' ),
	                'id'    => $tsce_prefix . 'countrycode',
	                'type'  => 'text',
	            ),
	            array(
	                'name'  => __( 'Geo lat/lon', 'plcr' ),
	                'id'    => $tsce_prefix . 'geo',
	                'type'  => 'text',
	            ),
	            array(
	                'name'  => __( 'Original title', 'plcr' ),
	                'id'    => $tsce_prefix . 'org_title',
	                'type'  => 'text',
	            ),	            
	            // array(
	            //     'name'  => __( 'Geo poly', 'plcr' ),
	            //     'id'    => $tsce_prefix . 'geo_poly',
	            //     'type'  => 'text',
	            // ),
	        ),
	    );

	    return $meta_boxes;		
	}

	// A callback function to add a custom field to our "trip" taxonomy
	function trip_taxonomy_custom_fields($tag) {

		$prefix = 'pctw_term_meta_';

		// Check for existing taxonomy meta for the term you're editing
		$t_id = $tag->term_id; // Get the ID of the term you're editing

		$field_id = $prefix . 'sortorder';
		$field_value = get_term_meta( $t_id, $field_id, true);
		echo pctw_add_meta_field('text', 'Sorteervolgorde', $field_id, $field_value, 'Voer een getal in om op te sorteren');

		$field_id = $prefix . 'date_from';
		$field_value = get_term_meta( $t_id, $field_id, true);
		echo pctw_add_meta_field('date', 'Datum vanaf', $field_id, $field_value, 'Datum dat de trip begon');

		$field_id = $prefix . 'date_until';
		$field_value = get_term_meta( $t_id, $field_id, true);
		echo pctw_add_meta_field('date', 'Datum t/m', $field_id, $field_value, 'Datum dat de trip was afgelopen');				

	}

	// A callback function to save our "trip" taxonomy data
	function save_taxonomy_custom_fields( $term_id ) {  
	    
	    if ( isset( $_POST['pctw_term_meta_sortorder'] ) ) {  
	    	add_term_meta($term_id, 'pctw_term_meta_sortorder', trim($_POST['pctw_term_meta_sortorder']), false);
	    }
	    if ( isset( $_POST['pctw_term_meta_date_from'] ) ) {  
	    	add_term_meta($term_id, 'pctw_term_meta_date_from', trim($_POST['pctw_term_meta_date_from']), false);
	    }
	    if ( isset( $_POST['pctw_term_meta_date_until'] ) ) {  
	    	add_term_meta($term_id, 'pctw_term_meta_date_until', trim($_POST['pctw_term_meta_date_until']), false);
	    }

	}  	

	// Helper function to write the actual HTML
	function pctw_add_meta_field($fieldtype, $fieldlabel, $fieldid, $fieldvalue, $fielddesc) {

		$return_html = '';	

		$return_html .= '<tr class="form-field">';
		$return_html .= '<th scope="row" valign="top">';
		$return_html .= '<label for="presenter_id">' . $fieldlabel . '</label>';
		$return_html .= '</th>';
		$return_html .= '<td>';
		$return_html .= '<input type="' . $fieldtype . '" name="' . $fieldid . '" id="' . $fieldid . '" size="25" style="width:60%;" value="' . $fieldvalue . '">';
		if (strlen(trim($fielddesc))>0) {
			$return_html .= '<br><span class="description">' . $fielddesc . '</span>';
		}
		$return_html .= '</td>';
		$return_html .= '</tr>';

		return $return_html;

	}


?>
