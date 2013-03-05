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
	
    /**
     * Takes month, day, and year from input form, validates input, and outputs in MySQL date format 
	 *
	 * @package NaNoRC
	 * @since	1.0
     * 
	 * @param	string	$month	Month from form input
	 * @param	string	$day	Day from form input
	 * @param	string	$year	Year from form input - may be YY or YYYY, will be converted later
	 *
     * @return	bool	Returns false if the inputs are not propertly formatted
	 * @return	string	Returns MySQL formatted date Y-m-d H:i:s with 00 for H, i, s
     */
	function date_validate_construct( $month, $day, $year ) {
	
		$month = intval( $month );
		$day = intval( $day );
		$year = intval( $year );
		
		if ( ! $month || ! $day || ! $year )
			die('invalid ints'); //return false;
		
		if ( 1 > $month || 12 < $month )
			die('invalid month'); //return false;
			
		if ( 4 != strlen( $year ) )
			die('invalid year'); //return false;
			
		switch( $month ):
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 9:
			case 10:
			case 12:
				if ( 31 < $day )
					die('invalid day'); //return false;	
				break;
			case 4:
			case 6:
			case 11:
				if ( 30 < $day )
					die('invalid day'); //return false;
				break;
			case 2:
				if ( 0 == date('L', strtotime("$year-01-01") )  && 28 < $day )
					die('invalid day'); //return false;
				
				if ( 1 == date('L', strtotime("$year-01-01") ) && 29 < $day )
					die('invalid day'); //return false;
				break;
			default:
				die('invalid month/day' . $month);
				return false;
		endswitch;

		return sprintf('%d-%02d-%d 00:00:00', $year, $month, $day);

	} // end date_validate
	
    /**
     * 
     * @package NaNoRC
	 * @since 1.0
     * 
     * @return <type>
     */
	function create_event() {
	
		$name = sanitize_text_field( $_POST['event-name'] );
		$title = sanitize_text_field( $_POST['event-title'] );
		$goal = intval($_POST['goal-count']);
		$goal_type = $_POST['goal-type'];
		$start_date = $this->date_validate_construct($_POST['event-start-mm'], $_POST['event-start-jj'], $_POST['event-start-aa']);
		$end_date = $this->date_validate_construct($_POST['event-end-mm'], $_POST['event-end-jj'], $_POST['event-end-aa']);
		
		$possible_goal_type = array( 'words', 'pages', 'scenes', 'hours' );
			
		if ( ! $goal || ! in_array( $goal_type, $possible_goal_type ) || false == $start_date || false == $end_date ) {
			die( 'Invalid input options' );
		} else {
			$description = array(
				'title' => $title,
				'goal' => $goal,
				'goal_type' => $goal_type,
				'start_date' => $start_date,
				'end_date' => $end_date,
				);
		}
		
		$description = maybe_serialize( $description );
				
		return wp_insert_term( $name, 'nanorc_event', array( 'description' => $description ) );
		
			
	} // end create_event
	
    /**
     * 
	 *
	 * @package NaNoRC
	 * @since	1.0
     * 
     * @param	int		$event_id	The term_id for the event being updated.
     * @return <type>
     */
	function update_event( $event_id ) {
		
		$name = sanitize_text_field( $_POST['event-name'] );
		$title = sanitize_text_field( $_POST['event-title'] );
		$goal = intval($_POST['goal-count']);
		$goal_type = $_POST['goal-type'];
		$start_date = $this->date_validate_construct($_POST['event-start-mm'], $_POST['event-start-jj'], $_POST['event-start-aa']);
		$end_date = $this->date_validate_construct($_POST['event-end-mm'], $_POST['event-end-jj'], $_POST['event-end-aa']);
		
		$possible_goal_type = array( 'words', 'pages', 'scenes', 'hours' );
			
		if ( ! $goal || ! in_array( $goal_type, $possible_goal_type ) || false == $start_date || false == $end_date ) {
			die( 'Invalid input options' );
		} else {
			$description = array(
				'title' => $title,
				'goal' => $goal,
				'goal_type' => $goal_type,
				'start_date' => $start_date,
				'end_date' => $end_date,
				);
		}
		
		$description = maybe_serialize( $description );
		
		return wp_update_term( $event_id, 'nanorc_event', array( 'name' => $name, 'description' => $description ) );
		
	} // end update_event
	
    /**
     * 
     * 
	 *
	 * @package NaNoRC
	 * @since	1.0
     * 
     * @param	int		$event_id	Term_id of the event to delete from the database
     * @return <type>
     */
	 function delete_event( $event_id ) {
	 
	 } // end delete_event
	
	 
	
    /**
     * Generates and displays table rows for the events in the database.
     * 
	 * @package NaNoRC
     * @since 1.0
     * 
     */
	 function event_rows() {
	 
		$delete_event_url = admin_url() . 'options-general.php?page=nanorc-options&action=delete-event';
		
		$events = get_terms( 'nanorc_event', array( 'orderby' => 'id', 'hide_empty' => 0, ) );
	 
		$counter = 1; //To add alternate class to rows
		
		foreach ( $events as $event ):
		
			$event_class = 'class="nanorc-event-' . $event->term_id;
			if ( 1 == $counter % 2 )
				$event_class .= ' alternate';
			$event_class .= '"';
		
			$event->description = maybe_unserialize( $event->description );
			
		/**
		 * @todo Make Edit and Delete links functional
		 * @todo Delete link only available if there aren't any updates for that event (otherwise we'll end up with orphaned updates in the database)
		 */
		
		?>
	 		<tr <?php echo $event_class; ?>>
				<td>
					<strong><?php echo $event->name; ?></strong>
					<br /><br />
					<div class="row-actions">
						<?php if ( current_user_can( 'manage_options' ) ): ?>
						<span class="edit"><a href="<?php echo esc_url(admin_url() . 'options-general.php?page=nanorc-options&action=edit-event&event_id=' . $event->term_id); ?>"><?php _e( 'Edit', 'nanorc' ); ?></a></span> 
						<?php endif; ?>
						<?php if ( 0 == $event->count && current_user_can( 'manage_options' ) ): ?>
						| <span class="trash"><a href=" <?php echo esc_url(wp_nonce_url( $delete_event_url . '&event_id=' . $event->term_id, 'nanorc_event_delete_' . $event->term_id )); ?> " class="submitdelete"><?php _e( 'Delete', 'nanorc' ); ?></a></span>
						<?php endif; ?>
					</div>
				</td>
				<td><?php echo $event->description['title']; //Title ?></td>
				<td>
					<?php echo $event->description['start_date']; //Start Date ?>
					<br />
					<?php echo $event->description['end_date']; //End Date?>
				</td>
				<td><?php echo $event->description['goal']; //Goal ?> <?php echo $event->description['goal_type']; //Goal Type?></td>
			</tr>
		<?php
			$counter++;
		endforeach;
	 
	 } // end event_rows()
	
	
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
	
		$sendback = wp_get_referer();
	
		if( !empty($_POST) && 'add-event' == $_POST['action'] && check_admin_referer('nanorc_addevent_nonce') && current_user_can( 'manage_options' ) ) {
			
			$this->create_event();
		
		} // Add new event
		
		if ( isset( $_GET['event_id'] ) && 'edit-event' == $_GET['action'] && current_user_can( 'manage_options' ) ) {

			if ( !empty($_POST) && check_admin_referer( 'nanorc_edit_event_' . $_POST['event_id'] ) ) {
			
				$this->update_event( $_GET['event_id'] );
				
				/**
				 * @todo Display an admin message with something along the lines of "Event __ updated."
				 */
			
			} elseif ( term_exists( intval($_GET['event_id']) ) ) {
			
				$this->page_edit_event( intval($_GET['event_id']) );
				
			} else {
				
				wp_redirect( $sendback );
								
			}// Update event or display event form if event exists
			
		} elseif ( isset( $_GET['event_id'] ) && 'delete-event' == $_GET['action'] && check_admin_referer( 'nanorc_event_delete_' . $_GET['event_id'] ) ) {
			echo 'Looks like you want to delete the term: ' . get_term($_GET['event_id'], 'nanorc_event')->name;
		} else {
		
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
							<?php echo $this->event_rows(); ?>
						</tbody>

					</table>
				</div> <!-- #col-right -->
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php _e( 'Add New Event', 'nanorc' ); ?></h3>
							<form id="addevent" action="options-general.php?page=nanorc-options" method="post">
								<input type="hidden" value="add-event" name="action" />
								<?php wp_nonce_field('nanorc_addevent_nonce'); ?>

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
									<input type="submit" class="button button-primary" value="<?php _e('Create Event', 'nanorc'); ?>" name="" />
								</p>
									
							</form>	
						</div> <!-- .form-wrap -->
					</div> <!-- .col-wrap -->
				</div> <!-- #col-left -->
			</div> <!-- #col-container -->
		</div> <!-- #wrap -->
	<?php
		}
	} // end page_options
	
	function page_edit_event( $event_id ) {
	
		$event = get_term( $event_id, 'nanorc_event' );
		$event_meta = maybe_unserialize( $event->description );

	?>
		<div id="wrap">	
			<?php screen_icon(); ?>
			<h2><?php _e( 'NaNoWriMo Report Card Settings', 'nanorc' ); ?></h2>
			
			<h3 class="title"><?php _e( 'Edit Event', 'nanorc' ); ?></
			<div class="form-wrap">
				<form id="addevent" action="options-general.php?page=nanorc-options&action=edit-event&event_id=<?php echo $event_id; ?>" method="post">
					<input type="hidden" value="edit-event" name="action" />
					<input type="hidden" value="<?php echo $event_id; ?>" name="event_id" />
					<?php wp_nonce_field('nanorc_edit_event_' . $event_id); ?>

					<div class="form-field-required">
						<label for="event-name"><?php _e( 'Event Name', 'nanorc' ); ?></label>
						<input type="text" name="event-name" id="event-name" size="40" value="<?php echo $event->name; ?>" />
					</div>
					
					<div class="form-field">
						<label for="event-title"><?php _e( 'Title', 'nanorc' ); ?></label>
						<input type="text" name="event-title" id="event-title" size="40" value="<?php echo $event_meta['title']; ?>" />
					</div>

					<div class="form-field">
						<h4><?php _e( 'Start Date', 'nanorc' ); ?></h4>
						<?php $this->date_form( $event_meta['start_date'], 'event-start-' ); ?>
					</div>

					<div class="form-field">
						<h4><?php _e( 'End Date', 'nanorc' ); ?></h4>
						<?php $this->date_form( $event_meta['end_date'], 'event-end-' ); ?>
					</div>

					<div class="form-field-required">
						<h4><?php _e( 'Goal', 'nanorc' ); ?></h4>
						<input type="text" name="goal-count" id="goal-count" size="40" value="<?php echo $event_meta['goal']; ?>" /> 

						<select name="goal-type" id="goal-type">
							<option value="words" <?php if ( 'words' == $event_meta['goal_type'] ) echo 'selected'; ?>><?php _e( 'Words', 'nanorc' ); ?></option>
							<option value="pages" <?php if ( 'pages' == $event_meta['goal_type'] ) echo 'selected'; ?>><?php _e( 'Pages', 'nanorc' ); ?></option>
							<option value="scenes" <?php if ( 'scenes' == $event_meta['goal_type'] ) echo 'selected'; ?>><?php _e( 'Scenes', 'nanorc' ); ?></option>
							<option value="hours" <?php if ( 'hours' == $event_meta['goal_type'] ) echo 'selected'; ?>><?php _e( 'Hours', 'nanorc' ); ?></option>
						</select>
					</div>
					
					<p class="submit">
						<input type="submit" class="button button-primary" value="<?php _e('Update Event', 'nanorc'); ?>" name="" />
					</p>
						
				</form>	
			</div> <!-- .form-wrap -->
		</div> <!-- #wrap -->
	
	<?php

	} // end page_edit_event


}
new NaNoReportCard;
?>