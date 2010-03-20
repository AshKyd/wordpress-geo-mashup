<?php
/**
 * Geo Mashup Options Page HTML Management
 *
 * @package GeoMashup
 */

/**
 * Print Geo Mashup Options HTML
 * 
 * @since 1.2
 * @access public
 */
function geo_mashup_options_page() {
	global $geo_mashup_options;

	if (isset($_POST['submit'])) {
		// Process option updates
		check_admin_referer('geo-mashup-update-options');
		// Missing add_map_type_control means empty array
		if ( empty( $_POST['global_map']['add_map_type_control'] ) ) {
			$_POST['global_map']['add_map_type_control']  = array();
		}
		if ( empty( $_POST['single_map']['add_map_type_control'] ) ) {
			$_POST['single_map']['add_map_type_control']  = array();
		}
		if ( empty( $_POST['context_map']['add_map_type_control'] ) ) {
			$_POST['context_map']['add_map_type_control']  = array();
		}
		$geo_mashup_options->set_valid_options ( $_POST );
		if ($geo_mashup_options->save()) {
			echo '<div class="updated fade"><p>'.__('Options updated.', 'GeoMashup').'</p></div>';
		}
	}

	if ( isset( $_POST['bulk_reverse_geocode'] ) ) {
		check_admin_referer('geo-mashup-update-options');
		$log = GeoMashupDB::bulk_reverse_geocode( ); 
		echo '<div class="updated fade">' . $log . '</div>';
	}

	if ( isset( $_POST['upgrade_db'] ) ) {
		check_admin_referer('geo-mashup-upgrade-db');
		if ( GeoMashupDB::install( ) ) {
			echo '<div class="updated fade"><p>'.__('Database upgraded.', 'GeoMashup').'</p></div>';
		}
	}

	if ( !empty ( $geo_mashup_options->corrupt_options ) ) {
		// Options didn't load correctly
		$message .= ' ' . __('Saved options may be corrupted, try updating again. Corrupt values: ') . 
			'<code>' . $geo_mashup_options->corrupt_options . '</code>';
		echo '<div class="updated"><p>'.$message.'</p></div>';
	}

	if ( !empty ( $geo_mashup_options->validation_errors ) ) {
		// There were invalid options
		echo '<div class="updated"><p>' .
			__('Some invalid options will not be used. If you\'ve just upgraded, do an update to initialize new options.', 'GeoMashup');
		echo '<ul>';
		foreach ( $geo_mashup_options->validation_errors as $message ) {
			echo "<li>$message</li>";
		}
		echo '</ul></p></div>';
	}

	// Create marker and color arrays
	$colorNames = Array(
		'aqua' => '#00ffff',
		'black' => '#000000',
		'blue' => '#0000ff',
		'fuchsia' => '#ff00ff',
		'gray' => '#808080',
		'green' => '#008000',
		'lime' => '#00ff00',
		'maroon' => '#800000',
		'navy' => '#000080',
		'olive' => '#808000',
		'orange' => '#ffa500',
		'purple' => '#800080',
		'red' => '#ff0000',
		'silver' => '#c0c0c0',
		'teal' => '#008080',
		'white' => '#ffffff',
		'yellow' => '#ffff00');
	
	$mapTypes = Array(
		'G_NORMAL_MAP' => __('Roadmap', 'GeoMashup'), 
		'G_SATELLITE_MAP' => __('Satellite', 'GeoMashup'),
		'G_HYBRID_MAP' => __('Hybrid', 'GeoMashup'),
		'G_PHYSICAL_MAP' => __('Terrain', 'GeoMashup'),
		'G_SATELLITE_3D_MAP' => __('Earth Plugin', 'GeoMashup'));

	$mapControls = Array(
		'GSmallZoomControl' => __('Small Zoom', 'GeoMashup'),
		'GSmallZoomControl3D' => __('Small Zoom 3D', 'GeoMashup'),
		'GSmallMapControl' => __('Small Pan/Zoom', 'GeoMashup'),
		'GLargeMapControl' => __('Large Pan/Zoom', 'GeoMashup'),
		'GLargeMapControl3D' => __('Large Pan/Zoom 3D', 'GeoMashup'));

	$futureOptions = Array(
		'true' => __('Yes', 'GeoMashup'),
		'false' => __('No', 'GeoMashup'),
		'only' => __('Only', 'GeoMashup'));

	$zoomOptions = Array( 'auto' => __( 'auto', 'GeoMashup' ) );
	for ( $i = 0; $i < GEO_MASHUP_MAX_ZOOM; $i++ ) {
		$zoomOptions[$i] = $i;
	}

	$selected_tab = ( empty( $_POST['geo_mashup_selected_tab'] ) ) ? 0 : $_POST['geo_mashup_selected_tab']; 
	$google_key = $geo_mashup_options->get( 'overall', 'google_key' );
	// Now for the HTML
?>
	<script type="text/javascript"> 
	jQuery(function( $ ) { 
		var selector = '#geo-mashup-settings-form';
		$( selector ).tabs( {
			selected: <?php echo $selected_tab ?>,
			select: function ( event, ui ) {
				$( '#geo-mashup-selected-tab' ).val( ui.index );
			}
		} ); 
 	} ); 
	</script>
	<div class="wrap">
		<h2><?php _e('Geo Mashup Options', 'GeoMashup'); ?></h2>
		<?php if ( GeoMashupDB::installed_version( ) != GEO_MASHUP_DB_VERSION ) : ?>
			<div class="updated">
				<form method="post" id="geo-mashup-upgrade-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<?php wp_nonce_field('geo-mashup-upgrade-db'); ?>
					<label><?php _e( 'The Geo Mashup database is out of date.', 'GeoMashup' ); ?></label>
					<input type="submit" name="upgrade_db" value="<?php _e('Upgrade Database', 'GeoMashup'); ?>" class="button" />
				</form>
			</div>
		<?php endif; ?>
		<form method="post" id="geo-mashup-settings-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<ul>
			<li><a href="#geo-mashup-overall-settings"><span><?php _e('Overall', 'GeoMashup'); ?></span></a></li>
			<li><a href="#geo-mashup-global-map-settings"><span><?php _e('Global Maps', 'GeoMashup'); ?></span></a></li>
			<li><a href="#geo-mashup-single-map-settings"><span><?php _e('Single Maps', 'GeoMashup'); ?></span></a></li>
			<li><a href="#geo-mashup-context-map-settings"><span><?php _e('Contextual Maps', 'GeoMashup'); ?></span></a></li>
			</ul>
			<fieldset id="geo-mashup-overall-settings">
				<?php wp_nonce_field('geo-mashup-update-options'); ?>
				<input id="geo-mashup-selected-tab" 
					name="geo_mashup_selected_tab" 
					type="hidden" 
					value="<?php echo $selected_tab; ?>" />
				<p><?php _e('Overall Geo Mashup Settings', 'GeoMashup'); ?></p>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr>
						<th width="33%" scope="row"><?php _e('Google API Key', 'GeoMashup'); ?></th>
						<td<?php if ( empty( $google_key ) ) echo ' class="error"'; ?>>
							<input id="google_key" 
								name="overall[google_key]" 
								type="text" 
								size="40" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'overall', 'google_key' ) ); ?>" />
							<a href="http://maps.google.com/apis/maps/signup.html"><?php _e('Get yours here', 'GeoMashup'); ?></a>
							<?php if ( empty( $google_key ) ) : ?>
							<p class="setting-description">
							<?php _e( 'This setting is required for Geo Mashup to work.', 'GeoMashup' ); ?>
							</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row" title="<?php _e('Generated links go here','GeoMashup'); ?>">
							<?php _e('Global Mashup Page', 'GeoMashup'); ?>
						</th>
						<td>
							<select id="mashup_page" name="overall[mashup_page]"><?php 
								$pages = get_pages(); 
								if ( $pages ) : 
									foreach ( $pages as $page ) : ?>
									<option value="<?php echo attribute_escape( $page->ID ); ?>"<?php
										if ( $geo_mashup_options->get( 'overall', 'mashup_page' ) == $page->ID ) { 
											echo ' selected="selected"';
										}
									?>><?php echo esc_html( $page->post_name ); ?></option>
								<?php endforeach; ?>
							<?php else : ?>
								<option value=""><?php _e( 'No pages available.', 'GeoMashup' ); ?></option>
							<?php endif; ?>
							</select>
							<span class="setting-description"><?php
								_e( 'Geo Mashup will use this page for generated location links', 'GeoMashup' );
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Use Theme Style Sheet with Maps', 'GeoMashup'); ?></th>
						<td>
							<input id="theme_stylesheet_with_maps" 
								name="overall[theme_stylesheet_with_maps]" 
								type="checkbox" 
								value="true"<?php 
									if ( $geo_mashup_options->get( 'overall', 'theme_stylesheet_with_maps' ) == 'true' ) { 
										echo ' checked="checked"'; 
									}
								?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Category Links', 'GeoMashup'); ?></th>
						<td>
							<input id="add_category_links" name="overall[add_category_links]" type="checkbox" value="true"<?php 
								if ( $geo_mashup_options->get( 'overall', 'add_category_links' ) == 'true' ) { 
									echo ' checked="checked"';
								}
							?> />
							<span class="setting-description"><?php 
								_e( 'Add map links to category lists. Categories must have descriptions for this to work.', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Category Link Separator', 'GeoMashup'); ?></th>
						<td>
							<input id="category_link_separator" 
								name="overall[category_link_separator]" 
								class="add-category-links-dep" 
								type="text" 
								size="3" 
								value="<?php echo attribute_escape( $geo_mashup_options->get( 'overall', 'category_link_separator' ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Category Link Text', 'GeoMashup'); ?></th>
						<td>
							<input id="category_link_text" 
								name="overall[category_link_text]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get( 'overall', 'category_link_text' ) ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Category Link Zoom Level', 'GeoMashup'); ?></th>
						<td>
							<select id="category_zoom" name="overall[category_zoom]">
								<?php foreach ( $zoomOptions as $value => $label ) : ?>
								<option value="<?php echo attribute_escape( $value ); ?>"<?php
									if ( strcmp( $value, $geo_mashup_options->get( 'overall', 'category_zoom' ) ) == 0 ) {
										echo ' selected="selected"';
									}
								?>><?php echo attribute_escape( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="setting-description"><?php 
								_e( '0 is zoomed all the way out.', 'GeoMashup' ); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Collect Location For', 'GeoMashup'); ?></th>
						<td>
							<input id="locate_posts" name="overall[located_object_name][post]" type="checkbox" value="true"<?php 
								if ( $geo_mashup_options->get( 'overall', 'located_object_name', 'post' ) == 'true' ) {
									echo ' checked="checked"';
								}
							?> /> <?php _e( 'posts and pages', 'GeoMashup' ); ?>
							<input id="locate_users" name="overall[located_object_name][user]" type="checkbox" value="true"<?php 
								if ( $geo_mashup_options->get( 'overall', 'located_object_name', 'user' ) == 'true' ) {
									echo ' checked="checked"';
								}
							?> /> <?php _e( 'users', 'GeoMashup' ); ?>
							<input id="locate_comments" name="overall[located_object_name][comment]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get( 'overall', 'located_object_name', 'comment' ) == 'true' ) {
									echo ' checked="checked"';
								}
							?> /> <?php _e( 'comments', 'GeoMashup' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Enable Reverse Geocoding', 'GeoMashup'); ?></th>
						<td>
							<input id="enable_reverse_geocoding" name="overall[enable_reverse_geocoding]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'overall', 'enable_reverse_geocoding' ) == 'true' ) {
									echo ' checked="checked"';
								}
							?> />
							<span class="setting-description"><?php 
								_e('Try to look up missing address fields for new locations.', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
					<?php if ( $geo_mashup_options->get( 'overall', 'enable_reverse_geocoding' ) == 'true' ) : ?>
					<tr>
						<th scope="row"><?php _e('Bulk Reverse Geocode', 'GeoMashup'); ?></th>
						<td>
							<input type="submit" name="bulk_reverse_geocode" value="<?php _e('Start', 'GeoMashup'); ?>" class="button" />
							<span class="setting-description"><?php 
								_e('Try to look up missing address fields for existing locations. Could be slow.', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php _e('AdSense For Search ID', 'GeoMashup'); ?></th>
						<td>
							<input id="adsense_code_text" 
								name="overall[adsense_code]" 
								type="text" 
								size="35" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'overall', 'adsense_code' ) ); ?>" /><br/>
							<span class="setting-description"><?php 
								_e('Your client ID, used with the Google Bar. Leave the default value to use Geo Mashup\'s :).', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
				</table>
				<div class="submit"><input type="submit" name="submit" value="<?php _e('Update Options', 'GeoMashup'); ?>" /></div>
			</fieldset>
			<fieldset id="geo-mashup-single-map-settings">
				<p><?php _e('Default settings for maps of a single located post.', 'GeoMashup'); ?></p>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr>
						<th scope="row"><?php _e('Map Width', 'GeoMashup'); ?></th>
						<td>
							<input id="in_post_map_width" 
								name="single_map[width]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'single_map', 'width' ) ); ?>" />
							<?php _e('Pixels, or append %.', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Height', 'GeoMashup'); ?></th>
						<td>
							<input id="in_post_map_height" 
								name="single_map[height]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'single_map', 'height' ) ); ?>" />
							<?php _e('px', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Control', 'GeoMashup'); ?></th>
						<td>
							<select id="in_post_map_control" name="single_map[map_control]">
							<?php foreach($mapControls as $type => $label) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php
									if ( $type == $geo_mashup_options->get( 'single_map', 'map_control' ) ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Map Type', 'GeoMashup'); ?></th>
						<td>
							<select id="in_post_map_type" name="single_map[map_type]">
							<?php foreach ( $mapTypes as $type => $label ) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php
									if ( $type == $geo_mashup_options->get ( 'single_map', 'map_type' ) ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Map Type Control', 'GeoMashup'); ?></th>
						<td>
						<?php foreach ( $mapTypes as $type => $label ) : ?>
						<input id="in_post_add_map_type_<?php echo attribute_escape( $type ); ?>" 
							name="single_map[add_map_type_control][]" 
							type="checkbox" 
							value="<?php echo attribute_escape( $type ); ?>" <?php 
								if ( in_array( $type, $geo_mashup_options->get ( 'single_map', 'add_map_type_control' ) ) ) {
									echo ' checked="checked"';
								}
								?> /> <?php echo esc_html( $label ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Overview Control', 'GeoMashup'); ?></th>
						<td><input id="in_post_add_overview_control" name="single_map[add_overview_control]" type="checkbox" value="true"<?php 
							if ( $geo_mashup_options->get( 'single_map', 'add_overview_control' ) == 'true' ) {
								echo ' checked="checked"';
							}
						?> /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Google Bar', 'GeoMashup'); ?></th>
						<td><input id="in_post_add_google_bar" name="single_map[add_google_bar]" type="checkbox" value="true"<?php 
							if ( $geo_mashup_options->get ( 'single_map', 'add_google_bar' ) == 'true' ) {
								echo ' checked="checked"';
							}
						?> /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Enable Scroll Wheel Zoom', 'GeoMashup'); ?></th>
						<td><input id="in_post_enable_scroll_wheel_zoom" name="single_map[enable_scroll_wheel_zoom]" type="checkbox" value="true"<?php 
							if ( $geo_mashup_options->get ( 'single_map', 'enable_scroll_wheel_zoom' ) == 'true' ) {
								echo ' checked="checked"';
							}
						?> /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Zoom Level', 'GeoMashup'); ?></th>
						<td>
							<select id="in_post_zoom" name="single_map[zoom]">
								<?php foreach ( $zoomOptions as $value => $label ) : ?>
								<option value="<?php echo attribute_escape( $value ); ?>"<?php
									if ( strcmp( $value, $geo_mashup_options->get( 'single_map', 'zoom' ) ) == 0 ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="setting-description"><?php 
								_e( '0 is zoomed all the way out.', 'GeoMashup' ); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load', 'GeoMashup'); ?></th>
						<td>
							<input id="in_post_click_to_load" name="single_map[click_to_load]" type="checkbox" value="true"<?php 
								if ( $geo_mashup_options->get( 'single_map', 'click_to_load' ) == 'true' ) {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load Text', 'GeoMashup'); ?></th>
						<td>
							<input id="in_post_click_to_load_text" 
								name="single_map[click_to_load_text]" 
								type="text" 
								size="50" 
								value="<?php echo attribute_escape( $geo_mashup_options->get( 'single_map', 'click_to_load_text' ) ); ?>" />
						</td>
					</tr>
				</table>
				<div class="submit"><input type="submit" name="submit" value="<?php _e('Update Options', 'GeoMashup'); ?>" /></div>
			</fieldset>
			<fieldset id="geo-mashup-global-map-settings">
				<p><?php _e('Default settings for global maps of located items.', 'GeoMashup'); ?></p>
				<div class="submit"><input type="submit" name="submit" value="<?php _e('Update Options', 'GeoMashup'); ?>" /></div>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr>
						<th scope="row"><?php _e('Map Width', 'GeoMashup'); ?></th>
						<td>
							<input id="map_width" 
								name="global_map[width]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'global_map', 'width' ) ); ?>" />
							<?php _e('Pixels, or append %.', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Height', 'GeoMashup'); ?></th>
						<td>
							<input id="map_height" 
								name="global_map[height]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'global_map', 'height' ) ); ?>" />
							<?php _e('px', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Control', 'GeoMashup'); ?></th>
						<td>
							<select id="map_control" name="global_map[map_control]">
							<?php	foreach($mapControls as $type => $label) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php 
									if ( $type == $geo_mashup_options->get( 'global_map', 'map_control' ) ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php	endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Map Type', 'GeoMashup'); ?></th>
						<td>
							<select id="map_type" name="global_map[map_type]">
							<?php foreach($mapTypes as $type => $label) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php 
									if ($type == $geo_mashup_options->get ( 'global_map', 'map_type' )) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Map Type Control', 'GeoMashup'); ?></th>
						<td>
						<?php foreach ( $mapTypes as $type => $label ) : ?>
						<input id="add_map_type_<?php echo attribute_escape( $type ); ?>" 
							name="global_map[add_map_type_control][]" 
							type="checkbox" 
							value="<?php echo attribute_escape( $type ); ?>" <?php 
								if ( in_array( $type, $geo_mashup_options->get ( 'global_map', 'add_map_type_control' ) ) ) {
									echo ' checked="checked"';
								}
								?> /> <?php echo esc_html( $label ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Overview Control', 'GeoMashup'); ?></th>
						<td>
							<input id="add_overview_control" name="global_map[add_overview_control]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'global_map', 'add_overview_control' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Google Bar', 'GeoMashup'); ?></th>
						<td>
							<input id="add_google_bar" name="global_map[add_google_bar]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'global_map', 'add_google_bar' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Enable Scroll Wheel Zoom', 'GeoMashup'); ?></th>
						<td><input id="enable_scroll_wheel_zoom" name="global_map[enable_scroll_wheel_zoom]" type="checkbox" value="true"<?php 
							if ( $geo_mashup_options->get ( 'global_map', 'enable_scroll_wheel_zoom' ) == 'true' ) {
								echo ' checked="checked"';
							}
						?> /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Zoom Level', 'GeoMashup'); ?></th>
						<td>
							<select id="zoom" name="global_map[zoom]">
								<?php foreach ( $zoomOptions as $value => $label ) : ?>
								<option value="<?php echo attribute_escape( $value ); ?>"<?php
									if ( strcmp( $value, $geo_mashup_options->get( 'global_map', 'zoom' ) ) == 0 ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="setting-description"><?php 
								_e( '0 is zoomed all the way out.', 'GeoMashup' ); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Cluster Markers Until Zoom Level', 'GeoMashup'); ?></th>
						<td>
							<input id="cluster_max_zoom" 
								name="global_map[cluster_max_zoom]" 
								type="text" 
								size="2" 
								value="<?php echo attribute_escape( $geo_mashup_options->get( 'global_map', 'cluster_max_zoom' ) ); ?>" />
							<span class="setting-description"><?php 
								_e( 'Highest zoom level to cluster markers, or blank for no clustering.', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Marker Selection Behaviors', 'GeoMashup'); ?></th>
						<td>
							<input id="global_marker_select_info_window" name="global_map[marker_select_info_window]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'global_map', 'marker_select_info_window' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Open info window', 'GeoMashup' ); ?>
							<input id="global_marker_select_highlight" name="global_map[marker_select_highlight]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'global_map', 'marker_select_highlight' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Highlight', 'GeoMashup' ); ?>
							<input id="global_marker_select_center" name="global_map[marker_select_center]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'global_map', 'marker_select_center' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Center', 'GeoMashup' ); ?>
							<input id="global_marker_select_attachments" name="global_map[marker_select_attachments]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'global_map', 'marker_select_attachments' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Show Geo Attachments', 'GeoMashup' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Automatic Selection', 'GeoMashup'); ?></th>
						<td>
							<input id="auto_info_open" name="global_map[auto_info_open]" type="checkbox" value="true"<?php
								if ($geo_mashup_options->get ( 'global_map', 'auto_info_open' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
							<span class="setting-description"><?php 
								_e( 'Selects the linked or most recent item when the map loads.', 'GeoMashup'); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Show Only Most Recent Items', 'GeoMashup'); ?></th>
						<td>
							<input id="max_posts" 
								name="global_map[max_posts]" 
								type="text" 
								size="4" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'global_map', 'max_posts' ) ); ?>" />
							<span class="setting-description"><?php _e('Number of items to show, leave blank for all', 'GeoMashup'); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Show Future Posts', 'GeoMashup'); ?></th>
						<td><select id="show_future" name="global_map[show_future]">
							<?php foreach($futureOptions as $value => $label) : ?>
							<option value="<?php echo attribute_escape( $value ); ?>"<?php
								if ($value == $geo_mashup_options->get ( 'global_map', 'show_future' )) {
									echo ' selected="selected"';
								}
							?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load', 'GeoMashup'); ?></th>
						<td>
							<input id="click_to_load" name="global_map[click_to_load]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'global_map', 'click_to_load' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load Text', 'GeoMashup'); ?></th>
						<td>
							<input id="click_to_load_text" 
								name="global_map[click_to_load_text]" 
								type="text" 
								size="50" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'global_map', 'click_to_load_text' ) ); ?>" />
						</td>
					</tr>
					<tr><td colspan="2" align="center">
						<table>
							<tr><th><?php _e('Category', 'GeoMashup'); ?></th><th><?php _e('Color'); ?></th>
								<th><?php _e('Show Connecting Line Until Zoom Level (0-20 or none)','GeoMashup'); ?></th></tr>
							<?php $categories = get_categories( array( 'hide_empty' => false ) ); ?>
							<?php if (is_array($categories)) : ?>
								<?php foreach($categories as $category) : ?>
								<tr><td><?php echo esc_html( $category->name ); ?></td>
									<td>
										<select id="category_color_<?php echo attribute_escape( $category->slug ); ?>" 
											name="global_map[category_color][<?php echo attribute_escape( $category->slug ); ?>]">
										<?php foreach($colorNames as $name => $rgb) : ?>
											<option value="<?php echo attribute_escape( $name ); ?>"<?php
												if ($name == $geo_mashup_options->get ( 'global_map', 'category_color', $category->slug ) ) {
													echo ' selected="selected"';
												}
											?> style="background-color:<?php echo attribute_escape( $rgb ); ?>'"><?php echo esc_html( $name ); ?></option>
										<?php endforeach; ?>	
										</select>
									</td><td>
									<input id="category_line_zoom_<?php 
										echo attribute_escape( $category->slug ); ?>" name="global_map[category_line_zoom][<?php 
										echo attribute_escape( $category->slug ); ?>]" value="<?php 
										echo attribute_escape( $geo_mashup_options->get( 'global_map', 'category_line_zoom', $category->slug ) );
									?>" type="text" size="2" maxlength="2" /></td></tr>
								<?php endforeach; ?>	
							<?php endif; ?>
						</table>
					</td></tr>
				</table>
				<div class="submit"><input type="submit" name="submit" value="<?php _e('Update Options', 'GeoMashup'); ?>" /></div>
			</fieldset>
			<fieldset id="geo-mashup-context-map-settings">
				<p><?php _e('Default settings for contextual maps, which include just the items shown on a page, for example.', 'GeoMashup'); ?></p>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr>
						<th scope="row"><?php _e('Map Width', 'GeoMashup'); ?></th>
						<td>
							<input id="context_map_width" 
								name="context_map[width]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'context_map', 'width' ) ); ?>" />
							<?php _e('Pixels, or append %.', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Height', 'GeoMashup'); ?></th>
						<td>
							<input id="context_map_height" 
								name="context_map[height]" 
								type="text" 
								size="5" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'context_map', 'height' ) ); ?>" />
							<?php _e('px', 'GeoMashup'); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Map Control', 'GeoMashup'); ?></th>
						<td>
							<select id="context_map_control" name="context_map[map_control]">
							<?php foreach ( $mapControls as $type => $label ) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php 
									if ($type == $geo_mashup_options->get ( 'context_map', 'map_control' )) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>	
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default Map Type', 'GeoMashup'); ?></th>
						<td>
							<select id="context_map_type" name="context_map[map_type]">
							<?php	foreach($mapTypes as $type => $label) : ?>
								<option value="<?php echo attribute_escape( $type ); ?>"<?php
									if ($type == $geo_mashup_options->get ( 'context_map', 'map_type' )) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Map Type Control', 'GeoMashup'); ?></th>
						<td>
						<?php foreach ( $mapTypes as $type => $label ) : ?>
						<input id="context_add_map_type_<?php echo attribute_escape( $type ); ?>" 
							name="context_map[add_map_type_control][]" 
							type="checkbox" 
							value="<?php echo attribute_escape( $type ); ?>" <?php 
								if ( in_array( $type, $geo_mashup_options->get ( 'context_map', 'add_map_type_control' ) ) ) {
									echo ' checked="checked"';
								}
								?> /> <?php echo esc_html( $label ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Overview Control', 'GeoMashup'); ?></th>
						<td>
							<input id="context_add_overview_control" name="context_map[add_overview_control]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'context_map', 'add_overview_control' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Add Google Bar', 'GeoMashup'); ?></th>
						<td>
							<input id="context_add_google_bar" name="context_map[add_google_bar]" type="checkbox" value="true"<?php 
							if ($geo_mashup_options->get ( 'context_map', 'add_google_bar' ) == 'true') {
								echo ' checked="checked"';
							}
						?> />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Enable Scroll Wheel Zoom', 'GeoMashup'); ?></th>
					<td><input id="context_enable_scroll_wheel_zoom" name="context_map[enable_scroll_wheel_zoom]" type="checkbox" value="true"<?php 
						if ( $geo_mashup_options->get ( 'context_map', 'enable_scroll_wheel_zoom' ) == 'true' ) {
							echo ' checked="checked"';
						}
					?> /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Default Zoom Level', 'GeoMashup'); ?></th>
						<td>
							<select id="zoom" name="context_map[zoom]">
								<?php foreach ( $zoomOptions as $value => $label ) : ?>
								<option value="<?php echo attribute_escape( $value ); ?>"<?php
									if ( strcmp( $value, $geo_mashup_options->get( 'context_map', 'zoom' ) ) == 0 ) {
										echo ' selected="selected"';
									}
								?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="setting-description"><?php 
								_e( '0 is zoomed all the way out.', 'GeoMashup' ); 
							?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Marker Selection Behaviors', 'GeoMashup'); ?></th>
						<td>
							<input id="context_marker_select_info_window" name="context_map[marker_select_info_window]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'context_map', 'marker_select_info_window' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Open info window', 'GeoMashup' ); ?>
							<input id="context_marker_select_highlight" name="context_map[marker_select_highlight]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'context_map', 'marker_select_highlight' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Highlight', 'GeoMashup' ); ?>
							<input id="context_marker_select_center" name="context_map[marker_select_center]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'context_map', 'marker_select_center' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Center', 'GeoMashup' ); ?>
							<input id="context_marker_select_attachments" name="context_map[marker_select_attachments]" type="checkbox" value="true"<?php 
								echo ( $geo_mashup_options->get( 'context_map', 'marker_select_attachments' ) == 'true' ) ? ' checked="checked"' : ''; 
							?> />
							<?php _e( 'Show Geo Attachments', 'GeoMashup' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load', 'GeoMashup'); ?></th>
						<td>
							<input id="context_click_to_load" name="context_map[click_to_load]" type="checkbox" value="true"<?php 
								if ($geo_mashup_options->get ( 'context_map', 'click_to_load' ) == 'true') {
									echo ' checked="checked"';
								}
							?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Click To Load Text', 'GeoMashup'); ?></th>
						<td>
							<input id="context_click_to_load_text" 
								name="context_map[click_to_load_text]" 
								type="text" 
								size="50" 
								value="<?php echo attribute_escape( $geo_mashup_options->get ( 'context_map', 'click_to_load_text' ) ); ?>" />
						</td>
					</tr>
				</table>
				<div class="submit"><input type="submit" name="submit" value="<?php _e('Update Options', 'GeoMashup'); ?>" /></div>
			</fieldset>
		</form>
		<?php if ( isset( $_GET['view_activation_log'] ) ) : ?>
		<div class="updated">
			<p><strong><?php _e( 'Activation Log', 'GeoMashup' ); ?></strong></p>
			<?php echo get_option( 'geo_mashup_activation_log' ) ?> 
		</div>
		<?php else : ?>
		<p><a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;view_activation_log=1"><?php _e('View Activation Log', 'GeoMashup'); ?></a></p>
		<?php endif; ?>
		<p><a href="http://code.google.com/p/wordpress-geo-mashup/wiki/Documentation"><?php _e('Geo Mashup Documentation', 'GeoMashup'); ?></a></p>
	</div>
<?php
}
?>
