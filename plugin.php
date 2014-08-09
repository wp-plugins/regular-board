<?php 

/**
 *
 * Plugin Name: Regular Board
 * Version: 2.00.0.4
 * License: GNU General Public License v2
 * License URI: //gnu.org/licenses/gpl-2.0.html
 * Author: Matthew Trevino
 * Author URI: //wordpress.org/plugins/my-optional-modules/
 *
 * LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program;if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 *
 */
 
 /**
  *
  * Regular Board Requirements
  * (1) cURL
  * (2) PHP5+
  * (3) WordPress 3.9.1+
  * (4) "Pretty Permalinks" (not default WordPress permalink structure)
  *
  */
	

 /**
  *
  * Cookie functionality
  * (1) sets a cookie for the password and name that expires in 30 days
  * (2) prefills name and password fields in both form and delete area
  * (3) set it first because we need to set this stuff before everything is sent
  *
  */
$self_domain  = sanitize_text_field( $_SERVER['SERVER_NAME'] );
if( isset( $_POST['do_post'] ) ) {
	$expires_days = 30;
	$expires      = time() + 60 * 60 * 24 * $expires_days;
	setcookie( 'post_password', sanitize_text_field( $_REQUEST['post_password'] ), $expires, '/', $self_domain );
	setcookie( 'post_name', sanitize_text_field( $_REQUEST['post_name'] ), $expires, '/', $self_domain );	
}

/**
 *
 * SQL row selection information
 *
 */
$regularboardplugin_posts_select = '
	post_id, 
	post_parent, 
	post_name, 
	post_date, 
	post_date_micro,
	post_email, 
	post_title, 
	post_comment, 
	post_comment_original, 
	post_edited, 
	post_moderator_comment, 
	post_type, 
	post_url, 
	post_provider, 
	post_domain, 
	post_board, 
	post_moderator, 
	post_last, 
	post_sticky, 
	post_locked, 
	post_password, 
	post_userid, 
	post_report, 
	post_reportcount, 
	post_reply_count, 
	post_guestip, 
	post_public, 
	post_like, 
	post_dislike, 
	post_approval_rating, 
	post_delete_this, 
	post_banned
';

/**
 *
 * Installation/uninstallation
 * Handle all of our installation procedures in this area, including 
 * anything that needs to be taken care upon activation of the 
 * plugin.
 *
 */
	
// (1) Uncomment the uninstall hook & deactivate/reactivate the plugin to uninstall it.
// register_activation_hook( __FILE__, 'regularboardplugin_uninstall' );
if( !function_exists( 'regularboardplugin_uninstall' ) ) {

	function regularboardplugin_uninstall() {

		global $wpdb;
		$regularboardplugin_posts = $wpdb->prefix . 'regularboardplugin_posts';
		delete_option( 'regularboardplugin_installed' );
		$wpdb->query( "DROP TABLE $regularboardplugin_posts" );

	}

}

