<div class="wrap">

	<h2><?php esc_html_e( 'Twitter to Wordpress Settings' , 'plcr');?></h2>

	<form name="pctw_conf" id="pctw-conf" action="" method="POST">
		<div style="text-align: right"><input type="button" name="btn_import_all" class="button-secondary" value="<?php esc_attr_e('Import All Tweets','plcr') ?>" /></div>

		<h3>General Settings</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="twitteraccount"><?php _e('Twitter account', 'pclr'); ?></label></th>
				<td><input id="twitteraccount" name="twitteraccount" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_twitteraccount') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="addtags"><?php _e('Add tag(s)', 'pclr'); ?></label></th>
				<td><input id="addtags" name="addtags" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_addtags') ); ?>" class="regular-text code"></td>
			</tr>
		</table>		
		<h3>Twitter App settings</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="consumerkey"><?php _e('Consumer Key', 'pclr'); ?></label></th>
				<td><input id="consumerkey" name="consumerkey" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_consumer_key') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="consumersecret"><?php _e('Consumer Secret', 'pclr'); ?></label></th>
				<td><input id="consumerkey" name="consumersecret" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_consumer_secret') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="accesstoken"><?php _e('Access token', 'pclr'); ?></label></th>
				<td><input id="accesstoken" name="accesstoken" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_access_token') ); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="accesstokensecret"><?php _e('Access token secret', 'pclr'); ?></label></th>
				<td><input id="accesstokensecret" name="accesstokensecret" type="text" size="60" value="<?php echo esc_attr( get_option('pctw_setting_access_tokensecret') ); ?>" class="regular-text code"></td>
			</tr>				
		</table>
		<p class="submit">
			<input type="hidden" name="issubmitted" value="1">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes','plcr') ?>" />
		</p>

	</form>

</div>