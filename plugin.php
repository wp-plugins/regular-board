<?php 
/**
 * Plugin Name: Regular Board
 * Plugin URI: https://github.com/onebillion/regular_board
 * Description: Standalone (continuation) project for Regular Board, an anonymous text-based WordPress powered bbs.
 * Version: 1.12
 * Author: boyevul
 * License: GNU General Public License v2
 * License URI: //www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: regular_board
 * GitHub Plugin URI: https://github.com/onebillion/regular_board
 * 
 * Regular Board
 * 
 * @package  regular_board
 * @author   onebillion
 * @license  GPL-2.0+
 * @link     https://github.com/onebillion/regular_board
 *
 * LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with this program;if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

$regular_board_version = '1.12-stable';

register_activation_hook ( __FILE__, 'regular_board_installation_option' );
function regular_board_installation_option() {
	
	global $wpdb;
	$regular_board_posts  = $wpdb->prefix.'regular_board_posts';
	$regular_board_boards = $wpdb->prefix.'regular_board_boards';
	$regular_board_users  = $wpdb->prefix.'regular_board_users';
	$regular_board_bans   = $wpdb->prefix.'regular_board_bans';
	$regular_board_logs   = $wpdb->prefix.'regular_board_logs';
	$regular_board_friends  = $wpdb->prefix . 'regular_board_friends';
	$regular_board_messages = $wpdb->prefix . 'regular_board_messages';

	// Upgrades - for when the plugin has already been installed and we need to 
	// alter tables without forcing the user to completely uninstall everything.
	
	$friends = "CREATE TABLE $regular_board_friends(
		friends_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		friends_connector text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		friends_connectee text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		friends_mutual BIGINT(20) NOT NULL ,
		PRIMARY KEY  (friends_id)
	);";
	$messages = "CREATE TABLE $regular_board_messages(
		messages_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		messages_date text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		messages_subject text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		messages_content LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		messages_to text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		messages_from text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		messages_read BIGINT(20) NOT NULL ,
		PRIMARY KEY  (messages_id)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta ( $friends );
	dbDelta ( $messages );	
	
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_avatar TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER user_follow"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_slogan TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER user_avatar"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_posts BIGINT(20) NOT NULL AFTER user_slogan"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_level BIGINT(20) NOT NULL AFTER user_posts"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_strikes BIGINT(20) NOT NULL AFTER user_level"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_logged_in BIGINT(20) NOT NULL AFTER user_strikes"
	);	
	$wpdb->query ( 
		"ALTER TABLE $regular_board_users 
		ADD user_logged_in_from TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER user_logged_in"
	);	
	$wpdb->query ( 
		"ALTER TABLE $regular_board_logs 
		ADD logs_content TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER logs_message"
	);
	$wpdb->query ( 
		"ALTER TABLE $regular_board_posts 
		ADD post_comment_parent BIGINT(20) NOT NULL AFTER post_comment"
	);
	add_option ( 'regular_board_installation', 0 );
	delete_option ( 'regular_board_postingoptions' );
}

define         ( 'regular_board_plugin', true );
require_once   ( ABSPATH . 'wp-includes/pluggable.php' );
require_once   ( 'system/regular_board_installation.php' );
if             ( function_exists ( 'akismet_admin_init' ) ) { require_once ( 'akismet.class.php' ); }
require_once   ( 'system/regular_board_functions.php' );
require_once   ( 'system/regular_board_ip_functions.php' );
add_action     ( 'admin_enqueue_scripts', 'regular_board_admin_css' );
require_once   ( 'system/regular_board_options.php' );
$regular_board_posts_select = 'post_id, post_parent, post_name, post_date, post_email, post_title, post_comment, post_comment_parent, post_type, post_url, post_board, post_moderator, post_last, post_sticky, post_locked, post_password, post_userid, post_public, post_report, post_reportcount';
require_once   ( 'plugin/regular_board.php' );
remove_action  ( 'wp_head', 'rel_canonical' );

if ( get_option ( 'regular_board_ascii' ) ) {
	function regular_board_ascii () {
		echo "<meta property=\"regular_board_useless_stupid_ascii\" content=\"" . get_option ( 'regular_board_ascii' ) . "\" />";
	}
	add_action     ( 'wp_head', 'regular_board_ascii' );
}

add_action     ( 'wp_head', 'regular_board_canonical' );
add_action     ( 'wp_enqueue_scripts', 'regular_board_style' );
add_action     ( 'wp_head', 'regular_board_head' );
add_shortcode  ( 'regular_board', 'regular_board_shortcode' );
add_filter     ( 'the_content','do_shortcode', 'regular_board_shortcode' );
add_filter     ( 'jetpack_enable_opengraph', '__return_false', 99 );
	
?>