<?php
/*
Plugin Name: NaNoWriMo Report Card
Description: Makes it easy to keep track of your novel progress on your wordpress powered blog.
Version: 1.0
Author: David Williamson
Author URI: http://davidized.com/
Author: Email: projects@davidized.com
License: 
*/

/*  Copyright 2013  David Williamson  (email : projects@davidized.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class NaNoReportCard {

	function __construct() {
	
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'setup_pages' ) );
	
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
	
	} // end constructor

	function activate( $network_wide ) {
	
	} // end activate
	
	function deactivate( $network_wide ) {
	
	} // end deactivate
	
	function uninstall( $network_wide ) {
	
	} //end uninstall
	
	function init() {
		
		load_plugin_textdomain( 'nanorc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		$nanorc_update_args = array(
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'map_meta_cap' => true,
			'rewrite' => false,
		);
		register_post_type( 'nanorc_update', $nanorc_update_args );
		
		$nanorc_event_args = array(
			'public' => false,
			'rewrite' => false,
		);
		register_taxonomy( 'nanorc_event', 'nanorc_update', $nanorc_event_args );
	} // end init
	
	function setup_pages() {
		add_dashboard_page( __( 'NaNoWriMo Report Card Updates',  'nanorc' ), __( 'NaNoWriMo', 'nanorc' ), 'edit_posts', 'nanorc-update', array($this, 'page_updates') );
		add_options_page( __( 'NaNoWriMo Report Card Settings',  'nanorc' ), __( 'NaNo Report Card', 'nanorc' ), 'edit_posts', 'nanorc-options', array($this, 'page_options') );
	
	} // end setup_pages

	/**
	 * Displays a date (no time) form that can be used when creating/editing events.
	 *
	 * @package NaNoRC
	 * @since	1.0
	 * 
	 * @param	string	$selected_date	A MySQL formatted date string
	 * @param	string	$id_prefix		Prefix to be used for form ids (useful when you need more than one date form at a time)
	 */
	function date_form( $selected_date, $id_prefix = '' ) {
		global $wp_locale;
	
		$jj = mysql2date( 'd', $selected_date, false );
		$mm = mysql2date( 'm', $selected_date, false );
		$aa = mysql2date( 'Y', $selected_date, false );
		
		$month = '<select id="' . $id_prefix . 'mm" name="' . $id_prefix . 'mm">' . "\n";
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise($i, 2);
			$month .= "\t\t\t\t\t\t\t" . '<option value="' . $monthnum . '"';
			if ( $i == $mm )
				$month .= ' selected="selected"';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
		}
		$month .= '</select>';

		$day = '<input type="text" id="' . $id_prefix . 'jj" name="' . $id_prefix . 'jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
		$year = '<input type="text" id="' . $id_prefix . 'aa" name="' . $id_prefix . 'aa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';

		echo '<div class="timestamp-wrap">';
		/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		printf(__('%1$s%2$s, %3$s'), $month, $day, $year);
		
		echo '</div>';
	
	} // end date_form
	
	// Displays the NaNoWriMo page under the Dashbaord
	function page_updates() {
	?>
		<div class="wrap">
			<h2><?php _e( 'NaNoWriMo Report Card Updates', 'nanorc' ); ?></h2>
			
			
		</div>
	<?php
	} // end page_updates
	
	// Displays the NaNoWriMo Report Card Settings page
	function page_options() {
	?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'NaNoWriMo Report Card Settings', 'nanorc' ); ?></h2>
			
			<h3 class="title"><?php _e( 'Events', 'nanorc' ); ?></h3>
			
			<div id="col-container">
				<div id="col-right">
					<table class="wp-list-table widefat fixed nanorc_events">
						<thead>
							<tr>
								<th><?php _e( 'Event Name', 'nanorc' ); ?></th>
								<th><?php _e( 'Title', 'nanorc' ); ?></th>
								<th><?php _e( 'Start Date', 'nanorc' ); ?><br /><?php _e( 'End Date', 'nanorc' ); ?></th>
								<th><?php _e( 'Goal', 'nanorc' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'Event Name', 'nanorc' ); ?></th>
								<th><?php _e( 'Title', 'nanorc' ); ?></th>
								<th><?php _e( 'Start Date', 'nanorc' ); ?><br /><?php _e( 'End Date', 'nanorc' ); ?></th>
								<th><?php _e( 'Goal', 'nanorc' ); ?></th>
							</tr>
						</tfoot>
						<tbody>
							<tr class="example alternate">
								<td>
									<strong>Test Event</strong>
									<br /><br />
									<div class="row-actions">
										<span class="edit"><a href="#"><?php _e( 'Edit', 'nanorc' ); ?></a></span> |
										<span class="trash"><a href="#" class="submitdelete"><?php _e( 'Delete', 'nanorc' ); ?></a></span>
									</div>
								</td>
								<td>My Amazing Novel</td>
								<td>Nov 1, 2011<br />Nov 30, 2011</td>
								<td>50000 words</td>
							</tr>
							<tr class="example">
								<td>
									<strong>Test Event</strong>
									<br /><br />
									<div class="row-actions">
										<span class="edit"><a href="#"><?php _e( 'Edit', 'nanorc' ); ?></a></span> |
										<span class="trash"><a href="#" class="submitdelete"><?php _e( 'Delete', 'nanorc' ); ?></a></span>
									</div>
								</td>
								<td>Another Amazing Novel</td>
								<td>Nov 1, 2012<br />Nov 30, 2012</td>
								<td>50000 words</td>
							</tr>			
						</tbody>

					</table>
				</div> <!-- #col-right -->
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php _e( 'Add New Event', 'nanorc' ); ?></h3>
							<form id="addevent" action="page_options" method="post">
								<input type="hidden" value="add-event" name="action" />
								<?php wp_nonce_field('nanorc_addevent_nonce', 'nanorc_addevent_submit'); ?>

								<div class="form-field-required">
									<label for="event-name"><?php _e( 'Event Name', 'nanorc' ); ?></label>
									<input type="text" name="event-name" id="event-name" size="40" value="" />
								</div>
								
								<div class="form-field">
									<label for="event-title"><?php _e( 'Title', 'nanorc' ); ?></label>
									<input type="text" name="event-title" id="event-title" size="40" value="" />
								</div>

								<div class="form-field">
									<h4><?php _e( 'Start Date', 'nanorc' ); ?></h4>
									<?php $this->date_form( current_time('mysql'), 'event-start-' ); ?>
								</div>

								<div class="form-field">
									<h4><?php _e( 'End Date', 'nanorc' ); ?></h4>
									<?php $this->date_form( current_time('mysql'), 'event-end-' ); ?>
								</div>

								<div class="form-field-required">
									<h4><?php _e( 'Goal', 'nanorc' ); ?></h4>
									<input type="text" name="goal-count" id="goal-count" size="40" value="" /> 

									<select name="goal-type" id="goal-type">
										<option value="words"><?php _e( 'Words', 'nanorc' ); ?></option>
										<option value="pages"><?php _e( 'Pages', 'nanorc' ); ?></option>
										<option value="scenes"><?php _e( 'Scenes', 'nanorc' ); ?></option>
										<option value="hours"><?php _e( 'Hours', 'nanorc' ); ?></option>
									</select>
								</div>
								
								<p class="submit">
									<input type="submit" class="button button-primary" value="Create Event" name="" />
								</p>
									
							</form>	
						</div> <!-- .form-wrap -->
					</div> <!-- .col-wrap -->
				</div> <!-- #col-left -->
			</div> <!-- #col-container -->
		</div> <!-- #wrap -->
	<?php
	} // end page_options


}
new NaNoReportCard;
?>
