<?php 

/**
 * Strings and variables
 *
 * (1) Define different strings and variables used throughout Regular Board
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$regular_board_posts           = $wpdb->prefix . 'regular_board_posts';
$regular_board_boards          = $wpdb->prefix . 'regular_board_boards';
$regular_board_users           = $wpdb->prefix . 'regular_board_users';
$regular_board_bans            = $wpdb->prefix . 'regular_board_bans';
$regular_board_logs            = $wpdb->prefix . 'regular_board_logs';
$regular_board_messages        = $wpdb->prefix . 'regular_board_messages';
$regular_board_friends         = $wpdb->prefix . 'regular_board_friends';

$user_logged_in                = 0;
if ( is_user_logged_in() ) {
	$user_logged_in            = 1;
}

$regular_board_messages_select = 'messages_id, messages_date, messages_subject, messages_content, messages_to, messages_from, messages_read';
$regular_board_friends_select  = 'friends_id, friends_connector, friends_connectee, friends_mutual';

$user_exists                   = 0;
$require_logged                = 0;
$post_nom                      = 0;
$postno                        = 0;
$post_no                       = 1;
$my_unread                     = 0;
$my_waitings                   = 0;
$wipe_countdown                = '';
$LOCKED                        = '';
$checkLOCK                     = '';
$query                         = '';
$profile_name                  = '';
$profile_email                 = '';
$search                        = '';
$board_id                      = '';
$board_name                    = '';
$board_short                   = '';
$board_description             = '';
$board_mods                    = '';
$board_jans                    = '';
$board_posts                   = '';
$the_board                     = '';
$thisboard                     = '';
$this_area                     = '';
$this_user                     = '';
$this_thread                   = '';
$results                       = '';
$usermod                       = '';
$is_moderator                  = '';		
$is_user_janitor               = '';
$lock                          = '';
$timegateactive                = '';
$correct                       = '';
$getposts                      = '';
$gotReplies                    = '';
$banned_count                  = '';
$board_rules                   = '';
$entered_parent                = 0;
if ( get_option ( 'regular_board_protected' ) ) {
	$protectedboards           = explode   ( ',', get_option ( 'regular_board_protected' ) );
	$protected_boards          = array_map ( 'regular_board_apply_quotes',  $protectedboards );
}
$regular_board_footer          = '';
if ( get_option ( 'regular_board_footer' ) ) {
	$regular_board_footer      = get_option ( 'regular_board_footer' );
}
$registration_open             = get_option ( 'regular_board_registration' );
$enable_blog                   = get_option ( 'regular_board_enableblog' );
$display_wipe                  = get_option ( 'regular_board_wipedisplay' );
$banned_image                  = get_option ( 'regular_board_bannedimage' );
$board_banner                  = get_option ( 'regular_board_boardbanner' );
$accounts_per_ip               = get_option ( 'regular_board_accountsper' );
$boards_or_tags                = get_option ( 'regular_board_useboards' );
if ( $boards_or_tags == strtolower ( 'boards' ) ) {
	$protocol = 'boards';
} elseif ( $boards_or_tags == strtolower ( 'tags' ) ) {
	$protocol = 'tags';
} else {
	$protocol = 'boards';
}
$blog_title                    = get_bloginfo();
$board_wipe_every              = get_option ( 'regular_board_wipeall' );
$board_wipe_per                = get_option ( 'regular_board_wipeper' );
$board_wipe_date               = strtotime ( get_option ( 'regular_board_wipealldate' ) );
$current_timestamp             = date ( 'Y-m-d H:i:s' );		

/*
 * Time functionality
 * Set up some variables for different points in time, using current time as an anchor.
 */
 $ten_minutes_from_now  = date("Y-m-d H:i:s", strtotime('+10 minutes'));
 $ten_minutes_ago       = date("Y-m-d H:i:s", strtotime('-10 minutes'));
 $two_hours_from_now    = date("Y-m-d H:i:s", strtotime('+2 hours'   ));
 $two_hours_ago         = date("Y-m-d H:i:s", strtotime('-2 hours'   ));
 $twelve_hours_from_now = date("Y-m-d H:i:s", strtotime('+12 hours'  ));
 $twelve_hours_ago      = date("Y-m-d H:i:s", strtotime('-12 hours'  ));
 $one_day_from_now      = date("Y-m-d H:i:s", strtotime('+1 day'     ));
 $one_day_ago           = date("Y-m-d H:i:s", strtotime('-1 day'     ));
 $one_month_from_now    = date("Y-m-d H:i:s", strtotime('+1 month'   ));
 $one_month_ago         = date("Y-m-d H:i:s", strtotime('-1 month'   ));
 
 