// (1) Check to see if Regular Board has been installed previously
if( !get_option( 'regularboardplugin_installed' ) ) {
	
		if( !function_exists( 'clean_up_old_regularboard' ) ) {
			
			// Upgrade from previous versions that use outdated code and references
			register_activation_hook( __FILE__, 'clean_up_old_regularboard' );
			function clean_up_old_regularboard() {
			
				global $wpdb;
				
				$rb_table_1 = $wpdb->prefix . 'regular_board_posts';
				$rb_table_2 = $wpdb->prefix . 'regular_board_boards';
				$rb_table_3 = $wpdb->prefix . 'regular_board_users';
				$rb_table_4 = $wpdb->prefix . 'regular_board_bans';
				$rb_table_5 = $wpdb->prefix . 'regular_board_logs';
				$rb_table_6 = $wpdb->prefix . 'regular_board_friends';
				$rb_table_7 = $wpdb->prefix . 'regular_board_messages';

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_1'" ) == $rb_table_1 ){ 

					$wpdb->query( "DROP TABLE $rb_table_1" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_2'" ) == $rb_table_2 ){ 

					$wpdb->query( "DROP TABLE $rb_table_2" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_3'" ) == $rb_table_3 ){ 

					$wpdb->query( "DROP TABLE $rb_table_3" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_4'" ) == $rb_table_4 ){ 

					$wpdb->query( "DROP TABLE $rb_table_4" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_5'" ) == $rb_table_5 ){ 

					$wpdb->query( "DROP TABLE $rb_table_5" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_6'" ) == $rb_table_6 ){ 

					$wpdb->query( "DROP TABLE $rb_table_6" );

				}

				if( $wpdb->get_var( "SHOW TABLES LIKE '$rb_table_6'" ) == $rb_table_7 ){ 

					$wpdb->query( "DROP TABLE $rb_table_7" );

				}

				// (1) Clean up after the original Regular Board
				$original_regular_board_options = array( 
					'regular_board_noindexboards',
					'regular_board_usercreate',
					'regular_board_useboards',
					'regular_board_totaluserallowed',
					'regular_board_accountsper',
					'regular_board_registration',
					'regular_board_protected',
					'regular_board_wipeper',
					'regular_board_enableblog',
					'regular_board_boardbanner',
					'regular_board_bannedimage',
					'regular_board_wipeall',
					'regular_board_wipealldate',
					'regular_board_frontpage',
					'regular_board_footer',
					'regular_board_autourl',
					'regular_board_formatting',
					'regular_board_ascii',
					'regular_board_announcements',
					'regular_board_hideannouncements',
					'regular_board_lazyload',
					'regular_board_robots',
					'regular_board_maxlinks',
					'regular_board_css_url',
					'regular_board_search',
					'regular_board_displayboards',
					'regular_board_displaymenu',
					'regular_board_ids',
					'regular_board_wipedisplay',
					'regular_board_focus',
					'regular_board_archivegate',
					'regular_board_floodgate',
					'regular_board_roll',
					'regular_board_postsper',
					'regular_board_modcode',
					'regular_board_usermodcode',
					'regular_board_enableurl',
					'regular_board_enablerep',
					'regular_board_maxbody',
					'regular_board_maxreplies',
					'regular_board_maxtext',
					'regular_board_boards',
					'regular_board_userflood',
					'regular_board_imgurid',
					'regular_board_dnsbl',
					'regular_board_postingoptions',
					'regular_board_installation'
				);

				foreach( $original_regular_board_options as &$o ) {

					if( get_option( $o ) ) {

						delete_option( $o );

					}

				}

			}

		}


		// (1) Comment this activation hook out if uninstalling the plugin
		if( !function_exists( 'regularboardplugin_install' ) ) {

			register_activation_hook( __FILE__, 'regularboardplugin_install' );			
			function regularboardplugin_install() {

				global $wpdb, $regularboardplugin_posts;			
				$regularboardplugin_posts = $wpdb->prefix . 'regularboardplugin_posts';
				$posts = "CREATE TABLE $regularboardplugin_posts( 
					post_id BIGINT(20) NOT NULL AUTO_INCREMENT ,
					post_parent BIGINT(20) NOT NULL ,
					post_name TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_date_micro TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_email TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_title TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_comment TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_comment_original TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_edited BIGINT(20) NOT NULL ,
					post_moderator_comment TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_type TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_url TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_provider TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_domain TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_board TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_moderator TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_last TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_sticky TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_locked TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_password TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_userid BIGINT(20) NOT NULL ,
					post_report TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_reportcount BIGINT(20) NOT NULL ,
					post_reply_count BIGINT(20) NOT NULL ,
					post_guestip TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_public BIGINT(20) NOT NULL ,
					post_like BIGINT(20) NOT NULL ,
					post_dislike BIGINT(20) NOT NULL ,
					post_approval_rating BIGINT(20) NOT NULL ,
					post_delete_this BIGINT(20) NOT NULL ,
					post_banned BIGINT(20) NOT NULL ,
					PRIMARY KEY( post_id )
				);";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $posts );
				update_option( 'regularboardplugin_installed', 1 );

			}

		}

	}





/**
 *
 * Plugin Base
 *
 */
define( 'regularboardplugin_plugin', true );
require_once( ABSPATH . 'wp-includes/pluggable.php' );
	
// (1) Determine if user is logged into WordPress
// (2) Determine if user is an admin
$current_user_is_an_admin = $is_current_logged_in = $is_current_an_admin = 0;

if( is_user_logged_in() ) {

	$is_current_logged_in = wp_get_current_user();
	$is_current_an_admin  = $is_current_logged_in->user_login;
	
	if( current_user_can( 'manage_options' ) ) {

		$current_user_is_an_admin = 1;

	}

}
	
	
	/****************************************************
		CSS
	****************************************************/
	if( !function_exists( 'regularboardplugin_board_css' ) ) {
		function regularboardplugin_board_css() {
			global $post;
			if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'regular board') ) {
				echo '
				<style type="text/css" media="screen">
				.regularboardplugin_container *,.regularboardplugin_container *:before,.regularboardplugin_container *:after{-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;}
				.regularboardplugin_container{border-radius:2px;margin:0 auto;display:block;border-radius:2px;overflow:hidden;}
				.regularboardplugin_container .pages,.regularboardplugin_container .filter_by,.regularboardplugin_container .admin_actions{width:100%;display:block;margin:0 auto;color:rgba(0,0,0,.5);padding:5px;font-size:65%;border-top:1px solid rgba(0,0,0,.1);}
				.regularboardplugin_container .pages a{padding:5px;background-color:rgba(0,0,0,.5);color:#e5f5ff;}
				.regularboardplugin_container .name a{color:#fff;}
				.regularboardplugin_container .filter_by a:hover, .regularboardplugin_container .admin_actions a:hover{text-decoration:none;}
				.regularboardplugin_container .filter_by a, .regularboardplugin_container .admin_actions a{color:rgba(44,129,183,.8);text-decoration:none;border:none;padding:2px;}
				.regularboardplugin_container .left{float:left;}
				.regularboardplugin_container .right{float:right;}
				.regularboardplugin_container section{padding:5px;display:block;margin:0 auto 5px;width:100%;border-radius:2px;border:1px solid rgba(0,0,0,.1);border-bottom:1px solid rgba(0,0,0,.1);overflow:hidden;}
				.regularboardplugin_container section a{color:#2d709a;}
				.regularboardplugin_container small.domain{font-size:55%;padding:2px;border-radius:2px;border:1px solid rgba(0,0,0,.1);color:rgba(255,255,255,.8);cursor:default;}
				.regularboardplugin_container p{padding:5px;margin:10px;}	
				.regularboardplugin_container .mediaEmbed{margin:-4px auto;display:block;position:relative;z-index:3;width:100%;}
				.regularboardplugin_container .mediaEmbed img{-webkit-box-shadow: 0 0 5px 2px rgba(0,0,0,.1);box-shadow: 0 0 5px 2px rgba(0,0,0,.1);width:100%;}
				.regularboardplugin_container img{max-width:100%;margin:5px auto;}
				.regularboardplugin_container .mod_comment{color:red;padding:5px 0;border-top:1px solid rgba(0,0,0,.1);}
				.regularboardplugin_container .banned{font-size:75%;padding:2px;border:1px solid rgba(0,0,0,.1);background-color:rgba(0,0,0,.8);color:rgba(255,255,255,.9);}
				.regularboardplugin_container .submission_links{width:100%;clear:both;display:block;padding:5px;border-top:1px solid rgba(0,0,0,.1);}
				.regularboardplugin_container .submission_links a{padding:5px;border-radius:2px;border:1px solid rgba(0,0,0,.1);text-decoration:none;margin:2px;width:32%;float:left;display:block;font-size:75%;text-align:center;}
				.regularboardplugin_container .submission_links a:hover{-webkit-box-shadow: 0 0 5px 1px rgba(0,0,0,.1);box-shadow: 0 0 5px 1px rgba(0,0,0,.1);}
				.regularboardplugin_container small{font-size:75%;}
				.regularboardplugin_container date,.regularboardplugin_container small.user{font-size:60%;padding:5px;border-radius:2px;border:1px solid rgba(0,0,0,.1);background-color:rgba(0,0,0,.5);color:rgba(255,255,255,.8);cursor:default;}
				.regularboardplugin_container small.name,.regularboardplugin_container small.score{font-size:60%;padding:5px;border-radius:2px;border:1px solid rgba(0,0,0,.1);background-color:green;color:rgba(255,255,255,.9);cursor:default;}
				.regularboardplugin_container form input[type="checkbox"]{float:left;clear:none;width:25px;margin:20px 0 -10px 0;}
				.regularboardplugin_container form div.clear{clear:both;display:block;margin:5px auto;width:100%;}
				.regularboardplugin_container .pages:after,.regularboardplugin_container .filter_by:after,.regularboardplugin_container .admin_actions:after,.regularboardplugin_container .submission_links:after,.regularboardplugin_container form div.clear:after{clear:both;content:"";display:table;}
				.regularboardplugin_container form div.small{width:25%;float:right;margin:2px;}
				.regularboardplugin_container form div.small_input{width:28%;float:left;margin:1px;clear:none;}
				.regularboardplugin_container form.create div.float{float:left;margin:1px;width:49%;}
				.regularboardplugin_container form.create label,.regularboardplugin_container form input{padding:5px;width:100%;margin:5px auto 0;}
				.regularboardplugin_container form.create label{font-size:75%;clear:both;display:block;}
				.regularboardplugin_container form.create input{font-size:105%;}
				.regularboardplugin_container input[type="checkbox"].confirm{clear:both;margin:5px auto;display:block;}
				.regularboardplugin_container form input[type="submit"]{margin:0;clear:both;display:block;}
				.regularboardplugin_container form.create input#post_url,.regularboardplugin_container form.create input[type="submit"]{width:99%;}
				.regularboardplugin_container form.create textarea{height:150px;width:99%;}
				.regularboardplugin_container form{border-radius:2px;padding:5px;margin:0 auto;width:100%;}
				.regularboardplugin_container .information{padding:5px;text-align:center;background-color:rgba(0,0,0,.7);color:rgba(255,255,255,.8);margin:0 auto;width:100%;display:block;cursor:default;border-top:1px solid rgba(255,255,255,.1);}
				.regularboardplugin_container .parent{border:0!important;}
				.regularboardplugin_container .hidden_element{display:none;visibility:Hidden;}
				</style>
				';
			}
		}
	}
	
	
	
	
	/****************************************************
		Configuration (defaults)
	****************************************************/
	include ( plugin_dir_path(__FILE__) . '/config.php' );
	if ( file_exists ( ABSPATH . '/regular_board_config.php' ) ) {
		include ( ABSPATH . '/regular_board_config.php' );
	}
	
	$got = $get = $location = '';
	if( isset( $_GET['a'] ) || isset( $_GET['b'] ) || isset( $_GET['t'] ) || isset( $_GET['u'] ) || isset( $_GET['n'] ) ) {
		if( isset( $_GET['a'] ) ) {
			$get              = 'a';
			$got = $_GET['a'] = sanitize_text_field( $_GET['a'] );
		}
		if( isset( $_GET['u'] ) ) {
			$get              = 'u';
			$got = $_GET['u'] = sanitize_text_field( $_GET['u'] );
		}		
		if( isset( $_GET['b'] ) ) {
			$get              = 'b';
			$got = $_GET['b'] = sanitize_text_field( $_GET['b'] );
		}
		if( isset( $_GET['t'] ) ) {
			$get              = 't';
			$got = $_GET['t'] = sanitize_text_field( $_GET['t'] );
		}
		if( isset( $_GET['n'] ) ) {
			$page_number = $_GET['n'] = intval( $_GET['n'] );
			if( $got ) {
				$location    = '?' . $get . '=' . $got . '&n=' . $page_number;
			} else {
				$location    = 'n=' . $page_number;
			}
		}
	}
	
	
	
	
	
	/****************************************************
		Status messages
	****************************************************/
	$error_no_posts        = 'No posts to display.';
	$new_content           = '';
	if( isset( $_GET['t'] ) ) {
		$posting_mode      = '<span class="information">Posting Mode: Reply (Click to expand)</span>';
	} else {
		if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' ) {
			$new_content = 'Link';
		} elseif ( isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' ) {
			$new_content = 'Self Post';
		}
		$posting_mode      = '<span class="information">Posting Mode: New ' . $new_content . '</span>';
	}
	$no_url_error     = '<span class="information">Link posts require a URL.</span>';
	$no_comment_error = '<span class="information">You must provide a comment.</span>';
	$no_title_error   = '<span class="information">Please provide a subject.</span>';





	/****************************************************
		Functions
	/***************************************************/
	// (1) Convert long numbers to shorter versions
	// http://stackoverflow.com/questions/10599933/convert-long-number-into-abbreviated-string-in-javascript-with-a-special-shortn
	if( !function_exists( 'regularboardplugin_nicenumbers' ) ) {
		function regularboardplugin_nicenumbers($n) {
			$n = (0+str_replace(",","",$n));
			if(!is_numeric($n)) return false;
			if($n>1000000000000) return round(($n/1000000000000),1).'t';
			else if($n>1000000000) return round(($n/1000000000),1).'b';
			else if($n>1000000) return round(($n/1000000),1).'m';
			else if($n>1000) return round(($n/1000),1).'k';
			return number_format($n);
		}
	}
	
	// (1) Generate random password for blank password posts
	$seedlet = str_split ( 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789!@#$%^&*()' );
	shuffle ( $seedlet );
	$random_password = '';
	$new_seed        = '';
	foreach (array_rand($seedlet, 10) as $p) $random_password .= $seedlet[ $p ];
	foreach (array_rand($seedlet, 60) as $p) $new_seed .= $seedlet[ $p ];
	
	// (1) Calculate a percentage
	if ( !function_exists ( 'regularboardplugin_percent' ) ) {
		function regularboardplugin_percent($num_amount, $num_total) {
			$count1 = $num_amount / $num_total;
			$count2 = $count1 * 100;
			$count = number_format($count2, 0);
			return $count;
		}
	}	
	
	// (1) Calculate time between (date) and (date)
	if ( !function_exists ( 'regularboardplugin_timesince' ) ) {
		function regularboardplugin_timesince ( $date, $granularity=2 ) {
			$retval = '';
			$date = strtotime ( $date );
			$difference = time() - $date;
			$periods = array( ' decades' => 315360000, 
				' years' => 31536000, 
				' months' => 2628000, 
				' weeks' => 604800,  
				' days' => 86400, 
				' hours' => 3600, 
				' minutes' => 60, 
				' seconds' => 1 );
			foreach ( $periods as $key => $value ) {
				if ( $difference >= $value ) {
					$time = floor ( $difference/$value );
					$difference %= $value;
					$retval .= ( $retval ? ' ' : '' ) . $time . '';
					$retval .= ( ( $time > 1 ) ? $key : $key );
					$granularity--;
				}
				if ( $granularity == '0' ) { break; }
			}
			return $retval . ' ago';
		}	
	}	
	
	// (1) Fix WordPress canonical URLs to work with thread URLs
	if( !function_exists( 'regularboardplugin_fix_canonical' ) ) {
		function regularboardplugin_fix_canonical() {
			global $wp, $post, $content;
			$canonical_url = sanitize_text_field( htmlentities( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) );
			echo "\n";
			echo '<link rel="canonical" href="' . $canonical_url . '"/>';
			echo "\n";
		}
	}
	
	// (1) Get the domain of a requested URL for display purposes
	if( !function_exists( 'regularboardplugin_get_domain' ) ) {
		function regularboardplugin_get_domain( $url ) {
			$pieces = parse_url( $url );
			$domain = isset( $pieces['host'] ) ? $pieces['host'] : '';
			if( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) ) {
				return $regs['domain'];
			}
			return false;			
		}
	}
		
	// (1) Strip http: from URLs to force compliance with https:
	if( !function_exists( 'regularboardplugin_protocol_relative_url' ) ) {	
		function regularboardplugin_protocol_relative_url( $url ) {		
			return str_replace( 'http:', '', esc_url( $url ) );
		}		
	}
	
	// (1) Automatically apply quotes to a group of items for SQL use
	if( !function_exists( 'regularboardplugin_apply_quotes' ) ) {	
		function regularboardplugin_apply_quotes( $items ) {		
			return "'" . esc_sql( $items ) . "'";			
		}		
	}

	// (1) Format comment with the following
	//  - **bold**, *italic*, ***bold+italic***, ~~strike~~, ---- (horizontal divide),
	//  - `code`, [s]spoiler[/s]
	if( !function_exists( 'regularboardplugin_comment_format' ) ) {
		function regularboardplugin_comment_format( $comment ) {
			$input = array(
				'/\{(.*?)}/is',
				'/\:\:(.*?) /is',
				'/\*\*\*(.*?)\*\*\*/is',
				'/\*\*(.*?)\*\*/is',
				'/\*(.*?)\*/is',
				'/\~\~(.*?)\~\~/is',
				'/\{\{(.*?)\}\}/is',
				'/-\-\-\-/is',
				'/\|/is',
				'/\`(.*?)\`/is',
				'/\[s](.*?)\[\/s]/is',
				'/\[(.*?)\]\((.*?)\)/is',
			);
			$output = array(
				'<span class="quotes"> &#62; $1 </span><br />',
				'<a href="?t=$1"> &#62;&#62; $1 </a><br />',
				'<strong><em>$1</em></strong>',
				'<strong>$1</strong>',
				'<em>$1</em>',
				'<u class="strike">$1</u>',
				'<blockquote>$1</blockquote>',
				'<hr />',
				'<br />',
				'<code>$1</code>',
				'<span class="spoiler">$1</span>',
				'<a href="$2">$1</a>',
			);
			$rtrn = preg_replace( $input, $output, $comment );
			return wpautop( $rtrn );		
		}
	}





	/****************************************************
		Classes
	****************************************************/

	/****************************************************
	Version: 0.2 
	Website: http://abhiomkar.in
	Author: Abhinay Omkar abhiomkar@gmail.com
	Title: SURBL Client 
	Description: PHP Client Library for the surbl.org blacklists
	Change Log:
	v0.2
	----
	- using tlds list of 2 and 3 levels provided by surbl.org
	- lot of improvements and bug fixes
	v0.1
	----
	This is ported from surblclient of Python 
	Licensed under The MIT License
	Redistributions of files must retain the above copyright notice.
	****************************************************/
	# SURBL SPAM Check - return True if it is Blacklisted at SURBL list
	class Blacklist {

		public $url = "";
		public $spam_check = False;
		
		function __construct($url="") {
			$this->url = $url;
			
			$url_exploded = parse_url($this->url);
			$domain = $url_exploded['host'];

			$this->spam_check = $this->lookup($domain);
		}
		
		function _get_base_domain($domain) {
			# Remove User Info
			if (strpos($domain, "@")){
				$domain = substr($domain, strpos($domain, "@") + 1, strlen($domain));
			}
			
			# Remove Port    
			if (strpos($domain, ":")){
				$domain = substr($domain, strpos($domain, ":") + 1, strlen($domain));
			}
		
			# Choose the right "depth"...
			if ($this->_three_level_tlds($domain)) {
				# For any domain on the three level list, check it at the fourth level.
				$n = 4;
			}
			else if ($this->_two_level_tlds($domain)){
				# For any domain on the two level list, check it at the third level.
				$n = 3;
			}
			else {
				# For any other domain, check it at the second level.
				$n = 2;
			}
			
			return implode('.', array_slice(explode('.', $domain), -$n));
		}
		
		function lookup($domain) {
			$_flags = array(
				2 => "sc",
				4 => "ws",
				8 => "ph",
				16 => "ob",
				32 => "ab",
				64 => "jp"
			  );
		
			$domain = $this->_get_base_domain($domain);
			
			$lookup = "$domain.multi.surbl.org";
			
			# returns the same host name if it couldn't resolve, otherwise, returns the IP Address
			$ip = gethostbyname($lookup);
			# Rudimentary way of validating IP Address, but this works.
			if (preg_match("/\d+\.\d+\.\d+\.\d+/", $ip)) {
				$last_octal_arr = array_slice(explode('.', $ip), -1);
				$last_octal = $last_octal_arr[0];
				$lists = array();
				foreach ($_flags as $key => $value) {
					if ($last_octal & $key) {
						$lists[] = $value;
					}
				}
				# SPAM SPAM! It's Blacklisted!
				return True;
			}
			else {
				# SAFE!
				return False;
			}
		}
		
		
		function _three_level_tlds($domain) {
			$three_level_tlds_data = file( plugin_dir_path( __FILE__ ) . 'three-level-tlds.data' );

			foreach($three_level_tlds_data as $tld) {
				$tld = trim($tld);
				if($this->_ends_with($domain, $tld)) {
					return true;
				}
			}

			return false;
		}

		function _two_level_tlds($domain) {
			$two_level_tlds_data = file( plugin_dir_path( __FILE__ ) . 'two-level-tlds.data' );

			foreach($two_level_tlds_data as $tld) {
				$tld = trim($tld);
				if($this->_ends_with($domain, $tld)) {
					return true;
				}
			}

			return false;
		}

		# Credits to http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions/834355#834355
		function _ends_with($haystack, $needle) {
			$length = strlen($needle);
			$start =  $length *-1; //negative
			return (substr($haystack, $start, $length) === $needle);
		}
		
	}

	# USAGE
	# - Download 'two-level-tlds.data' & 'three-level-tlds.data' files to the same directory
	# - the argument to Blacklist class should be a valid URL
	/*
	$url_c = new Blacklist("http://test.surbl.org");

	if($url_c->spam_check) {
		echo "SPAM SPAM!";
	}
	else {
		echo "SAFE!";
	}
	*/





	/****************************************************
		Continue Plugin Necessities 
	****************************************************/	
	remove_action( 'wp_head', 'rel_canonical' );
	add_action( 'wp_head', 'regularboardplugin_fix_canonical' );
	add_action( 'wp_head', 'regularboardplugin_board_css' );
	add_shortcode( 'regular board', 'regularboardplugin_shortcode' );
	add_filter( 'the_content', 'do_shortcode', 'regularboardplugin_shortcode' );





	// (1) Determine if connecting IP is valid (v4/v6)
	// (2) Determine if connecting (valid) IP is blacklisted on the DNSBL
	if( !function_exists( 'regularboardplugin_check_dnsbl' ) ) {
	
		if( inet_pton( $_SERVER['REMOTE_ADDR'] ) === false ) {
			$ipaddress = false;
		} else {
			$ipaddress = esc_attr( $_SERVER['REMOTE_ADDR'] );
			
			function regularboardplugin_check_dnsbl( $ipaddress ) {
				$dnsbl_lookup = array( 
					'dnsbl-1.uceprotect.net',
					'dnsbl-2.uceprotect.net',
					'dnsbl-3.uceprotect.net',
					'dnsbl.sorbs.net',
					'zen.spamhaus.org'
				);
				if( $ipaddress ) {
					$reverseip = implode( '.', array_reverse( explode( '.', $ipaddress ) ) );
					foreach( $dnsbl_lookup as $host ) {
						if( checkdnsrr( $reverseip . "." . $host . ".", "A" ) ) {
							$listed.= $reverseip . "." . $host;
						}
					}
				}
				if( $listed ) {
					$ipaddress === false;
				}
				
				if( $fuck_the_fcc ) {
					// IP Addresses obtained from:
					// https://gist.github.com/kyledrake/e6046644115f185f7af0
					$check_this_ip = ip2long( $ipaddress );
					$range_1_start = ip2long( "192.133.125.0" );
					$range_1_end   = ip2long( "192.133.125.24" );
					$range_2_start = ip2long( "165.135.0.0" );
					$range_2_end   = ip2long( "165.135.0.24" );
					$range_3_start = ip2long( "4.21.126.0" );
					$range_3_end   = ip2long( "4.21.126.24" );
					$range_4_start = ip2long( "65.125.25.64" );
					$range_4_end   = ip2long( "65.125.25.26" );
					$range_5_start = ip2long( "208.23.64.0" );
					$range_5_end   = ip2long( "208.23.64.25" );
					if ( 
						$check_this_ip >= $range_1_start && $check_this_ip <= $range_1_end || 
						$check_this_ip >= $range_2_start && $check_this_ip <= $range_2_end || 
						$check_this_ip >= $range_3_start && $check_this_ip <= $range_3_end || 
						$check_this_ip >= $range_4_start && $check_this_ip <= $range_4_end || 
						$check_this_ip >= $range_5_start && $check_this_ip <= $range_5_end 
					) {
						$ipaddress === false;
					}
				}
				
			}
			
		}
		
	} else {
	
		$ipaddress = true;
		
	}
	
	// (1) The connecting IP address has been found to be both:
	// (1) :: valid
	// (1) :: not listed on the DNSBL servers provided
	function regularboardplugin_shortcode() {
		global $postsperthread, $postsperpage, $page_number, $location, $get, $got, $self_domain, $expires, $allowed_image_types, $page, $wpdb, $wp, $post, $content, $ipaddress, $regularboardplugin_posts_select, $error_no_posts, $posting_mode, $no_url_error, $current_user_is_an_admin, $is_current_logged_in, $is_current_an_admin, $admin_code, $guest_code, $user_code, $supported_providers;
		$regularboardplugin_posts = $wpdb->prefix . 'regularboardplugin_posts';
		echo '<div class="regularboardplugin_container">';
		if( $ipaddress !== false ) {
			
			if( isset( $_GET['t'] ) ) {
				if( is_numeric( $_GET['t'] ) ) {
					$page = regularboardplugin_protocol_relative_url ( get_permalink() .  '?t=' . intval( $_GET['t'] ) );
				}
			} elseif( isset( $_GET['b'] ) ) {
				$page = regularboardplugin_protocol_relative_url ( get_permalink() .  '?t=' . sanitize_text_field( $_GET['b'] ) );
			} else {
				$page = regularboardplugin_protocol_relative_url ( get_permalink() );
			}
			
			
			
			echo '<div class="filter_by">';
				if( $current_user_is_an_admin ) {
					echo '<span class="right"><a href="?a=bans">Bans</a></span>';
				}

				$check_all_posts = $wpdb->get_results (
					"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts"
				);
				$parents = $allposts = $texts = $images = $embeds = $links = 0;
				foreach( $check_all_posts as $cap ) {
					if( $cap->post_type == 'text' ) {
						$texts++;
					}
					if( $cap->post_type == 'link' ) {
						$links++;
					}
					$allposts++;
					if( $cap->post_parent == 0 ) {
						$parents++;
					}
				}
				
				echo '<span class="left">
					[<a href="' . get_permalink() . '">All - ' . regularboardplugin_nicenumbers( $parents ) . '</a>] 
					[<a href="?a=allposts">All posts - ' . regularboardplugin_nicenumbers ( $allposts ) . '</a>] '; 
					if( $texts ) {
						$texts = regularboardplugin_nicenumbers( $texts );
						echo '[<a href="?a=texts">Text - ' . $texts . '</a>] ';
					}
					if( $links ) {
						$links = regularboardplugin_nicenumbers( $links );
						echo '[<a href="?a=links">Links - ' . $links . '</a>] ';
					}
				echo '</span>
			</div>';
			
			
			// (1) Never store the user IP address in plaintext.
			$ipaddress = wp_hash( $ipaddress );
			
	
			if( isset( $_GET['a'] ) && $_GET['a'] == 'bans') {
				if( $current_user_is_an_admin ) {
					$check_for_a_bans = $wpdb->get_results (
						"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_banned = 1 ORDER BY post_id DESC"
					);
					echo '<form method="post" name="post_form" class="create" action="' . $page . '?a=bans">';
					wp_nonce_field( 'post_form' );
					if( count( $check_for_a_bans ) ) {
						foreach( $check_for_a_bans as $cfabs ) {
							$post_id                  = $cfabs->post_id;
							$post_parent              = $cfabs->post_parent;
							$post_name                = $cfabs->post_name;
							$post_date                = $cfabs->post_date;
							$post_date_micro          = $cfabs->post_date_micro;
							$post_email               = $cfabs->post_email;
							$post_title               = str_replace( '\\', '', $cfabs->post_title);
							$post_comment             = str_replace( '\\', '', $cfabs->post_comment);
							$post_comment_original    = $cfabs->post_comment_original;
							$post_edited              = $cfabs->post_edited;
							$post_moderator_comment   = $cfabs->post_moderator_comment;
							$post_type                = $cfabs->post_type;
							$post_url                 = $cfabs->post_url;
							$post_provider            = $cfabs->post_provider;
							$post_domain              = $cfabs->post_domain;										
							$post_board               = $cfabs->post_board;
							$post_moderator           = $cfabs->post_moderator;
							$post_last                = $cfabs->post_last;
							$post_sticky              = $cfabs->post_sticky;
							$post_locked              = $cfabs->post_locked;
							$post_password            = $cfabs->post_password;
							$post_userid              = $cfabs->post_userid;
							$post_report              = $cfabs->post_report;
							$post_reportcount         = $cfabs->post_reportcount;
							$post_reply_count         = $cfabs->post_reply_count;
							$post_guestip             = $cfabs->post_guestip;
							$post_public              = $cfabs->post_public;
							$post_like                = $cfabs->post_like;
							$post_dislike             = $cfabs->post_dislike;
							$post_approval_rating     = $cfabs->post_approval_rating;
							$post_delete_this         = $cfabs->post_delete_this;
							$post_banned              = $cfabs->post_banned;
							
							if( !$post_name ) {
								$post_name            = 'anonymous';
							}
							if( !$post_title ) {
								$post_title           = 'no title';
							}										
							
							echo '<section>';
							echo '<input type="checkbox" name="unban[]" value="' . $post_id . '" />';
							echo '<p>';
							if( $post_title ) {
								echo '<em>';
								if( $post_url ) {
									echo '<a href="' . $post_url . '">' . $post_title . '</a>';
								} else {										
									echo $post_title;
								}
								echo '</em>';
							}
							if( $post_url ) {
								echo ' <small class="domain"><a href="//' . $post_domain . '">' . $post_domain . '</a></small>';
							}										
							
							echo ' &mdash; <small>no. ' . $post_id . '</small><br />';
							echo '<small class="name"><a href="?u=' . $post_name . '">' . $post_name . '</a></small>';
							
							if( $current_user_is_an_admin ) {
								echo ' <small class="name">' . $post_guestip . '</small>';
							}

							if ( $post_moderator == 1 ) {
								echo $admin_code;
							} elseif ( $post_moderator == 2 ) {
								echo $user_code;
							} else {
								echo $guest_code;
							}
							
							echo ' <date>' . regularboardplugin_timesince( $post_date ) . '</date>';

							echo '</p>';
							
							if( $post_url ) {
								echo '</section><div class="mediaEmbed">';
									echo wp_oembed_get ( $post_url );
								echo '</div><section>';
							}								
							if( $post_comment ) {
								echo wpautop( regularboardplugin_comment_format( $post_comment ) );
							}

							if( $post_moderator_comment || $post_banned ) {
								echo '<p class="mod_comment">';
								if( $post_moderator_comment ) {
									echo $post_moderator_comment . ' ';
								}
								if( $post_banned ) {
									echo '<em class="banned">user was banned for this post.</em>';
								}
								echo '</p>';
							}
							
							echo '</section>';										
							$post_id = $post_parent = $post_name = $post_date_micro = $post_date = $post_email = $post_title = $post_comment = $post_comment_original = $post_edited = $post_moderator_comment = $post_type = $post_url = $post_board = $post_moderator = $post_last = $post_sticky = $post_locked = $post_password = $post_userid = $post_report = $post_reportcount = $post_reply_count = $post_guestip = $post_like = $post_dislike = $post_approval_rating = $post_public = $post_delete_this = '';
						}
						echo '<input type="submit" value="Unban these users" name="unban_user" id="unban_user" />';
						echo '</form>';

						if( isset( $_POST['unban_user'] ) ) {
							if(!empty($_POST['unban'])) {
								foreach( $_POST['unban'] as $check ) {
									$set_ban_date = date( 'Y-m-d H:i:s' );
									$wpdb->query(
										"UPDATE $regularboardplugin_posts SET post_banned = 0 WHERE post_id = $check"
									);
									$wpdb->query(									
										"UPDATE $regularboardplugin_posts SET post_public = 0 WHERE post_id = $check"
									);
									$wpdb->query(
										"UPDATE $regularboardplugin_posts SET post_moderator_comment = '' WHERE post_id = $check"
									);
								}
							}
						}
					} else {
						echo '<section>There are no bans to moderate.</section>';
					}
				} else {
					echo '<section>You do not have permission to view this.</section>';
				}
			} else {
			
				// (1) Database results
				$reply_mode = 0;
				$regularboardplugin_parent_by = $regularboardplugin_posts_by = '';
				if( isset( $_GET['t'] ) ) {
					if( is_numeric( $_GET['t'] ) ) {
						$reply_mode = 1;
						$_GET['t'] = intval( $_GET['t'] );
						$regularboardplugin_posts_by = ' WHERE post_id = ' . $_GET['t'] . ' ';
						$regularboardplugin_parent_by = ' WHERE post_parent = ' . $_GET['t'] . ' ';
						$dont_show = 0;
					}
				} elseif( isset( $_GET['b'] ) ) {
					$reply_mode = 0;
					$regularboardplugin_posts_by = ' WHERE post_board = ' . $_GET['b'] . ' AND post_parent = 0 ';
					$dont_show = 0;
				} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'allposts' ) {
					$dont_show = 0;
					$regularboardplugin_posts_by = " WHERE post_id > 0 ";				
				} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'texts' ) {
					$dont_show = 0;
					$regularboardplugin_posts_by = " WHERE post_type = 'text' AND post_parent = 0 ";
				} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'images' ) {
					$dont_show = 0;
					$regularboardplugin_posts_by = " WHERE post_type = 'image' AND post_parent = 0 ";
				} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'embeds' ) {
					$dont_show = 0;
					$regularboardplugin_posts_by = " WHERE post_type = 'embed' AND post_parent = 0 ";
				} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'links' ) {
					$dont_show = 0;
					$regularboardplugin_posts_by = " WHERE post_type = 'link' AND post_parent = 0 ";
				} elseif( isset( $_GET['u'] ) ) {
					$dont_show = 0;
					if( $_GET['u'] == strtolower( 'anonymous' ) ) {
						$regularboardplugin_posts_by = " WHERE post_name = '' ";
					} else {
						$regularboardplugin_posts_by = " WHERE post_name = '" . $_GET['u'] . "' ";
					}
				} else {
					$dont_show = 0;
					$reply_mode = 0;
					$regularboardplugin_posts_by = ' WHERE post_parent = 0 AND post_public = 0 ';
				}
				
				if( $regularboardplugin_parent_by ) {
					$postsperpage = $postsperthread;
					$totalpages = $wpdb->get_var (
						"SELECT COUNT(*) FROM $regularboardplugin_posts $regularboardplugin_parent_by"
					);
					if( $totalpages > 0 ) {
						if( isset( $_GET['n'] ) ) {
							$page_no = intval( $_GET['n'] );
						} else {
							$page_no = 1;
						}
					} else { 
						$page_no = 1;
					}
					if( $page_no ) {
						$start = ( $page_no - 1 ) * $postsperpage;
					} else {
						$start = 0;
					}
					
					
				} else {
				
					$totalpages = $wpdb->get_var (
						"SELECT COUNT(*) FROM $regularboardplugin_posts $regularboardplugin_posts_by"
					);
					if( $totalpages > 0 ) {
						if( isset( $_GET['n'] ) ) {
							$page_no = intval( $_GET['n'] );
						} else {
							$page_no = 1;
						}
					} else {
						$page_no = 1;
					}
					
					if( $page_no ) {
						$start = ( $page_no - 1 ) * $postsperpage;
					} else {
						$start = 0;
					}					
				}
				

				
				$paging = $totalpages / $postsperpage;
				
				$results    = $wpdb->get_results (
					"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts $regularboardplugin_posts_by ORDER BY post_last DESC LIMIT $start, $postsperpage"
				);
				
				$replies = '';
				if( $reply_mode ) {
					$replies = $wpdb->get_results (
						"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_parent = " . $_GET['t'] . " ORDER BY post_last ASC LIMIT $start, $postsperpage"
					);
				}
				
				function regularboardplugin_do_post() {
					global $random_password, $self_domain, $expires, $wpdb, $wp, $post, $ipaddress, $allowed_image_types, $regularboardplugin_posts_select, $no_url_error, $no_comment_error, $no_title_error, $current_user_is_an_admin, $is_current_logged_in, $is_current_an_admin;
					$regularboardplugin_posts = $wpdb->prefix . 'regularboardplugin_posts';
					if( isset( $_GET['t'] ) ) {
						if( is_numeric( $_GET['t'] ) ) {
							$reply_mode = 1;
						}
					} elseif( isset( $_GET['b'] ) ) {
						$reply_mode = 0;
					} else {
						$reply_mode = 0;
					}				
					
					$date_time = date( 'Y-m-d H:i:s' );
					$check_last_reply  = strtotime("-0 seconds");
					$check_last_thread = strtotime("-0 seconds");
					$post_date_micro = $check_current = strtotime($date_time);
					
					if( $reply_mode ) {
						$check_last_post = $wpdb->get_results (
							"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_date_micro >= $check_last_reply AND post_guestip = '$ipaddress' AND post_parent != 0 ORDER BY post_date_micro LIMIT 1"
						);
					} else {
						$check_last_post = $wpdb->get_results (
							"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_date_micro >= $check_last_thread AND post_guestip = '$ipaddress' AND post_parent = 0 ORDER BY post_date_micro LIMIT 1"
						);					
					}
					
					if( count( $check_last_post ) ) {
						foreach( $check_last_post as $clp ) {
							if( $reply_mode ) {
								$try_again_in = $clp->post_date_micro - $check_last_reply;
								echo '<span class="information">You\'re doing that too much. Please try again in ' . $try_again_in . ' seconds.</span>';
							} else {
								$try_again_in = $clp->post_date_micro - $check_last_thread;
								echo '<span class="information">You\'re doing that too much. Please try again in ' . $try_again_in . ' seconds.</span>';
							}
						}						
					} else {
						$check_for_a_ban = $wpdb->get_results (
							"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_banned = 1 AND post_guestip = '$ipaddress' LIMIT 1"
						);
						
						if( count( $check_for_a_ban ) ) {

							foreach( $check_for_a_ban as $cfab ) {
								$post_id                  = $cfab->post_id;
								$post_parent              = $cfab->post_parent;
								$post_name                = $cfab->post_name;
								$post_date                = $cfab->post_date;
								$post_date_micro          = $cfab->post_date_micro;
								$post_email               = $cfab->post_email;
								$post_title               = str_replace( '\\', '', $cfab->post_title);
								$post_comment             = str_replace( '\\', '', $cfab->post_comment);
								$post_comment_original    = $cfab->post_comment_original;
								$post_edited              = $cfab->post_edited;
								$post_moderator_comment   = $cfab->post_moderator_comment;
								$post_type                = $cfab->post_type;
								$post_url                 = $cfab->post_url;
								$post_provider            = $cfab->post_provider;
								$post_domain              = $cfab->post_domain;										
								$post_board               = $cfab->post_board;
								$post_moderator           = $cfab->post_moderator;
								$post_last                = $cfab->post_last;
								$post_sticky              = $cfab->post_sticky;
								$post_locked              = $cfab->post_locked;
								$post_password            = $cfab->post_password;
								$post_userid              = $cfab->post_userid;
								$post_report              = $cfab->post_report;
								$post_reportcount         = $cfab->post_reportcount;
								$post_reply_count         = $cfab->post_reply_count;
								$post_guestip             = $cfab->post_guestip;
								$post_public              = $cfab->post_public;
								$post_like                = $cfab->post_like;
								$post_dislike             = $cfab->post_dislike;
								$post_approval_rating     = $cfab->post_approval_rating;
								$post_delete_this         = $cfab->post_delete_this;
								$post_banned              = $cfab->post_banned;
								
								echo '<section><h1><strong>You may not post.</strong></h1>';
								echo 'Your ban was filed on ' . $post_last . '. You were banned for the following post:</section>';
								
								if( !$post_name ) {
									$post_name            = 'anonymous';
								}
								if( !$post_title ) {
									$post_title           = 'no title';
								}										
								
								echo '<section>';
								echo '<p>';
								if( $post_title ) {
									echo '<em>';
									if( $post_url ) {
										echo '<a href="' . $post_url . '">' . $post_title . '</a>';
									} else {										
										echo $post_title;
									}
									echo '</em>';
								}
								if( $post_url ) {
									echo ' <small class="domain"><a href="//' . $post_domain . '">' . $post_domain . '</a></small>';
								}										
								
								echo ' &mdash; <small>no. ' . $post_id . '</small><br />';
								echo '<small class="name"><a href="?u=' . $post_name . '">' . $post_name . '</a></small>';
								
								if( $current_user_is_an_admin ) {
									echo ' <small class="name">' . $post_guestip . '</small>';
								}
								

								echo ' <date>' . regularboardplugin_timesince( $post_date ) . '</date>';

								echo '</p>';
								
								if( $post_url ) {
									echo '</section><div class="mediaEmbed">';
										echo wp_oembed_get ( $post_url );
									echo '</div><section>';
								}								
								if( $post_comment ) {
									echo wpautop( regularboardplugin_comment_format( $post_comment ) );
								}

								if( $post_moderator_comment || $post_banned ) {
									echo '<p class="mod_comment">';
									if( $post_moderator_comment ) {
										echo $post_moderator_comment . ' ';
									}
									if( $post_banned ) {
										echo '<em class="banned">user was banned for this post.</em>';
									}
									echo '</p>';
								}
								
								echo '</section>';										
								
								$post_id = $post_parent = $post_name = $post_date_micro = $post_date = $post_email = $post_title = $post_comment = $post_comment_original = $post_edited = $post_moderator_comment = $post_type = $post_url = $post_board = $post_moderator = $post_last = $post_sticky = $post_locked = $post_password = $post_userid = $post_report = $post_reportcount = $post_reply_count = $post_guestip = $post_public = $post_like = $post_dislike = $post_approval_rating = $post_delete_this = '';
							}
						} else { 
							if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode ) {

								$regularboardplugin_posts = $wpdb->prefix . 'regularboardplugin_posts';
								$blank                     = '';
								$post_provider = $post_domain = $post_id = $post_parent = $post_name = $post_date = $post_email = $post_title = $post_comment = $post_comment_original = $post_edited = $post_moderator_comment = $post_type = $post_url = $post_board = $post_moderator = $post_last = $post_sticky = $post_locked = $post_password = $post_userid = $post_report = $post_reportcount = $post_reply_count = $post_guestip = $post_public = $post_like = $post_dislike = $post_approval_rating = $post_delete_this = $post_id = $blank;
								if( isset( $_GET['t'] ) ) {
									$post_parent           = intval( $_GET['t'] );
								} else {
									$post_parent           = 0;
								}
								$post_banned               = 0;
								$post_name                 = sanitize_text_field( $_REQUEST['post_name'] );
								$post_date                 = date( 'Y-m-d H:i:s' );
								$post_email                = sanitize_text_field( $_REQUEST['post_email'] );
								
								if( !$reply_mode ) {
									$post_title            = sanitize_text_field( $_REQUEST['post_title'] );
								} else {
									$post_title            = $blank;
								}
								
								if( isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode ) {
									$post_comment          = sanitize_text_field( $_REQUEST['post_comment'] );
								} else {
									$post_comment          = $blank;
								}
								
								$post_comment_original     = $blank;
								$post_edited               = 0;
								$post_moderator_comment    = $blank;
								$post_type                 = $blank;
								
								if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || $reply_mode ) {
								
									$validate_this = sanitize_text_field( $_REQUEST['post_url'] );
									if( filter_var( "$validate_this", FILTER_VALIDATE_URL ) === false ) {
										$_REQUEST['post_url'] = $blank;
									}
								
									$spam_check                = sanitize_text_field( esc_url( $_REQUEST['post_url'] ) );
									$check_url                 = sanitize_text_field( esc_url( str_replace( array('http://www.','https://www.'), '', $_REQUEST['post_url'] ) ) );
							
									if( $spam_check ) {
										$spam_check = new Blacklist( "$check_url" );
										if( $spam_check->spam_check ) {
											$check_url = $blank;
										}
									}
									
									if( $check_url ) {
										$ch   = curl_init();
										$opts = array (
											CURLOPT_RETURNTRANSFER => true,
											CURLOPT_URL            => $check_url,
											CURLOPT_NOBODY         => true,
											CURLOPT_TIMEOUT        => 10
										);
										curl_setopt_array ( $ch, $opts );
										curl_exec ( $ch );
										$status = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
										curl_close ( $ch );
										$path_info = pathinfo( $check_url );
										$url_present = 0;
										$post_type             = 'link';
										$post_provider         = 'link';
										$post_url              = $check_url;
									}

									if( $post_url ) {
										$post_url = $check_url;
										$parsed = parse_url( $check_url );
										$post_domain = $parsed['host'];
									}
								} else {
									$check_url = $post_url = $spam_check = $blank;
									$post_type = 'text';
								}
								
								$post_board                = $blank;
								
								// (1) Set post moderator appropriately based on user level
								//  - 0 is a user who is not logged in (guest)
								//  - 1 is a user who is logged in AND an admin
								//  - 2 is a user who is logged in and IS NOT an admin
								$post_moderator            = 0;
								if( $current_user_is_an_admin ) {
									$post_moderator        = 1;
								}
								if( $is_current_logged_in && !$current_user_is_an_admin ) {
									$post_moderator        = 2;
								}
								
								$post_last                 = $post_date;
								$post_sticky               = 0;
								$post_locked               = 0;
								
								$sent_password             = sanitize_text_field( $_REQUEST['post_password'] );
								$post_password             = sanitize_text_field( wp_hash( $_REQUEST['post_password'] ) );
								if( !$post_password || !$sent_password ) {
									$sent_password = $random_password;
									$post_password = wp_hash( $random_password );
								}

								$post_userid               = 0;
								$post_report               = 0;
								$post_reportcount          = 0;
								$post_reply_count          = 0;
								$post_guestip              = $ipaddress;
								$post_public               = 0;
								$post_like                 = 1;
								$post_dislike              = 0;
								$post_approval_rating      = 1;
								$post_delete_this          = 0;

								if( $post_parent ) {
									if( $post_email != strtolower( 'sage' ) ) {
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_last = '$post_date' WHERE post_id = $post_parent"
										);
									}
									$wpdb->query(
										"UPDATE $regularboardplugin_posts SET post_reply_count = post_reply_count + 1 WHERE post_id = $post_parent"
									);
								}
								$wpdb->query(
									"UPDATE $regularboardplugin_posts SET post_type = 'link' WHERE post_type = 'embed'"
								);
								$wpdb->query(
									"UPDATE $regularboardplugin_posts SET post_type = 'link' WHERE post_type = 'image'"
								);
								$wpdb->query(
									"UPDATE $regularboardplugin_posts SET post_type = 'text' WHERE post_type = ''"
								);
								$duplicate_found = 0;
								$no_url          = 0;
								$no_comment      = 0;
								$no_title        = 0;
								if( !$post_title && !$post_parent ) {
									$no_title = 1;
								}
								if( isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode && !$post_url ) {
									if( !$post_comment ) {
										$no_comment = 1;
									}
								}
								if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' && !$post_url ) {
									$no_url = 1;
								}
								
								if( isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' ) {
									$check_for_duplicate = " WHERE post_comment LIKE '%$post_comment%' ";
								} elseif( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' ) {
									$check_for_duplicate = " WHERE post_url LIKE '%$post_url%' ";
								} else {
									if( $post_url && !$post_comment ) {
										$check_for_duplicate = " WHERE post_url LIKE '%$post_url%' AND post_url != '' ";
									} elseif ( !$post_url && $post_comment ) {
										$check_for_duplicate = " WHERE post_comment LIKE '%$post_comment%' AND post_comment != '' ";
									} else {
										$check_for_duplicate = " WHERE post_comment LIKE '%$post_comment%' AND post_comment != '' ";
									}
								}							
								$check_duplicates = $wpdb->get_results (
									"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts $check_for_duplicate"
								);
								if( count( $check_duplicates ) >= 1 ) {
									$duplicate_found = 1;
								}
								if( !$duplicate_found && !$no_comment && !$no_title && !$no_url ) {
									$wpdb->query( 
										$wpdb->prepare( 
											"INSERT INTO $regularboardplugin_posts 
											(
												post_id,
												post_parent,
												post_name,
												post_date,
												post_date_micro,
												post_email,
												post_title,
												post_comment,
												post_comment_original,
												post_edited,
												post_moderator_comment,
												post_type,
												post_url,
												post_provider, 
												post_domain, 
												post_board,
												post_moderator,
												post_last,
												post_sticky,
												post_locked,
												post_password,
												post_userid,
												post_report,
												post_reportcount,
												post_reply_count,
												post_guestip,
												post_public,
												post_like,
												post_dislike,
												post_approval_rating,
												post_delete_this,
												post_banned
											) VALUES (
												%d,
												%d,
												%s,
												%s,
												%s,
												%s,
												%s,
												%s,
												%s,
												%d,
												%s,
												%s,
												%s,
												%s,
												%s,
												%s,
												%d,
												%s,
												%d,
												%d,
												%s,
												%d,
												%d,
												%d,
												%d,
												%s,
												%d,
												%d,
												%d,
												%d,
												%d,
												%d
											)",
											$post_id,
											$post_parent,
											$post_name,
											$post_date,
											$post_date_micro,
											$post_email,
											$post_title,
											$post_comment,
											$post_comment_original,
											$post_edited,
											$post_moderator_comment,
											$post_type,
											$post_url,
											$post_provider, 
											$post_domain, 
											$post_board,
											$post_moderator,
											$post_last,
											$post_sticky,
											$post_locked,
											$post_password,
											$post_userid,
											$post_report,
											$post_reportcount,
											$post_reply_count,
											$post_guestip,
											$post_public,
											$post_like,
											$post_dislike,
											$post_approval_rating,
											$post_delete_this,
											$post_banned
										)
									);
										
									// (1) Clean-up posts that somehow got through with no comment that are NOT replies
									$wpdb->delete (
										$regularboardplugin_posts, 
										array(
											'post_parent' => 0,
											'post_comment' => '',
											'post_url' => ''
										),
										array(
											'%d',
											'%s',
											'%s'
										)
									);

									$get_last_post = $wpdb->get_results (
										"SELECT $regularboardplugin_posts_select FROM $regularboardplugin_posts WHERE post_guestip = '$ipaddress' AND post_name = '$post_name' AND post_comment = '$post_comment' AND post_url = '$post_url' ORDER BY post_last DESC LIMIT 1"
									);
									
									if( $reply_mode ) {
										echo '<span class="information">Post successful!</span>';
									} else {
										if( count( $get_last_post ) ) {
											foreach( $get_last_post as $glp ) {
												if( $glp->post_parent ) {
													$post_location = '?t=' . $glp->post_parent . '#' . $glp->post_id;
												} else {
													$post_location = '?t=' . $glp->post_id;
												}
												echo '<span class="information">Post successful! Click <a href="' . $post_location . '">here</a> to go to it.</span>';
											}
										}
									}
								} else {
									
									if( $no_comment ) {
										echo $no_comment_error;
									} elseif( $no_title ) {
										echo $no_title_error;
									} elseif( $no_url ) {
										echo $no_url_error;
									} elseif( $duplicate_found ) {
										echo '<span class="information">Duplicate content found - post discarded.</span>';
									}
								}
							}
						}
					}
				}
				// (1) Post form
				function regularboardplugin_do_post_form() {
					
					$set_password = $set_name = '';
					if( isset( $_COOKIE['post_password'] ) ) {
						$set_password = $_COOKIE['post_password'];
					}
					if( isset( $_COOKIE['post_name'] ) ) {
						$set_name = $_COOKIE['post_name'];
					}
					
					global $page;
					
					if( isset( $_GET['t'] ) ) {
						if( is_numeric( $_GET['t'] ) ) {
							$reply_mode = 1;
						}
					} elseif( isset( $_GET['t'] ) && isset( $_GET['n'] ) ) {
						if( is_numeric( $_GET['t'] ) && is_numeric( $_GET['n'] ) ) {
							$reply_mode = 1;
						}						
					} elseif( isset( $_GET['b'] ) ) {
						$reply_mode = 0;
					} else {
						$reply_mode = 0;
					}
					
					if( isset( $_GET['a'] ) ) {
						$page = $page . '?a=' . sanitize_text_field( $_GET['a'] );
					}
					
					if( $reply_mode ) {
						echo '
						<div class="reply_mode_form">';
					}
					echo '
						<form method="post" name="post_form" class="create" action="' . $page . '">';
					wp_nonce_field( 'post_form' );
					echo '
						<div class="clear">
							<div class="float"><label for="post_name">Name</label><input type="text" id="post_name" name="post_name" value="' . $set_name . '" /></div>
							<div class="float"><label for="post_email">Email</label><input type="text" id="post_email" name="post_email" /></div>
						</div>
						<div class="clear">';
							if( !$reply_mode ) {
								echo '<div class="float"><label for="post_title">Subject(*)</label><input type="text" id="post_title" name="post_title" /></div>';
							}
							echo '<div';
							if( !$reply_mode ) {
								echo ' class="float"';
							}
							echo '><label for="post_password">Password</label><input type="password" id="post_password" name="post_password" value="' . $set_password . '" /></div>
						</div>';
						
						if( isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode ) {
							echo '<div class="clear">
								<label for="post_comment">Comment(*)</label><textarea id="post_comment" name="post_comment"></textarea>
							</div>';
						}
						
						if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || $reply_mode ) {
							echo '<div class="clear">
							<label for="post_url">URL';
							if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' ) {
								echo '(*)';
							}
							echo '</label><input type="text" id="post_url" name="post_url" />
							</div>';
						}
						
						echo '
						<input type="submit" name="do_post" value="Post" />
						</form>';
						if( $reply_mode ) {
							echo '</div>';
						}						
				}					

				echo '<span class="submission_links">';
				if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode ) {			
					echo '<a href="' . get_permalink() . '">Return</a>';
				}
				echo '<a href="?a=linkpost">Submit a link</a> <a href="?a=selfpost">Submit a text post</a>';
				echo '</span>';
					
				if( isset( $_POST['do_post'] ) ) {
					regularboardplugin_do_post();
				}
				
				if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' || $reply_mode ) {
					if( $reply_mode && count( $results ) > 0 || $reply_mode && count( $replies ) > 0 || !$reply_mode ) {
						if( $reply_mode ) { 
							echo '<script type="text/javascript">
							jQuery(document).ready(function(){
								jQuery(\'.reply_mode_form\').addClass(\'hidden_element\');
								jQuery(\'.reply_mode_toggle\').live(\'click\', function(event) {        
									 jQuery(\'.reply_mode_form\').toggleClass(\'hidden_element\');
								});
							});
							</script>';
							echo '<span class="reply_mode_toggle">';
						}
						echo $posting_mode;
						if( $reply_mode ) { 
							echo '</span>';
						}						
						regularboardplugin_do_post_form();
					}
				}
		
				if( isset( $_GET['a'] ) && $_GET['a'] == 'linkpost' || isset( $_GET['a'] ) && $_GET['a'] == 'selfpost' ) {
					$dont_show = 1;
				}
				if( !$dont_show  || $reply_mode ) {
					echo '<form name="post_contents" method="post" action="' . $page . '">';
					wp_nonce_field( 'post_contents' );
					if( !count( $results ) ) {
						// (1) No database results to display.
						echo '<section><p>' . $error_no_posts . '</p></section>';
					}					
					if( count( $results ) > 0 || count( $replies ) > 0 ) {
						// (1) Database results found; display them.
						foreach( $results as $r ) {
							
								$post_id                  = $r->post_id;
								$post_parent              = $r->post_parent;
								$post_name                = $r->post_name;
								$post_date                = $r->post_date;
								$post_date_micro          = $r->post_date_micro;
								$post_email               = $r->post_email;
								$post_title               = str_replace( '\\', '', $r->post_title);
								$post_comment             = str_replace( '\\', '', $r->post_comment);
								$post_comment_original    = $r->post_comment_original;
								$post_edited              = $r->post_edited;
								$post_moderator_comment   = $r->post_moderator_comment;
								$post_type                = $r->post_type;
								$post_url                 = $r->post_url;
								$post_provider            = $r->post_provider;
								$post_domain              = $r->post_domain;
								$post_board               = $r->post_board;
								$post_moderator           = $r->post_moderator;
								$post_last                = $r->post_last;
								$post_sticky              = $r->post_sticky;
								$post_locked              = $r->post_locked;
								$post_password            = $r->post_password;
								$post_userid              = $r->post_userid;
								$post_report              = $r->post_report;
								$post_reportcount         = $r->post_reportcount;
								$post_reply_count         = $r->post_reply_count;
								$post_guestip             = $r->post_guestip;
								$post_public              = $r->post_public;
								$post_like                = $r->post_like;
								$post_dislike             = $r->post_dislike;
								$post_approval_rating     = $r->post_approval_rating;
								$post_delete_this         = $r->post_delete_this;
								$post_banned              = $r->post_banned;
								
								if( !$post_name ) {
									$post_name            = 'anonymous';
								}
								if( !$post_title ) {
									$post_title           = 'no title';
								}
								
								$class = '';
								if( isset( $_GET['t'] ) ) {
									if( $post_id == $_GET['t'] ) {
										$class = ' class="parent"';
									}
								}
								
								echo '<section>';
								echo '<input type="checkbox" name="post_id[]" value="' . $post_id . '" />';

								if( $post_title ) {
									echo '<p><em>';
									if( $post_url ) {
										echo '<a href="' . $post_url . '">' . $post_title . '</a>';
									} else {
										echo $post_title;
									}
									echo '</em>';
								}
								
								if( $post_url ) {
									echo ' <small class="domain"><a href="//' . $post_domain . '">' . $post_domain . '</a></small>';
								}
								
								echo '&mdash; <small>no. ' . $post_id . '</small>';
								
								if( $post_parent && !$reply_mode ) {
									echo ' - <small><a href="' . $page . '?t=' . $post_parent . '">+</a></small><br />';
								} elseif( !$post_parent && !$reply_mode ) {
									echo ' - <small><a href="' . $page . '?t=' . $post_id . '">' . $post_reply_count . ' comments</a></small><br />';
								} else {
									echo '<br />';
								}
								echo '<small class="name"><a href="?u=' . $post_name . '">' . $post_name . '</a></small>';
								
								if( $current_user_is_an_admin ) {
									echo ' <small class="name">' . $post_guestip . '</small>';
								}
								
								
								if ( $post_moderator == 1 ) {
									echo $admin_code;
								} elseif ( $post_moderator == 2 ) {
									echo $user_code;
								} else {
									echo $guest_code;
								}
								
								echo ' <date>' . regularboardplugin_timesince( $post_date ) . '</date>';
								echo '</p>';
								if( $reply_mode && $post_url ) {
									echo '</section><div class="mediaEmbed">';
										echo wp_oembed_get ( $post_url );
									echo '</div><section>';
								}
								if( $reply_mode && $post_comment ) {
									echo wpautop( regularboardplugin_comment_format( $post_comment ) );
								}
								
								if( $post_moderator_comment || $post_banned ) {
									echo '<p class="mod_comment">';
									if( $post_moderator_comment ) {
										echo $post_moderator_comment . ' ';
									}
									if( $post_banned ) {
										echo '<em class="banned">user was banned for this post.</em>';
									}
									echo '</p>';
								}
								
								echo '</section>';
								
								
								$post_parent = $post_name = $post_date = $post_date_micro = $post_email = $post_title = $post_comment = $post_comment_original = $post_edited = $post_moderator_comment = $post_type = $post_url = $post_board = $post_moderator = $post_last = $post_sticky = $post_locked = $post_password = $post_userid = $post_report = $post_reportcount = $post_reply_count = $post_guestip = $post_public = $post_like = $post_dislike = $post_approval_rating = $post_delete_this = '';

						}
						if( $reply_mode ) {
							if( count( $replies ) > 0 ) {
								foreach( $replies as $r ) {
									$post_id                  = $r->post_id;
									$post_parent              = $r->post_parent;
									$post_name                = $r->post_name;
									$post_date                = $r->post_date;
									$post_date_micro          = $r->post_date_micro;
									$post_email               = $r->post_email;
									$post_title               = str_replace( '\\', '', $r->post_title);
									$post_comment             = str_replace( '\\', '', $r->post_comment);
									$post_comment_original    = $r->post_comment_original;
									$post_edited              = $r->post_edited;
									$post_moderator_comment   = $r->post_moderator_comment;
									$post_type                = $r->post_type;
									$post_url                 = $r->post_url;
									$post_provider            = $r->post_provider;
									$post_domain              = $r->post_domain;										
									$post_board               = $r->post_board;
									$post_moderator           = $r->post_moderator;
									$post_last                = $r->post_last;
									$post_sticky              = $r->post_sticky;
									$post_locked              = $r->post_locked;
									$post_password            = $r->post_password;
									$post_userid              = $r->post_userid;
									$post_report              = $r->post_report;
									$post_reportcount         = $r->post_reportcount;
									$post_reply_count         = $r->post_reply_count;
									$post_guestip             = $r->post_guestip;
									$post_public              = $r->post_public;
									$post_like                = $r->post_like;
									$post_dislike             = $r->post_dislike;
									$post_approval_rating     = $r->post_approval_rating;
									$post_delete_this         = $r->post_delete_this;
									$post_banned              = $r->post_banned;
									
									if( !$post_name ) {
										$post_name            = 'anonymous';
									}
									if( !$post_title ) {
										$post_title           = 'reply';
									}										
									
									echo '<section id="' . $post_id . '">';
									echo '<input type="checkbox" name="post_id[]" value="' . $post_id . '" />';
									echo '<p>';
									if( $post_title ) {
										echo '<em>';
										if( $post_url ) {
											echo '<a href="' . $post_url . '">' . $post_title . '</a>';
										} else {										
											echo $post_title;
										}
										echo '</em>';
									}
									if( $post_url ) {
										echo ' <small class="domain"><a href="//' . $post_domain . '">' . $post_domain . '</a></small>';
									}										
									
									echo ' &mdash; <small>no. ' . $post_id . '</small><br />';
									echo '<small class="name"><a href="?u=' . $post_name . '">' . $post_name . '</a></small>';
									
									if( $current_user_is_an_admin ) {
										echo ' <small class="name">' . $post_guestip . '</small>';
									}
									

									if ( $post_moderator == 1 ) {
										echo $admin_code;
									} elseif ( $post_moderator == 2 ) {
										echo $user_code;
									} else {
										echo $guest_code;
									}
									
									echo ' <date>' . regularboardplugin_timesince( $post_date ) . '</date>';

									echo '</p>';
									
									if( $post_url ) {
										echo '<div class="mediaEmbed">';
											echo wp_oembed_get ( $post_url );
										echo '</div>';
									}								
									if( $reply_mode && $post_comment ) {
										echo wpautop( regularboardplugin_comment_format( $post_comment ) );
									}

									if( $post_moderator_comment || $post_banned ) {
										echo '<p class="mod_comment">';
										if( $post_moderator_comment ) {
											echo $post_moderator_comment . ' ';
										}
										if( $post_banned ) {
											echo '<em class="banned">user was banned for this post.</em>';
										}
										echo '</p>';
									}
									
									echo '</section>';										
									
									$post_id = $post_parent = $post_name = $post_date = $post_date_micro = $post_email = $post_title = $post_comment = $post_comment_original = $post_edited = $post_moderator_comment = $post_type = $post_url = $post_board = $post_moderator = $post_last = $post_sticky = $post_locked = $post_password = $post_userid = $post_report = $post_reportcount = $post_reply_count = $post_guestip = $post_public = $post_like = $post_dislike = $post_approval_rating = $post_delete_this = '';
									
								}
							}
						
					
					}						
						
						$set_password = '';
						if( isset( $_COOKIE['post_password'] ) ) {
							$set_password = $_COOKIE['post_password'];
						}
					

						$loc_one = $loc_previous = $loc_next = '';
						
						if( $totalpages > 0 ) {
							$pageresults = round($totalpages / $postsperpage);

							if( $page_no > 1 ) {
								if( $get ) {
									$loc_one = '<a class="left" href="?' . $get . '=' . $got . '">latest</a>';
								} else {
									$loc_one = '<a class="left" href="?n=1">Latest</a>';
								}
							}
							if( $page_no > 1 && $pageresults >= $page_no ) {
								if( $get ) {
									$loc_previous = '<a class="left" href="?' . $get . '=' . $got . '&n=' . ( $page_no - 1 ) . '">Newer</a>';
								} else {
									$loc_previous = '<a class="left" href="?n=' . ( $page_no - 1 ) . '">Newer</a>';
								}
							}		
							if($page_no == 1 && $pageresults > $page_no ){
								if( $get ) {
									$loc_next = '<a class="right" href="?' . $get . '=' . $got . '&n=2">Older</a>';
								} else {
									$loc_next = '<a class="right" href="?n=2">Older</a>';
								}
							}
							if($pageresults > $page_no  ){
								if( $get ) {
									$loc_next = '<a class="right" href="?' . $get . '=' . $got . '&n=' . ( $page_no + 1 ) . '">Older</a>';
								} else {
									$loc_next = '<a class="right" href="?n=' . ( $page_no + 1 ) . '">Older</a>';
								}
							}
							if( $pageresults ) {
								echo '<div class="pages">' . $loc_one . $loc_previous . $loc_next . '</div>';
							}
							
						}
						
						
						if( $current_user_is_an_admin ) {
							echo '<div class="small">
								<input type="text" value="" name="reason_for_ban" placeholder="Reason" id="reason_for_ban" />
								<div class="small_input"><input type="submit" name="ban_user" id="ban_user" value="B" /></div>
								<div class="small_input"><input type="submit" name="ban_all_user" id="ban_all_user" value="ALL" /></div>
								<div class="small_input"><input type="submit" name="ban_delete_user" id="ban_delete_user" value="B+D" />
								</div>
							</div>';
						}
						
						echo '<div class="small"><input type="password" value="' . $set_password . '" name="delete_posts_password" id="delete_posts_password" /><input type="submit" name="delete_posts" id="delete_posts" value="Delete" /></div>';
						
						echo '</form>';
						
						if( $current_user_is_an_admin ) {
							if( isset( $_POST['ban_delete_user'] ) ) {
								if(!empty($_POST['post_id'])) {
									foreach( $_POST['post_id'] as $check ) {
									
										if( $_REQUEST['reason_for_ban'] ) {
											$reason_for_ban = sanitize_text_field( $_REQUEST['reason_for_ban'] );
										} else {
											$reason_for_ban = 'No reason given.';
										}							
										
										$set_ban_date = date( 'Y-m-d H:i:s' );
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_comment = '' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_url = '' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_email = '' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_banned = 1 WHERE post_id = $check"
										);
										$wpdb->query(									
											"UPDATE $regularboardplugin_posts SET post_public = 2 WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_moderator_comment = '$reason_for_ban' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_password = '' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_last = '$set_ban_date' WHERE post_id = $check"
										);
										
									}
								}
							}
							if( isset( $_POST['ban_all_user'] ) ) {
								if(!empty($_POST['post_id'])) {
									foreach( $_POST['post_id'] as $check ) {
										$this_ipaddress = $wpdb->get_results (
											"SELECT post_guestip FROM $regularboardplugin_posts WHERE post_id = $check"
										);
										if( count( $this_ipaddress ) ) {
											foreach( $this_ipaddress as $tip ) {
												if( $_REQUEST['reason_for_ban'] ) {
													$reason_for_ban = sanitize_text_field( $_REQUEST['reason_for_ban'] );
												} else {
													$reason_for_ban = 'No reason given.';
												}							
												
												$the_ip_to_ban = $tip->post_guestip;
												
												$set_ban_date = date( 'Y-m-d H:i:s' );
												
												$wpdb->query(
													"UPDATE $regularboardplugin_posts SET post_banned = 1 WHERE post_guestip = '$the_ip_to_ban'"
												);
												$wpdb->query(									
													"UPDATE $regularboardplugin_posts SET post_public = 2 WHERE post_guestip = '$the_ip_to_ban'"
												);
												$wpdb->query(
													"UPDATE $regularboardplugin_posts SET post_moderator_comment = '$reason_for_ban' WHERE post_guestip = '$the_ip_to_ban'"
												);
												$wpdb->query(
													"UPDATE $regularboardplugin_posts SET post_password = '' WHERE post_guestip = '$the_ip_to_ban'"
												);
												$wpdb->query(
													"UPDATE $regularboardplugin_posts SET post_last = '$set_ban_date' WHERE post_guestip = '$the_ip_to_ban'"
												);											
											}
										}

										
									}
								}
							}						
							if( isset( $_POST['ban_user'] ) ) {
								if(!empty($_POST['post_id'])) {
									foreach( $_POST['post_id'] as $check ) {
									
										if( $_REQUEST['reason_for_ban'] ) {
											$reason_for_ban = sanitize_text_field( $_REQUEST['reason_for_ban'] );
										} else {
											$reason_for_ban = 'No reason given.';
										}							
										
										$set_ban_date = date( 'Y-m-d H:i:s' );
										
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_banned = 1 WHERE post_id = $check"
										);
										$wpdb->query(									
											"UPDATE $regularboardplugin_posts SET post_public = 2 WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_moderator_comment = '$reason_for_ban' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_password = '' WHERE post_id = $check"
										);
										$wpdb->query(
											"UPDATE $regularboardplugin_posts SET post_last = '$set_ban_date' WHERE post_id = $check"
										);
										
									}
								}
							}
						}
						
						if( isset( $_POST['delete_posts'] ) ) {
							if(!empty($_POST['post_id'])) {
								foreach( $_POST['post_id'] as $check ) {
									$check_password = sanitize_text_field( wp_hash( $_REQUEST['delete_posts_password'] ) );
									$check_for_password = $wpdb->get_results (
										"SELECT post_password FROM $regularboardplugin_posts WHERE post_id = $check LIMIT 1"
									);
									$check_exists = $wpdb->get_results (
										"SELECT post_id FROM $regularboardplugin_posts WHERE post_parent = $check"
									);	
									// (1) Logged-in Admin don't need to enter passwords.
									if( $current_user_is_an_admin ) {
										$wpdb->delete (
											$regularboardplugin_posts, 
											array(
												'post_id' => $check
											),
											array(
												'%d'
											)
										);							
									} else {
										$check_password = sanitize_text_field( wp_hash( $_REQUEST['delete_posts_password'] ) );
										$check_for_password = $wpdb->get_results (
											"SELECT post_password FROM $regularboardplugin_posts WHERE post_id = $check LIMIT 1"
										);									
										foreach( $check_for_password as $cfp ) {
											if( $cfp->post_password == $check_password ) {
												$wpdb->delete (
													$regularboardplugin_posts, 
													array(
														'post_password' => $check_password,
														'post_id' => $check
													),
													array(
														'%s',
														'%d'
													)
												);
											}
										}
									}
									if( $current_user_is_an_admin ) {
										if( count( $check_exists ) == 0 ) {
											$wpdb->delete (
												$regularboardplugin_posts, 
												array(
													'post_parent' => $check
												),
												array(
													'%d'
												)
											);
										}
									} else {
										$check_password = sanitize_text_field( wp_hash( $_REQUEST['delete_posts_password'] ) );
										$check_for_password = $wpdb->get_results (
											"SELECT post_password FROM $regularboardplugin_posts WHERE post_id = $check LIMIT 1"
										);									
										foreach( $check_for_password as $cfp ) {
											if( $cfp->post_password == $check_password ) {
												if( count( $check_exists ) == 0 ) {
													$wpdb->delete (
														$regularboardplugin_posts, 
														array(
															'post_parent' => $check
														),
														array(
															'%d'
														)
													);
												}
											}
										}
									}								
								}	
							}
						}

					}
				}
			}
		
		} else {
			echo 'You are not permitted to view this content.';
		}
		echo '</div>';
	
	}
		
?>