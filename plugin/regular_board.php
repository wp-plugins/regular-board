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
	global $wp, $post, $wpdb;
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
	global $wpdb, $wp, $post, $ipaddress, $random_password, $regular_board_version;
	
	/**
	 * Display Regular Board content if IP address is valid.
	 */
	 
	if ( $ipaddress !== false ) {
		
		/**
		 * Variables used throughout the plugin.
		 */

        $regular_board_posts   = $wpdb->prefix . 'regular_board_posts';
		$regular_board_boards  = $wpdb->prefix . 'regular_board_boards';
		$regular_board_users   = $wpdb->prefix . 'regular_board_users';
		$regular_board_bans    = $wpdb->prefix . 'regular_board_bans';
		$regular_board_logs    = $wpdb->prefix . 'regular_board_logs';
		 
		$user_logged_in        = 0;
		if ( is_user_logged_in() ) {
			$user_logged_in    = 1;
		}
		$user_exists           = 0;
		$require_logged        = 0;
		$post_nom              = 0;
		$wipe_countdown        = '';
		$LOCKED                = '';
		$checkLOCK             = '';
		$query                 = '';
		$profile_name          = '';
		$profile_email         = '';
		$search                = '';
		$board_id              = '';
		$board_name            = '';
		$board_short           = '';
		$board_description     = '';
		$board_mods            = '';
		$board_jans            = '';
		$board_posts           = '';
		$the_board             = '';
		$thisboard             = '';
		$this_area             = '';
		$this_user             = '';
		$this_thread           = '';
		$results               = '';
		$usermod               = '';
		$is_moderator          = '';		
		$is_user_janitor       = '';
		$lock                  = '';
		$timegateactive        = '';
		$correct               = '';
		$getposts              = '';
		$gotReplies            = '';
		$banned_count          = '';
		$entered_parent        = 0;
		$display_wipe          = get_option ( 'regular_board_wipedisplay' );
		$banned_image          = get_option ( 'regular_board_bannedimage' );
		$board_banner          = get_option ( 'regular_board_boardbanner' );
		$blog_title            = get_bloginfo();
		
		$board_wipe_every      = get_option ( 'regular_board_wipeall' );
		$board_wipe_date       = get_option ( 'regular_board_wipealldate' );
		$current_timestamp     = date ( 'Y-m-d H:i:s' );		
		if ( $board_wipe_every && $board_wipe_ever != strtolower ( 'never' ) ) {
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
			} else {
				$uptime = intval ( $board_wipe_every ) * 60;
			}
			$board_life = ( intval ( $board_wipe_date ) + intval ( $uptime ) );
			$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
			$wipe = number_format ( intval ( $today_is ) - intval ( $board_wipe_date ) ) / intval ( $uptime ) * 100;
			if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
				$wipe_countdown = '<span class="wipe" data-timer="' . $next_wipe . '"></span>';
			}
			if($today_is > $board_life){
				$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = 0 WHERE board_id > 0" );			
				$wpdb->delete ( $regular_board_posts, array ( 'post_userid' => 1 ), array ( '%d' ) );
				$wpdb->delete ( $regular_board_posts, array ( 'post_userid' => 2 ), array ( '%d' ) );
				$wpdb->delete ( $regular_board_posts, array ( 'post_userid' => 3 ), array ( '%d' ) );
				update_option ( 'regular_board_wipealldate', str_replace ( '\\', '', $today_is ) );
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
		$boards                = get_option ( 'regular_board_boards' );
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
		 
		$getuser     = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_bans WHERE banned_ip = %s AND banned_banned = %d LIMIT 1", $user_ip, 1  ) );
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
		 
		$myinformation = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_ip = %s LIMIT 1", $user_ip ) );
		if ( count ( $myinformation ) > 0 ) {
			foreach ( $myinformation as $myinfo ) {
				$profileavatar       = sanitize_text_field ( $myinfo->user_avatar );
				$profileslogan       = sanitize_text_field ( str_replace ( '\\', '', $myinfo->user_slogan ) );
				$profileid           = intval ( $myinfo->user_id );
				$profileheaven       = intval ( $myinfo->user_heaven );
				$profile_email       = sanitize_text_field ( $myinfo->user_email );
				$profile_name        = sanitize_text_field ( $myinfo->user_name );
				if ( !$profile_name ) {
					$profile_name    = 'anonymous';
				}
				$profilepassword     = sanitize_text_field ( $myinfo->user_password );
				$profilefollow       = sanitize_text_field ( $myinfo->user_follow );
				$following           = sanitize_text_field ( $myinfo->user_follow );
				$boards              = sanitize_text_field ( $myinfo->user_boards );
				$profileboards       = sanitize_text_field ( $myinfo->user_boards );
				$following           = sanitize_text_field ( $profilefollow );
				$user_exists         = 1;
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
				
				
				
				
				
			}
		}
	
		/**
		 * Get all boards
		 * If there is only one, set that board as the main board so we can do things like post new topics from anywhere on the install.
		 */
		 
		$getboards = $wpdb->get_results ( "SELECT * FROM $regular_board_boards WHERE board_shortname != '' ORDER BY board_name ASC" );
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
		
		/**
		 * If there is only one board, get results as if it's the current board.
		 */
		 
		if ( !$the_board && $thisboard ) {
			$get_current_board  = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thisboard ) );
		}
		
		/**
		 * Our search input
		 */
		 
		if ( $search_enabled && isset ( $_POST['regular_board_search_submit'] ) && $_REQUEST['regular_board_search'] ) {
			$search = esc_sql ( str_replace ( '\'', '\\\'', $_REQUEST['regular_board_search'] ) );
		}
		
		/**
		 * If we're browsing a board, let's get all of the information associated with that board 
		 * from _boards.
		 */
		 
		if ( $the_board ) {
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
						$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_date ) ) );
						$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
						$next_clean = date($boarddate, time() + $next_wipe);
	
						if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
							$wipe_countdown = '<span class="wipe" data-timer="' . $next_wipe . '"></span>';
						}
					}
				}
				
				if ( $board_wipe_every ) {
					$wipe_countdown = '';
				}
				
				if( $board_description ) {
					$boardheader      = '<p class="boardheader"><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . '</a>' . $board_description . $wipe_countdown . '</p>';
				}
				if( !$board_description ) {
					$boardheader      = '<p class="boardheader"><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . '</a>' . $wipe_countdown;
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
		if ( $the_board && !$this_thread) { 
			$use_this++;
			if ( !$search ) {
				$where_by = "WHERE post_board = '$the_board' AND post_parent = 0"; 
				$order_by = "post_sticky DESC, post_last DESC";
			}
			if ( $search_enabled && $search ) {
				$where_by = "WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_board = '$the_board'";
			}
		}
		if ( $this_area == 'topics' || !$the_board && !$this_area && !$this_user) {
			$use_this++;
			$where_by = "WHERE post_parent = 0";
		}
		if( !$the_board && $this_area == 'replies' && !$this_thread && !$this_user ){
			$use_this++;
			$where_by = "WHERE post_parent != 0";
		}
		if( $nothing_is_here ) {
			$use_this++;
			$where_by = "";
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
			$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_parent = %d", $this_thread ) );
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
			$use_this++;
			$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_name = %s LIMIT 1", $profileid, $this_user ) );
			$where_by = "WHERE post_name = '$this_user'";
			$order_by = "post_date DESC";
		}
		if ( $use_this > 0 ) {
			$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by" );
			if ( $totalpages > 0 ) {
				if ( strpos ( strtolower ( $query ), 'n=' ) ) {
					$results    = intval ( $_GET['n'] );
				}
				if( $results ) {
					$start = ( $results - 1 ) * $posts_per_page;
				} else {
					$start = 0;
				}
				$getposts = $wpdb->get_results( "SELECT * FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
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
			$get_reports = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_reportcount > %d OR post_public = %d", 0, 2 ) );
			$get_deleted = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_public = %d", 3 ) );
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

		echo '<div class="boardAll">';
		echo '<div class="banner"><img src="' . $board_banner . '" alt="Banner" /></div>';
		
		if ( count ( $myinformation ) == 0 ) {
			
			/**
			 * If the associated IP has no infromation in the _user table, automatically 
			 * create an entry for it and refresh (whatever) page the user is on.
			 */
			 
			echo '<div class="registration">';
			
				echo '<form class="i_am_a_human" enctype="multipart/form-data" name="i_am_a_human" method="post" action="' . $current_page . '">
				<p><center>New here?  Just click the button to begin using the boards.</center></p>';
				wp_nonce_field('i_am_a_human');
				echo '<input type="submit" name="i_am_a_human" value="Finish automatic registration" />
				</form>';
				if ( isset ( $_POST['i_am_a_human'] ) ) {
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
									user_strikes
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
									%d
								)", 
								'', 
								$current_timestamp, 
								$user_ip, 
								'', 
								'', 
								'', 
								0, 
								'', 
								'', 
								'',
								0,
								0,
								0
							) 
						);
					echo '<meta http-equiv="refresh" content="0">';			
				}
			echo '</div>';
		}
		
		if ( $nothing_is_here ) {
			if ( get_option ( 'regular_board_frontpage' ) ) {
				echo '<div class="thread"><span class="frontinfo">Welcome</span>' . wpautop ( get_option ( 'regular_board_frontpage' ) ) . '</div>';
			}
			
			echo '<div class="navi">';
			echo '<span>[';
			if ( $user_exists && $profileboards ) {
				echo ' <a href="' . $current_page . '?a=subscribed">subscribed</a>';
			}
			if ( $user_exists && $profilefollow ) {
				echo ' <a href="' . $current_page . '?a=following">following</a>';
			}
			echo ' <a href="' . $current_page . '?a=topics">topics</a>
			<a href="' . $current_page . '?a=replies">replies</a>';
			if ( $enable_rep || $enable_url || $imgurid ) {
				echo '<a href="' . $current_page . '?a=gallery">gallery</a>';
			}
			if ( $user_exists ) {
				echo ' <a href="' . $current_page . '?a=history">history</a>';
			}
			if ( $is_moderator && count($get_reports) > 0 ) {
				echo '<a href="' . $current_page . '?a=reports">reports ( ' . count ( $get_reports ) . ' )</a>';
			}
			if ( $is_moderator && count($get_deleted) > 0 ) {
				echo '<a href="' . $current_page . '?a=deleted">deleted ( ' . count ( $get_deleted ) . ' )</a>';
			}
			echo ' <a href="' . $current_page . '?a=stuff">stuff</a>';
			echo ']</span>';
			echo '</div>';

			echo '<div class="thread">';
			if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
				include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
			} else {
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
			}			
			echo '</div>';
			
			if ( count ( $getboards ) > 0 ) {
				echo '<div class="thread"><span class="frontinfo">Boards</span>';
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
					if ( $gotboards->board_postcount > 0 ) {
						echo ' <a class="boardlink" href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_name . ' ( ' . $gotboards->board_postcount . ' )</a>';
					}
				}
				echo '</div>';
			}
			echo '<div class="thread">'. $wipe_countdown . '</div>';
			if ( $getposts ) {
				echo '<div class="thread">
					<span class="frontinfo">Latest activity</span>';
					if ( count ( $getposts ) > 0 ) {
						foreach ( $getposts as $posts ) {
							if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
								include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
							} else {
								include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
							}
						}
					}
				} else {
					echo '<div class="thread">
						<span class="frontinfo">No activity to show</span>
				</div>';
			}
			
		}

		if ( $the_board && !$this_thread || $thisboard && !$this_thread) { 
			if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
				include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
			} else {
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
			}
		}

		echo $boardheader;

		if ( count ( $getboards ) > 0 && !$nothing_is_here ) {
			echo '<div class="navi">';
			echo '<span>[';
			echo ' <a href="' . $current_page . '">home</a> ';
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
			echo ']</span>';
			echo '<span>[';

			if ( $user_exists && $profileboards ) {
				echo ' <a href="' . $current_page . '?a=subscribed">subscribed</a>';
			}
			if ( $user_exists && $profilefollow ) {
				echo ' <a href="' . $current_page . '?a=following">following</a>';
			}
			echo ' <a href="' . $current_page . '?a=topics">topics</a>
			<a href="' . $current_page . '?a=replies">replies</a>';
			if ( $enable_rep || $enable_url || $imgurid ) {
				echo '<a href="' . $current_page . '?a=gallery">gallery</a>';
			}
			if ( $user_exists ) {
				echo ' <a href="' . $current_page . '?a=history">history</a>';
			}
			if ( $is_moderator && count($get_reports) > 0 ) {
				echo '<a href="' . $current_page . '?a=reports">reports ( ' . count ( $get_reports ) . ' )</a>';
			}
			if ( $is_moderator && count($get_deleted) > 0 ) {
				echo '<a href="' . $current_page . '?a=deleted">deleted ( ' . count ( $get_deleted ) . ' )</a>';
			}
			echo ' <a href="' . $current_page . '?a=stuff">stuff</a>';
			echo ']</span>';
			if ( $this_area != 'newtopic' && $user_exists ) {
				echo '<span>';
				if ( $user_exists) {
					if ( !$the_board ) {
						
					} else {
						echo '<a class="newtopic" href="' . $current_page . '?b=' . $the_board . '&amp;a=newtopic">new</a> ';
					}
					echo '<span class="hidden notopic">cancel</span>';
				}
				echo '</span>';
			}
			echo '</div>';
		}
		echo '<p class="newtopic"></p>';

		
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
				}
			}
		} elseif ( $this_area == 'editpost' && $user_exists ) {
			if ( $this_area = 'editpost' && $this_thread && !$the_board ) {
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php' );
			}
		} elseif ( $this_area == 'options' && $user_exists ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_user_options.php' );
		} elseif ( $this_area == 'history' || $this_user ) {
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
								if ( $search_enabled ) {
									echo '<form name="regular_board_search" method="post" action="' . $website_url . '">';
										wp_nonce_field('regular_board_search');
										echo '
										<input type="text" name="regular_board_search" id="regular_board_search" placeholder="Search ';
										if ( !$this_thread ) { 
											echo $the_board;
										} elseif ( $this_thread ) { 
											echo 'this thread';
										} 
										echo 
										' for..." />
										<input type="submit" class="hidden" id="regular_board_search_submit" name="regular_board_search_submit" value="Search" />
									</form>';
								}										
								if ( $totalpages > 0 ) {
									include ( plugin_dir_path(__FILE__) . '/regular_board_board_loop.php' );
								} else {
									echo '<div class="thread"><center><em>Nothing to see here.</em></center></div>';
								}
							}
							if ( $this_thread && $threadexists == 1 ) {
								echo '<p>';
								if ( $thisboard ) {
									echo '<a href="' . $current_page . '">Return</a>';
								} else {
									echo '<a href="' . $current_page . '?b=' . $the_board . '">Return</a>';
								}
								echo '<a href="#top">Top</a><a class="reload" data="' . $current_page . '?t=' . $this_thread . '">Update</a>
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
			if ( $getposts ) {
				if ( count ( $getposts ) > 0 ) {
					foreach ( $getposts as $posts ) {
						if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
							include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
						} else {
							include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
						}
					}
				}
			} else {
				echo '<center><em>Nothing to see here.</em></center>';
			}
		} else if ( $this_area == 'news' ) {
			if ( $announcements ) {
				echo '<h3><center>Announcements</center></h3>';
				$blog_total = get_term_by('id',$announcements,'category');
				$blog_total = $blog_total->count;

				$cat_args=array(
				'include' => intval ( $announcements )
				);
				$categories=get_categories($cat_args);
				foreach($categories as $category) {
					
					$show_posts     = 3;
					
					if ( !isset ( $_GET['n'] ) ) {
						$n              = 1;
						$current_offset = 0;
					} elseif ( isset ( $_GET['n'] ) ) {
						$n              = intval ( $_GET['n'] );
					}
					
					if ( isset ( $_GET['n'] ) && $_GET['n'] != 1 ) {
						$current_offset = ( $n * $show_posts );
					} elseif ( isset ( $_GET['n'] ) && $_GET['n'] == 1 ) {
						$current_offset = 0;
					} else {
						$current_offset = 0;
					}
					
					$total_pages = round ( $blog_total / $show_posts );
					if ( $total_pages ) {
						if ( isset ( $_GET['n'] ) ) {
							$n = intval ( $_GET['n'] );
							if ( $n < $total_pages ) {
								echo '<a class="right" href="' . $this_page . '?a=news&amp;n=' . ( $n + 1 ) . '">Next page</a>';
							}
							if ( $n > 1 && $n <= $total_pages ) {
								echo '<a class="left" href="' . $this_page . '?a=news&amp;n=' . ( $n - 1 ) . '">previous page</a>';
							}
						}
						echo '<hr />';
					}
					$args=array(
					'offset' => $current_offset,
					'showposts' => $show_posts,
					'category__in' => array ( $category->term_id ),
					'ignore_sticky_posts'=> 1
					);
					$posts = get_posts ( $args );
					if ( $posts ) {
						foreach($posts as $post) {
							setup_postdata($post); 
								echo '<div class="thread"><h5>';
								the_title_attribute();
								echo '</h5><hr />';
								str_replace ( '<img class="', '<img class="imageOP', the_content() );
								echo '<hr />';
								the_date();
								echo ' &mdash; ';
								the_author();
								echo '</div>';
						}
					} else {
						echo '<div class="thread"><h5>Nothing to see here.</h5></div>';
					}
					if ( $total_pages ) {
						echo '<hr />';
						if ( isset ( $_GET['n'] ) ) {
							$n = intval ( $_GET['n'] );
							if ( $n < $total_pages ) {
								echo '<a class="right" href="' . $this_page . '?a=news&amp;n=' . ( $n + 1 ) . '">Next page</a>';
							}
							if ( $n > 1 && $n <= $total_pages ) {
								echo '<a class="left" href="' . $this_page . '?a=news&amp;n=' . ( $n - 1 ) . '">previous page</a>';
							}
						}
					}
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
				echo '<div class="thread"><h5>Nothing to see here.</h5></div>';
			}
		} elseif ( $this_area == 'stuff' ) {
			echo '<div class="thread">
			<div class="container_half">
				<em>Tools/info</em>:
				<ul>';
				if ( $user_exists ) {
					echo '<li><a href="' . $current_page . '?a=options">options</a> &mdash; your personal settings</li>';
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
			
		}
	echo '</div>';
	}
}