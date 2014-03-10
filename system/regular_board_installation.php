<?php 

/**
 * Regular Board Installation
 *
 * (1) Run this file when the plugin is activated, installing
 * (1) all relevant tables and options.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

function regular_board_installation(){
	add_option ( 'regular_board_boardbanner' );
	add_option ( 'regular_board_bannedimage' );
	add_option ( 'regular_board_wipeall', 'never' );
	add_option ( 'regular_board_wipealldate' );
	add_option ( 'regular_board_frontpage' );
	add_option ( 'regular_board_formatting', 1);
	add_option ( 'regular_board_autourl', 1 );
	add_option ( 'regular_board_ascii' );
	add_option ( 'regular_board_announcements' );
	add_option ( 'regular_board_hideannouncements' );
	add_option ( 'regular_board_postingoptions', 1 );
	add_option ( 'regular_board_css_url' );
	add_option ( 'regular_board_search', 1 );
	add_option ( 'regular_board_ids', 1 );
	add_option ( 'regular_board_focus', 'nothing' );
	add_option ( 'regular_board_enableurl', 1 );
	add_option ( 'regular_board_enablerep', 1 );
	add_option ( 'regular_board_maxbody', 1800 );
	add_option ( 'regular_board_maxreplies', 500 );
	add_option ( 'regular_board_maxtext', 75 );
	add_option ( 'regular_board_boards', '' );
	add_option ( 'regular_board_userflood', '' );
	add_option ( 'regular_board_imgurid', '' );
	add_option ( 'regular_board_modcode', '##MOD' );
	add_option ( 'regular_board_usermodcode', '##JRMOD' );
	add_option ( 'regular_board_postsper', 50 );
	add_option ( 'regular_board_dnsbl', '\'dnsbl-1.uceprotect.net\',\'dnsbl-2.uceprotect.net\',\'dnsbl-3.uceprotect.net\',\'dnsbl.sorbs.net\',\'zen.spamhaus.org\',\'dnsbl-2.uceprotect.net\',\'dnsbl-3.uceprotect.net\'' );
	add_option ( 'regular_board_roll', '0,100' );
	add_option ( 'regular_board_floodgate', 10 );
	add_option ( 'regular_board_archivegate', 5356800 );
	add_option ( 'regular_board_wipedisplay', 1 );
	add_option ( 'regular_board_maxlinks', 5 );
	add_option ( 'regular_board_robots', 0 );
	add_option ( 'regular_board_lazyload', 0 );

	// Old options that need to vanish.
	delete_option ( 'regular_board_displayboards');
	delete_option ( 'regular_board_displaymenu');
	
	
	global $wpdb;
	$regular_board_posts  = $wpdb->prefix.'regular_board_posts';
	$regular_board_boards = $wpdb->prefix.'regular_board_boards';
	$regular_board_users  = $wpdb->prefix.'regular_board_users';
	$regular_board_bans   = $wpdb->prefix.'regular_board_bans';
	$regular_board_logs   = $wpdb->prefix.'regular_board_logs';
	
	$boards = "CREATE TABLE $regular_board_boards(
	board_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
	board_date text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_name TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_shortname TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_description TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_mods TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_janitors TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	board_postcount BIGINT(20) NOT NULL ,
	board_locked BIGINT(20) NOT NULL ,
	board_logged BIGINT(20) NOT NULL ,
	board_wipe TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	PRIMARY KEY  (board_id)
	);";
	$posts = "CREATE TABLE $regular_board_posts(
	post_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
	post_parent BIGINT(20) NOT NULL ,
	post_name TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_email TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_title TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_comment LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_type TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_url TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_board TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_moderator TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_last TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_sticky TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_locked TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_password TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_userid BIGINT(20) NOT NULL , 
	post_public BIGINT(20) NOT NULL ,
	post_report TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	post_reportcount BIGINT(20) NOT NULL ,
	PRIMARY KEY  (post_id)
	);";
	$users = "CREATE TABLE $regular_board_users(
	user_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
	user_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	user_ip TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	user_name TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_email TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_password TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_heaven TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_boards TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_follow TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_avatar TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	user_slogan TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci ,
	PRIMARY KEY  (user_id)
	);";
	$bans = "CREATE TABLE $regular_board_bans(
	banned_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
	banned_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	banned_ip TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	banned_banned INT(11) NOT NULL,
	banned_message TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	banned_length TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	PRIMARY KEY  (banned_id)
	);";
	$logs = "CREATE TABLE $regular_board_logs(
	logs_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
	logs_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	logs_ip TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	logs_thread BIGINT(20) NOT NULL  , 
	logs_parent BIGINT(20) NOT NULL ,
	logs_board TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	logs_message TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	PRIMARY KEY  (logs_id)
	);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta ( $posts );
	dbDelta ( $boards );
	dbDelta ( $users );
	dbDelta ( $bans );
	dbDelta ( $logs );


	$date = date ( 'Y-m-d H:i:s' );

	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO $regular_board_boards 
			( 	
				board_id,
				board_date,
				board_name,
				board_shortname,
				board_description,
				board_mods,
				board_janitors,
				board_postcount,
				board_locked,
				board_logged,
				board_wipe
			)
			VALUES ( 
				%d, 
				%s, 
				%s, 
				%s, 
				%s, 
				%s, 
				%s, 
				%d, 
				%d, 
				%d,
				%s
			)",
				'',
				$date,
				'Random',
				'b',
				'Test board.',
				'',
				'',
				0,
				0,
				0,
				'never'
		)
	);	
}