$formatting                    = get_option ( 'regular_board_formatting' );
$auto_url                      = get_option ( 'regular_board_autourl' );
$announcements                 = get_option ( 'regular_board_announcements' );
$max_links                     = get_option ( 'regular_board_maxlinks' );
$search_enabled                = get_option ( 'regular_board_search' );
$enable_url                    = get_option ( 'regular_board_enableurl' );
$enable_rep                    = get_option ( 'regular_board_enablerep' );
$max_body                      = get_option ( 'regular_board_maxbody' );
$max_replies                   = get_option ( 'regular_board_maxreplies' );
$max_text                      = get_option ( 'regular_board_maxtext' );
$these_boards                  = get_option ( 'regular_board_boards' );
if ( $these_boards ) {
	$these_boards              = explode   ( ',', $these_boards );
	$these_boards              = array_map ( 'regular_board_apply_quotes',  $these_boards );
}
$user_flood                    = get_option ( 'regular_board_userflood' );
$imgurid                       = get_option ( 'regular_board_imgurid' );			
$flood_gate                    = get_option ( 'regular_board_floodgate' );
$archive_gate                  = get_option ( 'regular_board_archivegate' );
$posts_per_page                = get_option ( 'regular_board_postsper' );
$roll                          = get_option ( 'regular_board_roll' );
$id_display                    = get_option ( 'regular_board_ids' );
$user_create                   = get_option ( 'regular_board_usercreate' );
$mod_code                      = '<strong>' . get_option ( 'regular_board_modcode', '##MOD' ) . '</strong>';
$user_mod_code                 = '<strong>' . get_option ( 'regular_board_usermodcode', '##JRMOD' ) . '</strong>';
$current_page                  = protocol_relative_url_dangit( get_permalink() );
$the_ip                        = $ipaddress;
$user_ip                       = sanitize_text_field ( wp_hash ( $the_ip ) );
if ( $user_total_allowed ) {
	if ( $user_total_allowed <= $count_users_total ) {
		$registration_open     = 0;
	} else {
		$registration_open     = 1;
	}
}
$check_this_ip                 = sanitize_text_field ( $the_ip );
$query                         = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
$selfpost                      = '';
if ( $query ) {
	if ( isset ( $_GET['b'] ) ) {
		$the_board         = sanitize_text_field ( $_GET['b'] );
	}
	if ( isset ( $_GET['ht'] ) ) {
		$the_tag               = sanitize_text_field ( $_GET['ht'] );
	}
	if ( isset ( $_GET['a'] ) ) {
		$this_area             = sanitize_text_field ( strtolower( $_GET['a'] ) );
	}
	if ( isset ( $_GET['u'] ) ) {
		$this_user             = sanitize_text_field ( strtolower( $_GET['u'] ) );
	}
	if ( isset ( $_GET['t'] ) ) {
		$this_thread           = intval ( $_GET['t'] );
	}
}
if ( $this_thread ) {
	$the_board = $wpdb->get_var( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
}
if ( !$this_area && !$the_board && !$this_user && !$this_thread && !$the_tag ) {
	$nothing_is_here           = 1;
}
$is_user_mod                   = false;
$is_user                       = true;
$posting                       = 1;
$userisbanned                  = 0;

$style = 'tiny';