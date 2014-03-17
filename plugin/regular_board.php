<?php 

/**
 * Regular Board Functionality
 *
 * (1) Main functionality that drives any Regular Board installation
 *
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

/**
 * Include header information if post content contains the shortcode
 */
 
function regular_board_head( $atts ) {
	global $wp, $post, $wpdb, $regular_board_posts_select;
	$content = $post->post_content;
	$regular_board_posts   = $wpdb->prefix . 'regular_board_posts';
	$regular_board_boards  = $wpdb->prefix . 'regular_board_boards';
	if ( has_shortcode ( $content, 'regular_board' ) ) {
		include( plugin_dir_path(__FILE__) . '/regular_board_meta.php' );
		if ( get_option ( 'regular_board_robots' ) ) {
			echo '<meta name="robots" content="noindex,nofollow"/>';
		}
	}
}

/**
 * Shortcode functions [regular_board]
 */
 
function regular_board_shortcode ( $content = null ) {
	global $wpdb, $wp, $post, $ipaddress, $random_password, $regular_board_version, $regular_board_posts_select;
	
	/**
	 * Display Regular Board content if IP address is valid.
	 */
	 
	if ( $ipaddress !== false ) {
		
		/**
		 * Variables used throughout the plugin.
		 */

        $regular_board_posts    = $wpdb->prefix . 'regular_board_posts';
		$regular_board_boards   = $wpdb->prefix . 'regular_board_boards';
		$regular_board_users    = $wpdb->prefix . 'regular_board_users';
		$regular_board_bans     = $wpdb->prefix . 'regular_board_bans';
		$regular_board_logs     = $wpdb->prefix . 'regular_board_logs';
		$regular_board_messages = $wpdb->prefix . 'regular_board_messages';
		$regular_board_friends  = $wpdb->prefix . 'regular_board_friends';
		 
		$user_logged_in        = 0;
		if ( is_user_logged_in() ) {
			$user_logged_in    = 1;
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
		$entered_parent                = 0;
		if ( get_option ( 'regular_board_protected' ) ) {
			$protectedboards               = explode   ( ',', get_option ( 'regular_board_protected' ) );
			$protected_boards              = array_map ( 'regular_board_apply_quotes',  $protectedboards );
			
		}
		$registration_open             = get_option ( 'regular_board_registration' );
		$enable_blog                   = get_option ( 'regular_board_enableblog' );
		$display_wipe                  = get_option ( 'regular_board_wipedisplay' );
		$banned_image                  = get_option ( 'regular_board_bannedimage' );
		$board_banner                  = get_option ( 'regular_board_boardbanner' );
		$accounts_per_ip               = get_option ( 'regular_board_accountsper' );
		$blog_title                    = get_bloginfo();
		$board_wipe_every              = get_option ( 'regular_board_wipeall' );
		$board_wipe_per                = get_option ( 'regular_board_wipeper' );
		$board_wipe_date               = strtotime ( get_option ( 'regular_board_wipealldate' ) );
		$current_timestamp             = date ( 'Y-m-d H:i:s' );		
		if ( $board_wipe_every && $board_wipe_every != strtolower ( 'never' ) && $board_wipe_per == strtolower ( 'board' ) ) {
			$today_is   = strtotime ( $current_timestamp );
			if ( strpos ( strtolower ( $board_wipe_every ), 'minute' ) ) {
				$uptime = intval ( $board_wipe_every ) * 60;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'hour' ) ) {
				$uptime = intval ( $board_wipe_every ) * 3600;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'day' ) ) {
				$uptime = intval ( $board_wipe_every ) * 86400;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'week' ) ) {
				$uptime = intval ( $board_wipe_every ) * 604800;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'month' ) ) {
				$uptime = intval ( $board_wipe_every ) * 2628000;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'year' ) ) {
				$uptime = intval ( $board_wipe_every ) * 31536000;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'second' ) ) {
				$uptime = intval ( $board_wipe_every ) * 1;
			} else {
				$uptime = intval ( $board_wipe_every ) * 60;
			}
			$board_life = ( intval ( $board_wipe_date ) + intval ( $uptime ) );
			$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
			$wipe = number_format ( intval ( $today_is ) - intval ( $board_wipe_date ) ) / intval ( $uptime ) * 100;
			if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
				$wipe_on_this_date = date ( "M d, Y - h:i:s A T", $board_life );
				$wipe_countdown = $wipe_on_this_date;
			}
			if($today_is > $board_life){
				$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = 0 WHERE board_id > 0" );
				
				if ( $protectedboards ) {
					$wpdb->query ( "DELETE FROM $regular_board_posts WHERE post_board NOT IN ( " . join (',', $protected_boards ) . ")");
					
				} else{				
					$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 0 ), array ( '%d' ) );
					$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 1 ), array ( '%d' ) );
					$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 2 ), array ( '%d' ) );
					$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 3 ), array ( '%d' ) );
				}
				
				update_option ( 'regular_board_wipealldate', str_replace ( '\\', '', $current_timestamp ) );
			}
		}
		
		
		$formatting            = get_option ( 'regular_board_formatting' );
		$auto_url              = get_option ( 'regular_board_autourl' );
		$announcements         = get_option ( 'regular_board_announcements' );
		$max_links             = get_option ( 'regular_board_maxlinks' );
		$posting_options       = get_option ( 'regular_board_postingoptions' );
		$search_enabled        = get_option ( 'regular_board_search' );
		$enable_url            = get_option ( 'regular_board_enableurl' );
		$enable_rep            = get_option ( 'regular_board_enablerep' );
		$max_body              = get_option ( 'regular_board_maxbody' );
		$max_replies           = get_option ( 'regular_board_maxreplies' );
		$max_text              = get_option ( 'regular_board_maxtext' );
		
		$these_boards          = get_option ( 'regular_board_boards' );
		if ( $these_boards ) {
			$these_boards      = explode   ( ',', $these_boards );
			$these_boards      = array_map ( 'regular_board_apply_quotes',  $these_boards );
		}
		
		$user_flood            = get_option ( 'regular_board_userflood' );
		$imgurid               = get_option ( 'regular_board_imgurid' );			
		$flood_gate            = get_option ( 'regular_board_floodgate' );
		$archive_gate          = get_option ( 'regular_board_archivegate' );
		$posts_per_page        = get_option ( 'regular_board_postsper' );
		$roll                  = get_option ( 'regular_board_roll' );
		$id_display            = get_option ( 'regular_board_ids' );
		$mod_code              = '<strong>' . get_option ( 'regular_board_modcode', '##MOD' ) . '</strong>';
		$user_mod_code         = '<strong>' . get_option ( 'regular_board_usermodcode', '##JRMOD' ) . '</strong>';
		$current_page          = protocol_relative_url_dangit( get_permalink() );
		$the_ip                = $ipaddress;
		$user_ip               = sanitize_text_field ( wp_hash ( $the_ip ) );
		$check_ammount         = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_ip = '$user_ip'" );
		$count_users_total     = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users" );
		$posts_users_total     = $wpdb->get_var ( "SELECT SUM(user_posts) FROM $regular_board_users" );
		
		$user_total_allowed    = get_option ( 'regular_board_totaluserallowed' );
		if ( $user_total_allowed ) {
			if ( $user_total_allowed <= $count_users_total ) {
				$registration_open = 0;
			} else {
				$registration_open = 1;
			}
		}
		
		$check_this_ip         = sanitize_text_field ( $the_ip );
		$query                 = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
		if ( $query ) {
			if ( isset ( $_GET['b'] ) ) {
				$the_board             = sanitize_text_field ( strtolower( $_GET['b'] ) );
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
		
		if ( !$this_area && !$the_board && !$this_user && !$this_thread ) {
			$nothing_is_here   = 1;
		}
		
		$is_user_mod           = false;
		$is_user               = true;
		$posting               = 1;
		
		/**
		 * Check if the current IP address has been entered into the bans table at any point.
		 * If it has, display appropriate ban information and set them as such.
		 */
		 
		$getuser     = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_bans WHERE banned_ip = %s AND banned_banned = %d LIMIT 1", $user_ip, 0  ) );
		$userisbanned = 0;
		if ( count ( $getuser ) > 0 ) {
			$userisbanned = 1;
		}
		
		/**
		 * USER INFORMATION
		 * Get all information for current IP (user)
		 */
		
		/** 
		 * Get all information from the database associated with the currently connected 
		 * IP address for use throughout the plugin.
		 */
		 
		$myinformation = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_logged_in_from = %s AND user_logged_in = 1 LIMIT 1", $user_ip ) );
		if ( count ( $myinformation ) > 0 ) {
			foreach ( $myinformation as $myinfo ) {
				$profileavatar       = sanitize_text_field ( $myinfo->user_avatar );
				$profileslogan       = sanitize_text_field ( str_replace ( '\\', '', $myinfo->user_slogan ) );
				$profileid           = intval ( $myinfo->user_id );
				$profileheaven       = intval ( $myinfo->user_heaven );
				$profile_email       = sanitize_text_field ( $myinfo->user_email );
				$profile_name        = sanitize_text_field ( $myinfo->user_name );
				if ( !$profile_name ) {
					$profile_name    = 'null';
				}
				$profilepassword     = sanitize_text_field ( $myinfo->user_password );
				$profilefollow       = sanitize_text_field ( $myinfo->user_follow );
				$following           = sanitize_text_field ( $myinfo->user_follow );
				$boards              = sanitize_text_field ( $myinfo->user_boards );
				$profileboards       = sanitize_text_field ( $myinfo->user_boards );
				$following           = sanitize_text_field ( $profilefollow );
				
				if ( !$myinfo->user_logged_in ) {
					$user_exists         = 0;
				}
				if ( $myinfo->user_logged_in ) {
					$user_exists         = 1;
				}
				
				
				if ( $profileboards ) {
					$profileboards       = explode   ( ',', $profileboards );
					$profileboards       = array_map ( 'regular_board_apply_quotes', $profileboards );
				}
				if( $following ) {
					$following       = explode   ( ',', $following );
					$following       = array_map ( 'regular_board_apply_quotes', $following );
				}
				$profile_strikes     = intval ( $myinfo->user_strikes );
				$profile_strikes_up  = intval ( $myinfo->user_strikes + 1 );
				$profile_level       = intval ( $myinfo->user_level );
				$profile_level_up    = intval ( $myinfo->user_level + 1 );
				$profile_posts       = intval ( $myinfo->user_posts );
				$profile_posts_up    = intval ( $myinfo->user_posts + 1 );
				$i_am_logged_in      = intval ( $myinfo->user_logged_in );
				
				if ( $profile_strikes == 0 ) {
					$ban_length_minutes = '10 minutes';
				} else {
					$ban_length_minutes = $profile_strikes . '0 minutes';
				}				
				
				if ( $profile_level <= 50 ) {
					$profile_posts_check = $profile_posts / 10;
				}
				if ( $profile_level <= 100 && $profile_level > 50  ) {
					$profile_posts_check = $profile_posts / 20;
				}
				if ( $profile_level <= 150 && $profile_level > 100  ) {
					$profile_posts_check = $profile_posts / 30;
				}
				if ( $profile_level <= 200 && $profile_level > 150  ) {
					$profile_posts_check = $profile_posts / 40;
				}
				if ( $profile_level <= 250 && $profile_level > 200  ) {
					$profile_posts_check = $profile_posts / 50;
				}
				if ( $profile_level <= 300  && $profile_level > 250 ) {
					$profile_posts_check = $profile_posts / 60;
				}
				if ( $profile_level <= 350  && $profile_level > 300 ) {
					$profile_posts_check = $profile_posts / 70;
				}
				if ( $profile_level <= 400  && $profile_level > 350 ) {
					$profile_posts_check = $profile_posts / 80;
				}
				if ( $profile_level <= 450  && $profile_level > 400 ) {
					$profile_posts_check = $profile_posts / 90;
				}
				if ( $profile_level <= 500  && $profile_level > 450 ) {
					$profile_posts_check = $profile_posts / 100;
				}
				if ( !isset ( $_GET['message'] ) ) {
					$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) ORDER BY messages_id DESC", $profile_name, $profile_name ) );
				}
				if ( isset ( $_GET['message'] ) ) {
					$message_id = intval ( $_GET['message'] );
					$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) AND messages_id = %d LIMIT 1", $profile_name, $profile_name, $message_id ) );
				}
				$my_unread   = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_messages WHERE messages_read = 0 AND messages_to = '$profile_name'" );
				$my_unread   = intval ( $my_unread );
				$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $profile_name, $profile_name, 1 ) );
				$my_waiting  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE friends_connectee = %s AND friends_mutual = %d", $profile_name, 0 ) );
				$my_waitings = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_friends WHERE friends_connectee = '$profile_name' AND friends_mutual = 0" );
			}
			
			if ( isset ( $_POST['log_out'] ) ) {
				$wpdb->update (
					$regular_board_posts,
					array ( 
						'user_logged_in' => 0
					),
					array ( 
						'user_ip'      => $user_ip
					),
					array ( 
						'%d', 
						'%s'
					)
				);			
			}
			
		}
	
		/**
		 * Get all boards
		 * If there is only one, set that board as the main board so we can do things like post new topics from anywhere on the install.
		 */
		 
		$getboards = $wpdb->get_results ( "SELECT * FROM $regular_board_boards WHERE board_shortname != '' ORDER BY board_postcount DESC, board_name ASC" );
		if ( count ( $getboards ) == 1 ) {
			foreach ( $getboards as $board ) {
				$thisboard = $board->board_shortname;
			}
		}
		
		/**
		 * Results for the board we are currently viewing.
		 */
		 
		if ( $the_board ) {
			$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
		}
		
		if ( isset ( $_REQUEST['board'] ) ) {
			$the_board = sanitize_text_field ( strtolower ( $_REQUEST['board'] ) );
			$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
		}
		
		/**
		 * If there is only one board, get results as if it's the current board.
		 */
		 
		if ( !$the_board && $thisboard ) {
			$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thisboard ) );
		}
		
		if ( $this_thread ) {
			$thread_board      = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
			if ( $thread_board ) {
				$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thread_board ) );
			}
		}
		
		/**
		 * Our search input
		 */
		 
		if ( $search_enabled && isset ( $_POST['regular_board_search_submit'] ) && $_REQUEST['regular_board_search'] ) {
			$search = sanitize_text_field ( str_replace ( '\'', '\\\'', $_REQUEST['regular_board_search'] ) );
		}
		
		/**
		 * If we're browsing a board, let's get all of the information associated with that board 
		 * from _boards.
		 */
		 
		if ( count ( $get_current_board ) > 0 ) {
			foreach ( $get_current_board as $current_board_information ) {
				$lock              = intval ( $current_board_information->board_locked );
				$board_id          = intval ( $current_board_information->board_id );
				$board_name        = $current_board_information->board_name;
				$board_short       = $current_board_information->board_shortname;
				$board_description = $current_board_information->board_description;
				$board_mods        = $current_board_information->board_mods;
				$board_jans        = $current_board_information->board_janitors;
				$board_posts       = intval ( $current_board_information->board_postcount );
				$require_logged    = intval ( $current_board_information->board_logged );
				$boardwipe         = $current_board_information->board_wipe;
				$boarddate         = $current_board_information->board_date;
				if ( !$board_wipe_every ) {
					if( $boardwipe && $boardwipe != strtolower ( 'never' ) ) {
						$board_date = strtotime ( $boarddate );
						$today_is   = strtotime ( $current_timestamp );
						if ( strpos ( strtolower ( $boardwipe ), 'minute' ) ) {
							$uptime   = intval ( $boardwipe ) * 60;
							$interval = ' every minute';
						} elseif ( strpos ( strtolower ( $boardwipe ), 'hour' ) ) {
							$uptime   = intval ( $boardwipe ) * 3600;
							$interval = ' hourly';
						} elseif ( strpos ( strtolower ( $boardwipe ), 'day' ) ) {
							$uptime   = intval ( $boardwipe ) * 86400;
							$interval = ' daily';
						} elseif ( strpos ( strtolower ( $boardwipe ), 'week' ) ) {
							$uptime   = intval ( $boardwipe ) * 604800;
							$interval = ' weekly';
						} elseif ( strpos ( strtolower ( $boardwipe ), 'month' ) ) {
							$uptime   = intval ( $boardwipe ) * 2628000;
							$interval = ' monthly';
						} elseif ( strpos ( strtolower ( $boardwipe ), 'year' ) ) {
							$uptime   = intval ( $boardwipe ) * 31536000;
							$interval = ' yearly';
						} else {
							$uptime   = intval ( $boardwipe ) * 60;
							$interval = ' every minute';
						}
						$board_life = ( intval ( $board_date ) + intval ( $uptime ) );
						$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
						$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
						$next_clean = date($boarddate, time() + $next_wipe);
	
						if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
							$wipe_on_this_date = date ( "M d, Y - h:i:s A T", $board_life );
							$wipe_countdown = $wipe_on_this_date;
						}
					}
				}
				
				if ( $board_wipe_every ) {
					$wipe_countdown = '';
				}
				
				if( $board_description ) {
					$boardheader      = '<li><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . ' - ' . $board_description . ' <i class="fa fa-caret-square-o-down"></i></a>';
				}
				if( !$board_description ) {
					$boardheader      = '<li><a href="' . $current_page . '?b=' . $board_short . '">' . $board_short .  ' - ' . $board_name . ' <i class="fa fa-caret-square-o-down"></i></a>';
				}
				echo '<script type="text/javascript">document.title = \'' . $board_name . ' / ' . $board_short . '\';</script>';
			}
		} else {
			
			/**
			 * If we're not browsing a board, the board header will be blank.
			 */
			 
			$boardheader = '';
		}
	
		
		/**
		 * Queries for:
		 * (1) Board
		 * (2) Search
		 * (3) Topics
		 * (4) Replies
		 * (5) All
		 * (6) Subscribed
		 * (7) Following
		 * (8) Thread
		 * (9) History/profile
		 */
		 
		$use_this      = 0;
		$order_by = "post_id DESC";
		if ( $search_enabled && $search ) {
			$use_this++;
			$where_by = "WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' )";
		} else {
			if ( $the_board ) {
				$use_this++;
				$where_by = "WHERE post_parent = 0 AND post_board = '$the_board'";
				$order_by = "post_sticky DESC, post_last DESC";
			}		
			if ( $this_area == 'topics' || !$the_board && !$this_area && !$this_user) {
				$use_this++;
				$where_by = "WHERE post_parent = 0";
				$order_by = "post_sticky DESC, post_last DESC";
			}
			if( !$the_board && $this_area == 'replies' && !$this_thread && !$this_user ){
				$use_this++;
				$where_by = "WHERE post_parent != 0";
				$order_by = "post_sticky DESC, post_last DESC";
			}
			if( $nothing_is_here ) {
				$use_this++;
				if ( $these_boards ) {
					$where_by = "WHERE post_parent = 0 AND post_board IN ( " . join (',', $these_boards ) . ") ";
				} else {
					$where_by = "WHERE post_parent = 0 ";
				}
			}
			if( !$the_board && $this_area == 'gallery' && !$this_thread && !$this_user ) {
				$use_this++;
				$where_by = "WHERE post_url != ''";
			}		
			if( !$the_board && $this_area == 'subscribed' && $profileboards && !$this_user ) {
				$use_this++;
				$where_by = "WHERE post_board IN ( " . join (',', $profileboards ) . ")";
			}
			if( !$the_board && $this_area == 'following' && $following && !$this_user ) {
				$use_this++;
				$where_by = "WHERE ( post_userid IN (" . join (',', $following ) . ") OR post_name IN (" . join (',', $following ) . ") )";
			}
			if ( $this_thread && !$this_user ) {
				$use_this++;
				$where_by = "WHERE post_id = $this_thread AND post_parent = 0";
				if ( $search_enabled && $search ) {
					$countParentReplies = "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_parent = $this_thread";
				} else {					
					$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d", $this_thread ) );
				}
			}
			if ( $this_area == 'history' ) {
				$use_this++;
				$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid, $this_user ) );
				$where_by = "WHERE post_userid = $profileid";
				$order_by = "post_date DESC";		
			}
			if ( $this_area == 'mod' ) {
				$mod_logs = $wpdb->get_results ( "SELECT * FROM $regular_board_logs ORDER BY logs_id DESC LIMIT 50 " );
			}
			if ( $this_user ) {
				$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $this_user, $this_user, 1 ) );
				$use_this++;
				$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_name = %s LIMIT 1", $profileid, $this_user ) );
				$where_by = "WHERE post_name = '$this_user'";
				$order_by = "post_date DESC";
			}
		}
		if ( $use_this > 0 ) {
			if ( $search_enabled && $search ) {
				$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by AND ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' )" );
			} else {
				$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by" );
			}
			if ( $totalpages > 0 ) {
				if ( strpos ( strtolower ( $query ), 'n=' ) ) {
					$results    = intval ( $_GET['n'] );
				}
				if( $results ) {
					$start = ( $results - 1 ) * $posts_per_page;
				} else {
					$start = 0;
				}
				$getposts = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
			}
		}
		
		/**
		 * Determine whether or not the current IP belongs to:
		 * 1-> a logged in admin
		 * 2-> a logged in user who is either a usermoderator or userjanitor
		 * 3-> a user, logged in or not, who does not meet the above criteria
		 */
		 
		$current_user       = wp_get_current_user();
		$current_user_login = $current_user->user_login;
		if ( current_user_can ( 'manage_options' ) ) {
			$is_moderator = true;
		}
		if( $board_mods ) {
			$usermods = explode ( ',', $board_mods );
			if ( in_array ( $current_user_login, $usermods ) || in_array ( $profileid, $usermods ) ) {
				$is_user_mod    = true;
				$user_logged_in = 1;
			}
		}
		if ( $board_jans ) {
			$userjanitors = explode ( ',', $board_jans );
			if (in_array ( $current_user_login, $userjanitors ) || in_array ( $profileid, $userjanitors ) ) {
				$is_user_janitor = true;
				$user_logged_in  = 1;
			}
		}
		if ( $usermod ) {
			$usermod = array ( $usermod );
		}
		if ( $is_moderator ) {
			$is_user = false;
		}
		if( $is_user_mod ) {
			$is_user = false;
		}
		if ( $is_user_janitor ) {
			$is_user = false;
		}
		if ( $is_moderator || $is_user_mod ) {
			$get_reports = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_reportcount > %d OR post_public = %d", 0, 2 ) );
			$get_deleted = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_public = %d", 3 ) );
			$get_queue   = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_public = %d", 666 ) );
		}
		
		/**
		 * Allow a moderator to post in locked threads.
		 */
		 
		if ( $lock == 1 ) {
			if ( $is_user ) {
				$posting = 0;
			}
			if ( $is_user !== true ) {
				$posting = 1;
			}
		}
		foreach ( $getboards as $gotboards ) {
			if ( !$board_wipe_every ) {
				if( $gotboards->board_wipe && $gotboards->board_wipe != strtolower ( 'never' ) ) {
					$board_date = strtotime($gotboards->board_date);
					$today_is = strtotime($current_timestamp);
					if ( strpos ( strtolower ( $gotboards->board_wipe ), 'minute' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 60;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'hour' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 3600;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'day' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 86400;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'week' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 604800;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'month' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 2628000;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'year' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 31536000;
					} else {
						$uptime = intval ( $gotboards->board_wipe ) * 60;
					}
					$board_life = ( intval ( $board_date ) + intval ( $uptime ) );
					$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_date ) ) );
					$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
					
					if($today_is > $board_life){
						$wpdb->delete ( $regular_board_posts, array ( 'post_board' => $gotboards->board_shortname ), array ( '%s' ) );
						$wpdb->query ( "UPDATE $regular_board_boards SET board_date = '$current_timestamp' WHERE board_shortname = '$gotboards->board_shortname'" );
					}
				}
			}
		}
		

		echo '<div class="boardAll">
		<div class="spacer">';
		
		if ( $board_banner != '' ) {
			echo '<div class="banner"><img src="' . $board_banner . '" alt="Banner" /></div>';
		}		

		echo '<div class="navi">';
		
		echo '<a title="latest activity" href="' . $current_page . '"'; if ( $nothing_is_here ) { echo ' class="active"'; } echo '><i class="fa fa-home"></i><span>home</span></a>';
		
		echo '<a title="all topics" href="' . $current_page . '?a=topics"'; if ( $this_area == 'topics' || $the_board ) { echo ' class="active"'; } echo '><i class="fa fa-book"></i><span>topics</span></a>';
		if ( $enable_rep || $enable_url || $imgurid ) {
			echo '<a title="all images" href="' . $current_page . '?a=gallery"'; if ( $this_area == 'gallery' ) { echo ' class="active"'; } echo '><i class="fa fa-camera"></i><span>gallery</span></a>';
		}
		if ( $user_exists ) {
			echo '<a title="my profile" href="' . $current_page . '?a=history"'; if ( $this_area == 'history' ) { echo ' class="active"'; } echo '><i class="fa fa-user"></i><span>me</span></a>';
		}
		
		echo '<a title="options and other misc. stuff of importance" href="' . $current_page . '?a=stuff"'; 
		if ( $this_area == 'stuff' ||
			 $this_area == 'messages' || 
			 $this_area == 'options' || 
			 $this_area == 'blog' || 
			 $this_area == 'news' || 
			 $this_area == 'stats' || 
			 $this_area == 'mod' ) { 
			echo ' class="active"'; 
		} 
		echo '><i class="fa fa-cog"></i><span>stuff ';
		$my_alerts = $my_waitings + $my_unread;
		if ( $my_alerts > 0 ) { 
			echo ' <em>' . $my_alerts . ' alert(s)</em> '; }
		echo '</span></a>';

		if ( $user_exists) {
			echo '<a title="logout" href="' . $current_page . '?a=logout"';
			if ( $this_area == 'logout' ) {
				echo ' class="active"';
			}
			echo '><i class="fa fa-times-circle"></i><span>logout</span></a>';
		}
		if ( $search_enabled ) {
			$search_action = $current_page;
			echo '<form name="regular_board_search" method="post" action="' . $search_action . '">';
				wp_nonce_field('regular_board_search');
				echo '
				<input type="text" name="regular_board_search" id="regular_board_search" placeholder="Search" />
				<input type="submit" class="hidden" id="regular_board_search_submit" name="regular_board_search_submit" value="Search" />
			</form>';
		}
		echo '<small class="clear right">
		
		<i class="fa fa-user" title="You are using ' . $check_ammount . ' of ' . $accounts_per_ip . ' user slots available to you."> ' . $check_ammount . ' / ' . $accounts_per_ip . '</i>
		 &mdash; 
		<i class="fa fa-users" title="Accounts total"> ' . $count_users_total;
		if ( $user_total_allowed ) {
			echo ' / ' . $user_total_allowed; 
		}
		echo '</i>
		&mdash; 
		<i class="fa fa-pencil" title="Total posts created by users"> ' . $posts_users_total . '</i>
		</small>';
		
		echo '</div>';		
		
		if ( $userisbanned ) {
			echo '<div class="thread">';
			if ( $userisbanned ) {
				include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' );
			}
			echo '</div>';
		} elseif ( !$user_exists && !$userisbanned ) {
			if ( isset ( $_REQUEST['password'] ) && $_REQUEST['password'] ) { $password       = sanitize_text_field ( wp_hash ( $_REQUEST['password'] ) ); }
			if ( isset ( $_REQUEST['email'] ) && $_REQUEST['email'] )       { $username       = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) ); }
			
			/**
			 * If the associated IP has no infromation in the _user table, automatically 
			 * create an entry for it and refresh (whatever) page the user is on.
			 */
			 
			echo '<hr />
			<div id="reply" class="reply">';
				echo '<form enctype="multipart/form-data" name="i_want_to_log_in" method="post" action="' . $current_page . '">';
				wp_nonce_field('i_want_to_log_in');
				echo '<section><p><strong>Welcome!</strong><br /><small>Already have an account?  Log-in!</small>';
				if ( $registration_open ) {
					echo ' <small>New and need an account?  Simply enter your e-mail address and a password to get started.  ( Your 
					e-mail address is not stored in a readable format, and will never be shared.  This is simply for identification 
					purposes only. )</small>';
				}
				echo '</p>';
				echo '<input type="text" id="email" name="email" placeholder="you@that.com" />';
				echo '<input type="password" id="password" name="password" placeholder="Password" />';
				if ( $registration_open ) {
					echo '<input type="submit" name="i_want_to_log_in" value="Sign-in / Register" />';
				} else {
					echo '<input type="submit" name="i_want_to_log_in" value="Sign-in" />';
				}
				
				echo '</form>';

				if ( isset ( $_POST['i_want_to_log_in'] ) && isset ( $_REQUEST['password'] ) && $_REQUEST['email'] && isset ( $_REQUEST['email'] ) && $_REQUEST['password'] ) {
					if ( $check_ammount < $accounts_per_ip ) {
						$check_username = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_email = '$username' " );
						if ( $check_username ) {
							$check_password = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_email = '$username' AND user_password = '$password' " );
						}
						if ( $check_username ) {
							if ( $check_password ) {
								$wpdb->update (
									$regular_board_users,
									array ( 
										'user_logged_in'      => 1,
										'user_logged_in_from' => $user_ip
									),
									array ( 
										'user_email'      => $username,
										'user_password'  => $password
									),
									array ( 
										'%d', 
										'%s', 
										'%s', 
										'%s'
									)
								);
								echo '<meta http-equiv="refresh" content="0">';
							} else {
								echo '<p><center><small>Bad password attempt.  This has been recorded.</small></center></p>';
								$login_limit = $wpdb->get_results ( "SELECT * FROM $regular_board_bans WHERE banned_ip = '$user_ip' AND banned_message = 'bad password' LIMIT 1 " );
								if ( count ( $login_limit ) == 0 ) {
									$mute_count = 3;
									$wpdb->query (
										$wpdb->prepare (
											"INSERT INTO $regular_board_bans 
											( 
												banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length 
											) 
											VALUES ( 
												%d, %s, %s, %d, %s, %s 
											)",
										'', $current_timestamp, $user_ip, 3, 'bad password', '10 minutes' 
										)
									);
								}
								if ( count ( $login_limit ) > 0 ) {
									foreach ( $login_limit as $mute ) {
										if ( $mute->banned_banned == 3 ) { $banned_count = 2; }
										if ( $mute->banned_banned == 2 ) { $banned_count = 1; }
										$mute_count = $banned_count - 1;
										$wpdb->update (
											$regular_board_bans,
											array( 
												'banned_banned' => $banned_count
											),
											array( 
												'banned_ip' => $user_ip
											),
											array( 
												'%s'
											)
										);
									}
								}
							}
						} else {
								if ( $registration_open ) {
								$wpdb->query ( 
									$wpdb->prepare ( 
										"INSERT INTO $regular_board_users 
											( 
												user_id, 
												user_date, 
												user_ip, 
												user_name, 
												user_email, 
												user_password, 
												user_heaven, 
												user_boards, 
												user_follow, 
												user_avatar,
												user_posts,
												user_level,
												user_strikes,
												user_logged_in,
												user_logged_in_from
											) VALUES ( 
												%d, 
												%s, 
												%s, 
												%s, 
												%s, 
												%s, 
												%d, 
												%s, 
												%s,
												%s,
												%d,
												%d,
												%d,
												%d,
												%s
											)", 
										'', 
										$current_timestamp, 
										$user_ip, 
										'', 
										$username, 
										$password, 
										0, 
										'', 
										'', 
										'',
										0,
										0,
										0,
										1,
										$user_ip
									) 
								);
								echo '<meta http-equiv="refresh" content="0">';
							}
						}
					} else {
					echo '<center><small>You have too many registrations.</small></center>';
				}
				} 
			echo '</div>';
		} else {		
			if ( $this_area != 'post' ) {
				if ( !$this_thread ) {
					if ( $the_board || $correct == 0 && $this_area == 'newtopic' || $correct == 0 && $this_thread && count($getposts) > 0 || $nothing_is_here || $this_thread ) {
						echo '<hr />';
						if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
							include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
						} else {
							include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
						}
					}
				}
			}
		}		
		if ( $is_moderator || $is_user_mod ) {
			if ( count ( $get_reports ) > 0 || count ( $get_deleted ) > 0 || count ( $get_queue ) > 0 ) {
				echo '<hr />';
				
				echo '<strong>Moderation queue</strong>: ';
				
				if ( count ( $get_reports ) > 0 ) {
					echo '<a href="' . $current_page . '?a=reports">reports ( ' . count ( $get_reports ) . ' )</a>';
				}
				if ( count ( $get_deleted ) > 0 ) {
					echo '<a href="' . $current_page . '?a=deleted">deleted ( ' . count ( $get_deleted ) . ' )</a>';
				}
				if ( count ( $get_queue ) > 0 ) {
					echo '<a href="' . $current_page . '?a=queue">moderation queue ( ' . count ( $get_queue ) . ' )</a>';
				}
			}
		}		
		
		echo '<hr />';
		
		echo '<div class="left-half">';

		$total_posts = $wpdb->get_var ( "SELECT SUM(board_postcount) FROM $regular_board_boards" );
		echo '<div class="tag_cloud">';
		foreach ( $getboards as $gotboards ) {
			
			$board_post_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = '$gotboards->board_shortname' ");
			if ( !$board_post_count ) {
				$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = 0 WHERE board_shortname = '$gotboards->board_shortname'" );
			}
			
			if ( !$board_wipe_every ) {
				if( $gotboards->board_wipe && $gotboards->board_wipe != strtolower ( 'never' ) ) {
					$board_date = strtotime($gotboards->board_date);
					$today_is = strtotime($current_timestamp);
					if ( strpos ( strtolower ( $gotboards->board_wipe ), 'minute' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 60;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'hour' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 3600;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'day' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 86400;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'week' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 604800;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'month' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 2628000;
					} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'year' ) ) {
						$uptime = intval ( $gotboards->board_wipe ) * 31536000;
					} else {
						$uptime = intval ( $gotboards->board_wipe ) * 60;
					}
					$board_life = ( intval ( $board_date ) + intval ( $uptime ) );
					$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_date ) ) );
					$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
					if($today_is > $board_life){
						$wpdb->delete ( $regular_board_posts, array ( 'post_board' => $gotboards->board_shortname ), array ( '%s' ) );
						$wpdb->query ( "UPDATE $regular_board_boards SET board_date = '$current_timestamp' WHERE board_shortname = '$gotboards->board_shortname'" );
					}
				}
			}
			if ( $gotboards->board_postcount > 0 ) {
				$percent = regular_board_percent ( $gotboards->board_postcount, $total_posts );
			} else {
				$percent = 0;
			}
			if ( $percent == 0 ) { $percent = 10; }
			elseif ( $percent >= 1 && $percent <= 10 )   { $percent = 11; }
			elseif ( $percent >= 11 && $percent <= 20 )  { $percent = 12; }
			elseif ( $percent >= 21 && $percent <= 30 )  { $percent = 13; }
			elseif ( $percent >= 31 && $percent <= 40 )  { $percent = 14; }
			elseif ( $percent >= 41 && $percent <= 50 )  { $percent = 15; }
			elseif ( $percent >= 51 && $percent <= 60 )  { $percent = 16; }
			elseif ( $percent >= 61 && $percent <= 70 )  { $percent = 17; }
			elseif ( $percent >= 71 && $percent <= 80 )  { $percent = 18; }
			elseif ( $percent >= 81 && $percent <= 90 )  { $percent = 19; }
			elseif ( $percent >= 91 && $percent <= 100 ) { $percent = 20; }
			echo '<span '; if ( $percent == 10 ) { echo 'class="nothing" '; } echo 'style="font-size:' . $percent . 'px;"><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '"'; if ( $the_board && $the_board == $gotboards->board_shortname ) { echo ' class="active"'; } echo '>';
			echo $gotboards->board_name . '</a></span>';
		}
		echo '<span><a href="' . $this_page . '?a=replies"'; if ( $this_area == 'replies' ) { echo ' class="active"'; } echo '>all replies</a></span>
		<span><a href="' . $this_page . '?a=subscribed"'; if ( $this_area == 'subscribed' ) { echo ' class="active"'; } echo '>all subscribed</a></span>
		<span><a href="' . $this_page . '?a=following"'; if ( $this_area == 'following' ) { echo ' class="active"'; } echo '>all followed</a></span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="right-half">';
		
		if ( $nothing_is_here ) {
			if ( !$search ) {
				if ( get_option ( 'regular_board_frontpage' ) ) {
					echo '<div class="thread_container"><span class="frontinfo">Welcome</span>' . wpautop ( get_option ( 'regular_board_frontpage' ) ) . '</div>';
				echo '<hr />';
				}

			}
			if ( $getposts ) {
				echo '<div class="thread_container">
					<span class="frontinfo">';
						if ( !$search ) {
							echo 'Latest activity';
						} else {
							echo 'Search results';
						}
					echo '</span>';
					if ( count ( $getposts ) > 0 ) {
						foreach ( $getposts as $posts ) {
							if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
								include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
							} else {
								include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
							}
						}
						include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
					}
				} else {
					echo '<div class="thread_container">
						<span class="frontinfo">No activity to show</span>
				</div>';
			}
			echo '</div>';
		}

		if ( $this_area == 'newtopic' ) {
			if ( count ( $getboards ) == 1 ) {
				foreach ( $getboards as $board ) {
					$the_board = $board->board_shortname;
				}
			}
		}
		include ( plugin_dir_path(__FILE__) . '/regular_board_posting_deletepost.php' );
		if ( $this_area == 'deleted' ) {
			if ( $is_moderator || $is_user_mod ) {
				foreach ( $get_deleted as $posts ) {
					if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
						include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
					} else {
						include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
					}
					include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
				}
			}
		} elseif ( $this_area == 'queue' ) {
			if ( $is_moderator || $is_user_mod ) {
				foreach ( $get_queue as $posts ) {
					if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
						include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
					} else {
						include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
					}
					include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
				}
			}
		} elseif ( $this_area == 'reports' ) {
			if ( $is_moderator || $is_user_mod ) {
				foreach ( $get_reports as $posts ) {
					if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
						include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
					} else {
						include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
					}
					include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
				}
			}
		} elseif ( $this_area == 'editpost' && $user_exists ) {
			if ( $this_area = 'editpost' && $this_thread && !$the_board ) {
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php' );
			}
		} elseif ( $this_area == 'options' && $user_exists ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_user_options.php' );
		} elseif ( $this_area == 'history'  && $user_exists || $this_user ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_profile_loop.php' );
		} elseif ( $this_area == 'stats' ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_board_stats.php' );
		} elseif ( $the_board || $this_thread ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_posting_checkflood.php' );
				if ( count ( $get_current_board ) > 0 || $this_thread ) {
					if ( !$user_logged_in && $require_logged == 1 ) {
						echo '<div class="thread"><p>You are not logged in.</p></div>';
					} elseif ( !$user_logged_in && $require_logged == 0 || $user_logged_in ) {
						if ( count ( $get_current_board ) > 0 ) {
							foreach ( $get_current_board as $gotCurrentBoard ) {
								$boardName = $gotCurrentBoard->board_name;
								$boardShort = $gotCurrentBoard->board_shortname;
							}
						}
						if ( $this_thread ) {
							$currentCountNomber = count ( $countParentReplies );
						}
						if ( isset($_POST['FORMSUBMIT'] ) ) {
							$img = $_FILES['img'];
							if ( $_FILES['img']['size'] != 0 ) {
								if ( $img['name'] ) {
									$filename  = $img['tmp_name'];
									$client_id = "$imgurid";
									$handle    = fopen ( $filename, "r" );
									$data      = fread ( $handle, filesize( $filename ) );
									$pvars     = array ( 'image' => base64_encode ( $data ) );
									$timeout   = 30;
									$curl      = curl_init();
									curl_setopt ( $curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json' );
									curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout );
									curl_setopt ( $curl, CURLOPT_HTTPHEADER, array ( 'Authorization: Client-ID ' . $client_id ) );
									curl_setopt ( $curl, CURLOPT_POST, 1 );
									curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
									curl_setopt ( $curl, CURLOPT_POSTFIELDS, $pvars );
									$out       = curl_exec ( $curl );
									curl_close ( $curl );
									$pms       = json_decode( $out, true );
									$URL       = $pms['data']['link'];
									$TYPE      = 'image';
								}
							}else{
								$URL = sanitize_text_field ( wp_strip_all_tags ( $_REQUEST['URL'] ) );
							}
							include ( plugin_dir_path(__FILE__) . '/regular_board_post_action.php' );
						}
						if ( !isset ( $_POST['FORMSUBMIT'] ) ) {
							if ( $this_area == 'editpost' ) { 
								if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
									include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
								} else {
									include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
								}
							}
							if ( $this_area != 'newtopic' && $correct != 3 ) {
							
								if ( $the_board && !$this_thread ) {
									$website_url = $current_page . '?b=' . $the_board; 
								} elseif ( $this_thread ) { 
									$website_url = $current_page . 't=' . $this_thread; 
								}
								
								if ( $board_name ) {
									echo '<div class="thread"><strong>/' . $board_short . '/ ' . $board_name . '</strong> &mdash; ' . $board_description . '</div>';
								}
								
								if ( $totalpages > 0 ) {
									include ( plugin_dir_path(__FILE__) . '/regular_board_board_loop.php' );
								} else {
									echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
								}
							}
							if ( $this_thread && $threadexists == 1 ) {
								echo '<p>';
								if ( $thisboard ) {
									echo '<a href="' . $current_page . '">Return</a>';
								} elseif ( $the_board ) {
									echo '<a href="' . $current_page . '?b=' . $the_board . '">Return</a>';
								} elseif ( $thread_board ) {
									echo '<a href="' . $current_page . '?b=' . $thread_board . '">Return</a>';
								} else {
									echo '<a href="' . $current_page . '">Return</a>';
								}								
								echo '<a href="#top">Top</a><a class="reload" xdata="' . $this_thread .'" data="' . $current_page . '?t=' . $this_thread . '">Refresh</a>
								</p>';
							}
						}
					}
				}
		} elseif ( $this_area == 'post' ) {
			echo '<div id="post">';
			if ( isset ( $_POST['FORMSUBMIT'] ) ) {			
				$img = $_FILES['img'];
				if ( $_FILES['img']['size'] != 0 ) {
					if ( $img['name'] ) {
						$filename  = $img['tmp_name'];
						$client_id = "$imgurid";
						$handle    = fopen ( $filename, "r" );
						$data      = fread ( $handle, filesize ( $filename ) );
						$pvars     = array ( 'image' => base64_encode ( $data ) );
						$timeout   = 30;
						$curl      = curl_init();
						curl_setopt ( $curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json' );
						curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout);
						curl_setopt ( $curl, CURLOPT_HTTPHEADER, array ( 'Authorization: Client-ID ' . $client_id ) );
						curl_setopt ( $curl, CURLOPT_POST, 1 );
						curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
						curl_setopt ( $curl, CURLOPT_POSTFIELDS, $pvars );
						$out       = curl_exec ( $curl );
						curl_close ( $curl );
						$pms       = json_decode ( $out,true );
						$URL       = $pms['data']['link'];
						$TYPE      = 'image';
					}
				}else{
					$URL = sanitize_text_field ( wp_strip_all_tags( $_REQUEST['URL'] ) );
				}
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_action.php' );
			}
			echo '</div>';
		} elseif ( $this_area == 'gallery' || $this_area == 'replies' || $this_area == 'topics' || $this_area == 'subscribed' || $this_area == 'following' ) {
			echo '<h1>' . $this_area . '</h1>';
			if ( $getposts ) {
				if ( count ( $getposts ) > 0 ) {
					foreach ( $getposts as $posts ) {
						if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
							include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
						} else {
							include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
						}
					}
					include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
				}
			} else {
				echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
			}
		} elseif ( $this_area == 'news' ) {
			if ( $announcements ) {
				echo '<h3><center>Announcements</center></h3>';
				if ( isset ( $_GET['post'] ) ) {
					$postno = intval ( $_GET['post'] );
				}
				$cat_args=array(
				'include' => intval ( $announcements )
				);
				$categories=get_categories($cat_args);
				foreach($categories as $category) {
					if ( $postno ) {
						$args=array(
						'p'=> $postno,
						);					
					} else {
						$args=array(
						'showposts' => -1,
						'category__in' => array ( $category->term_id ),
						);
					}
					$posts = get_posts ( $args );
					if ( $posts ) {
						if ( $postno ) {
							echo '<div class="thread"><a href="' . $this_page . '?a=news">More site announcements</a></div>';
						}
						foreach($posts as $post) {
							setup_postdata($post); 
								echo '<div class="thread"><strong class="left">';
								echo '<a href="' . $this_page . '?a=news&amp;post=' . $post->ID . '">' . $post->post_title . '</a>';
								echo '</strong>';
								echo '<span class="right">' . regular_board_timesince( $post->post_date ) . '</span>';
								if ( $postno ) { 
									echo '<hr />' . wpautop ( $post->post_content ) . '<hr /><em>posted by</em> ';
									the_author();
								}
								echo '</div>';
								if ( $postno ) {
									echo '<hr />';
								}
						}
					} else {
						echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
					}
				}
			}		
		} elseif ( $enable_blog && $this_area == 'blog' ) {
			echo '<h3><center>Blog</center></h3>';
			if ( isset ( $_GET['post'] ) ) {
				$postno = intval ( $_GET['post'] );
			}
			$cat_args=array(
			'include' => intval ( $announcements )
			);
			$categories=get_categories($cat_args);
			foreach($categories as $category) {
				
				if ( $postno ) {
					$args=array(
					'p'=> $postno,
					);					
				} elseif ( $announcements ) {
					$args=array(
					'showposts' => -1,
					'category__not_in' => array ( $category->term_id ),
					);
				} else {
					$args=array(
					'showposts' => -1
					);
				}
				$posts = get_posts ( $args );
				if ( $posts ) {
					if ( $postno ) {
						echo '<div class="thread"><a href="' . $this_page . '?a=blog">More blog entries</a></div>';
					}
					foreach($posts as $post) {
						setup_postdata($post); 
							echo '<div class="thread"><strong class="left">';
							echo '<a href="' . $this_page . '?a=news&amp;post=' . $post->ID . '">' . $post->post_title . '</a>';
							echo '</strong>';
							echo '<span class="right">' . regular_board_timesince( $post->post_date ) . '</span>';
							if ( $postno ) { 
								echo '<hr />' . wpautop ( $post->post_content ) . '<hr /><em>posted by</em> ';
								the_author();
							}
							echo '</div>';
							if ( $postno ) {
								echo '<hr />';
							}
					}
				} else {
					echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
				}
			}		
		} elseif ( $this_area == 'mod' ) {
			if ( count ( $mod_logs ) > 0 ) {
				echo '<div class="thread"><div class="right">Age</div><div class="left">Message</div></div>';
				foreach ( $mod_logs as $logs ) {
					echo '<div class="thread">';
					
					echo '<div class="right">' . regular_board_timesince( $logs->logs_date ) . '</div><div class="left">' . $logs->logs_message . '</div>';
					
					echo '</div>';
				}
			} else { 
				echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
			}
		} elseif ( $this_area == 'stuff' ) {
			echo '<div class="thread_container">
			<h1>stuff</h1>
			<div class="container_half">
				<em>Tools/info</em>:
				<ul>';
				if ( $user_exists ) {
					echo '<li><a href="' . $current_page . '?a=messages">messages</a> &mdash; you have ' . $my_unread . ' unread messages.</li>';
					echo '<li><a href="' . $current_page . '?a=options">options</a> &mdash; your personal settings / you have ' . $my_waitings . ' connections pending.</li>';
				}
				if ( $enable_blog ) {
					echo '<li><a href="' . $current_page . '?a=blog">blog</a> &mdash; words and thoughts</li>';
				}
				if ( $announcements ) {
					echo '<li><a href="' . $current_page . '?a=news">news</a> &mdash; announcements and site news</li>';
				}
				echo '
				<li><a href="' . $current_page . '?a=stats">stats</a> &mdash; board statistics</li>
				<li><a href="' . $current_page . '?a=mod">moderation log</a></li>
				</ul>
			</div>
			<div class="container_half">
			<em>Active boards</em>:
			<ul>';
			foreach ( $getboards as $gotboards ) {
				if ( $gotboards->board_postcount > 0 ) {
					echo '<li><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_shortname . '</a></li>';
				}
			}
			echo '</ul>
			</div>';
			
		} elseif ( $this_area == 'messages' && $user_exists ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_messages.php' );
		} elseif ( $this_area == 'logout' && $user_exists ) {
			$wpdb->update (
				$regular_board_users,
				array ( 
					'user_logged_in'      => 0,
					'user_logged_in_from' => ''
				),
				array ( 
					'user_id'  => $profileid
				),
				array ( 
					'%d', 
					'%s', 
					'%d'
				)
			);
			echo '<meta http-equiv="refresh" content="0;' . $current_page . '">';
		}
	echo '</div></div></div>';
	}
}