<?php 

/**
 * Regular Board Uninstallation
 *
 * (1) Run this file when we are uninstalling Regular Board,
 * (1) while will delete all options and databases that the 
 * (1) installation file created.
 *
 * @package regular_board
 */	
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( !function_exists ( 'regular_board_uninstallation' ) ) {	
	function regular_board_uninstallation() {
		global $wpdb;
		$regular_board_posts   = $wpdb->prefix . 'regular_board_posts';
		$regular_board_boards  = $wpdb->prefix . 'regular_board_boards';
		$regular_board_users   = $wpdb->prefix . 'regular_board_users';
		$regular_board_bans    = $wpdb->prefix . 'regular_board_bans';
		$regular_board_logs    = $wpdb->prefix . 'regular_board_logs';
		$regular_board_friends = $wpdb->prefix . 'regular_board_friends';
		$regular_board_message = $wpdb->prefix . 'regular_board_messages';
		delete_option ( 'regular_board_noindexboards' );
		delete_option ( 'regular_board_usercreate' );
		delete_option ( 'regular_board_useboards' );
		delete_option ( 'regular_board_totaluserallowed' );
		delete_option ( 'regular_board_accountsper' );
		delete_option ( 'regular_board_registration' );
		delete_option ( 'regular_board_protected' );
		delete_option ( 'regular_board_wipeper' );
		delete_option ( 'regular_board_enableblog' );
		delete_option ( 'regular_board_boardbanner' );
		delete_option ( 'regular_board_bannedimage' );
		delete_option ( 'regular_board_wipeall' );
		delete_option ( 'regular_board_wipealldate' );
		delete_option ( 'regular_board_frontpage' );
		delete_option ( 'regular_board_footer' );
		delete_option ( 'regular_board_autourl' );
		delete_option ( 'regular_board_formatting' );
		delete_option ( 'regular_board_ascii' );
		delete_option ( 'regular_board_announcements' );
		delete_option ( 'regular_board_hideannouncements' );
		delete_option ( 'regular_board_lazyload' );
		delete_option ( 'regular_board_robots' );
		delete_option ( 'regular_board_maxlinks' );
		delete_option ( 'regular_board_css_url' );
		delete_option ( 'regular_board_search' );
		delete_option ( 'regular_board_displayboards' );
		delete_option ( 'regular_board_displaymenu' );	
		delete_option ( 'regular_board_ids' );
		delete_option ( 'regular_board_wipedisplay' );
		delete_option ( 'regular_board_focus' );
		delete_option ( 'regular_board_archivegate' );
		delete_option ( 'regular_board_floodgate' );
		delete_option ( 'regular_board_roll' );
		delete_option ( 'regular_board_postsper' );
		delete_option ( 'regular_board_modcode' );
		delete_option ( 'regular_board_usermodcode' );
		delete_option ( 'regular_board_enableurl' );
		delete_option ( 'regular_board_enablerep' );
		delete_option ( 'regular_board_maxbody' );
		delete_option ( 'regular_board_maxreplies' );
		delete_option ( 'regular_board_maxtext' );
		delete_option ( 'regular_board_boards' );
		delete_option ( 'regular_board_userflood' );
		delete_option ( 'regular_board_imgurid' );
		delete_option ( 'regular_board_dnsbl' );
		$wpdb->query ( "DROP TABLE $regular_board_posts" );
		$wpdb->query ( "DROP TABLE $regular_board_boards" );
		$wpdb->query ( "DROP TABLE $regular_board_users" );
		$wpdb->query ( "DROP TABLE $regular_board_bans" );
		$wpdb->query ( "DROP TABLE $regular_board_logs" );
	}
}