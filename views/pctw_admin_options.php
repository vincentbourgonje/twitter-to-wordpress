<div class="wrap">

	<h2><?php esc_html_e( 'Twitter to Wordpress Settings' , 'plcr');?></h2>

	<form name="pctw_conf" id="pctw-conf" action="" method="POST">
		<div style="text-align: right"><input type="button" name="btn_import_all" class="button-secondary" value="<?php esc_attr_e('Import All Tweets','plcr') ?>" /></div>

		<h3><?php _e('General Settings','pctw'); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="twitteraccount"><?php _e('Twitter account', 'pctw'); ?></label></th>
				<td><input id="twitteraccount" name="twitteraccount" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_twitteraccount') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="addtags"><?php _e('Add tag(s)', 'pctw'); ?></label></th>
				<td><input id="addtags" name="addtags" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_addtags') ); ?>" class="regular-text code"></td>
			</tr>
		</table>
		<h3><?php _e('Home Settings','pctw'); ?></h3>
		<p><?php _e('Anti burglar settings - do not show gps information with my Tweets when they are closer than x km from my home','pctw'); ?></p>
		<?php 
			// if there are values saved then show them
			$home_coords = get_option('pctw_setting_home_coords',false);
			if ($home_coords !== false) {
				_e('Current coordinates:','pctw');
				echo ' <a href="https://www.google.nl/maps/@' . $home_coords . ',17z" target="_blank">' . $home_coords . '</a>';
			}
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="homeaddress"><?php _e('Your home address', 'pctw'); ?></label></th>
				<td><input id="homeaddress" name="homeaddress" type="text" size="120" value="<?php echo esc_attr( get_option('pctw_setting_homeaddress') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="homeradius"><?php _e('Radius', 'pctw'); ?></label></th>
				<td><input id="homeradius" name="homeradius" type="text" size="3" value="<?php echo esc_attr( get_option('pctw_setting_homeradius') ); ?>" class="small-text code"> km</td>
			</tr>
		</table>		
		<h3><?php _e('Twitter App Settings','pctw'); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="consumerkey"><?php _e('Consumer Key', 'pctw'); ?></label></th>
				<td><input id="consumerkey" name="consumerkey" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_consumer_key') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="consumersecret"><?php _e('Consumer Secret', 'pctw'); ?></label></th>
				<td><input id="consumerkey" name="consumersecret" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_consumer_secret') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="accesstoken"><?php _e('Access token', 'pctw'); ?></label></th>
				<td><input id="accesstoken" name="accesstoken" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_access_token') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="accesstokensecret"><?php _e('Access token secret', 'pctw'); ?></label></th>
				<td><input id="accesstokensecret" name="accesstokensecret" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_access_tokensecret') ); ?>" class="regular-text code"></td>
			</tr>				
		</table>
		<p class="submit">
			<input type="hidden" name="issubmitted" value="1">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes','plcr') ?>" />
		</p>

	</form>

</div>