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
			<div id="icon-options-general" class="icon32"><br /></div><h2><?php _e( 'NaNoWriMo Report Card Settings', 'nanorc' ); ?></h2>
			
			
		</div>
	<?php
	} // end page_options


}
new NaNoReportCard;
?>
