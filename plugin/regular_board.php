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

function regular_board_head ( ) {
	global	$wp, $post, $wpdb, $regular_board_posts_select;

	$noindexboards = $the_board = '';
	$content = $post->post_content;
	$regular_board_posts = $wpdb->prefix . 'regular_board_posts';
	$regular_board_boards = $wpdb->prefix . 'regular_board_boards';			
	
	if ( isset ( $_GET['b'] ) ) {
		$the_board = sanitize_text_field ( $_GET['b'] );
	}	
	if ( isset ( $_GET['t'] ) ) {
		$this_thread = intval ( $_GET['t'] );
		if ( $this_thread ) {
			$the_board = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
		}
	}
	
	if ( has_shortcode ( $content, 'regular_board' ) ) {
		include ( plugin_dir_path(__FILE__) . '/regular_board_meta.php' );
		if ( get_option ( 'regular_board_robots' ) ) {
			echo '<meta name="robots" content="noindex,nofollow"/>';
		}
		if ( $the_board ) {
			$noindexboards = explode ( ',', get_option ( 'regular_board_noindexboards' ) );
			if ( in_array ( $the_board, $noindexboards ) ) {
				echo '<meta name="robots" content="noindex,nofollow"/>';
			}
		}		
	}
}

function regular_board_shortcode ( ) {

	global	$wpdb, $wp, $post, $ipaddress, $random_password, $regular_board_version, $regular_board_posts_select, $regular_board_users_select, $regular_board_boards_select, $regular_board_bans_select;
	
	// @ _ip_functions | inet_pton determined IP address to be valid / was not found on DNSBL
	if ( $ipaddress !== false ) { 









		// Variables used throughout ( begin )
		
			// tables
			$regular_board_posts = $wpdb->prefix . 'regular_board_posts';
			$regular_board_boards = $wpdb->prefix . 'regular_board_boards';
			$regular_board_users = $wpdb->prefix . 'regular_board_users';
			$regular_board_bans = $wpdb->prefix . 'regular_board_bans';
			$regular_board_logs = $wpdb->prefix . 'regular_board_logs';
			$regular_board_messages = $wpdb->prefix . 'regular_board_messages';
			$regular_board_friends = $wpdb->prefix . 'regular_board_friends';

			// default board style
			$style = 'tiny';
			
			// @tables || selection parameters 
			$regular_board_messages_select = 'messages_id, messages_date, messages_subject, messages_content, messages_to, messages_from, messages_read';
			$regular_board_friends_select  = 'friends_id, friends_connector, friends_connectee, friends_mutual';
			
			// 
			$nsfw_image = plugins_url() . '/regular-board/system/css/nsfw.jpg';
			$allowed_types = array ( 'jpg', 'gif', 'jpeg', 'png' );
			
			// set defaults
			$countParentReplies = $totalpages = $this_thread = $post_count = $profile_level = $nothing_is_here = $profileid = $user_level_plus_one = $userisbanned = $id_display = $user_create = $posts_per_page = $formatting = $auto_url = $announcements = $max_text = $max_replies = $max_body = $enable_rep = $enable_url = $search_enabled = $max_links = $regular_board_registration = $enable_blog = $display_wipe = $user_exists = $require_logged = $post_nom = $my_unread = $my_waitings = $entered_parent = 0;
			$this_title = $the_board = $this_area = $post_title = $this_page = $get_queue = $regular_board_board = $profileboards = $following = $where_by = $protectedboards = $protected_boards = $profilepassword = $board_current = $thread_board = $get_current_board = $getboards = $the_tag = $banned_content = $selfpost = $user_flood = $imgurid = $archive_gate = $flood_gate = $these_boards = $board_wipe_every = $board_wipe_per = $board_wipe_date = $blog_title = $board_banner = $banned_image = $regular_board_footer = $wipe_countdown = $LOCKED = $checkLOCK = $query = $profile_name = $profile_email = $search = $board_id = $board_name = $board_short = $board_description = $board_mods = $board_jans = $board_posts = $the_board = $thisboard = $this_area = $this_user = $this_thread = $results = $usermod = $is_moderator = $is_user_janitor = $lock = $timegateactive = $correct = $getposts = $gotReplies = $banned_count = $board_rules = '';
			$posting = $accounts_per_ip = $post_no = 1;
			$boards_or_tags = 'boards';
			$roll = '0,100';
			$ban_length_minutes = '10 minutes';
			$user_total_allowed = 50;
			
			// determine if user is logged into WordPress
			if ( is_user_logged_in() ) {
				$user_logged_in = 1;
			} else {
				$user_logged_in = 0;
			}

			// determine boards that are protected from wipes
			if ( get_option ( 'regular_board_protected' ) ) {
				$protectedboards = explode ( ',', get_option ( 'regular_board_protected' ) );
				$protected_boards = array_map ( 'regular_board_apply_quotes',  $protectedboards );
			}
			
			// get regular board options from the database
			$user_flood = get_option ( 'regular_board_userflood' );
			$imgurid = get_option ( 'regular_board_imgurid' );			
			$flood_gate = get_option ( 'regular_board_floodgate' );
			$archive_gate = get_option ( 'regular_board_archivegate' );
			$posts_per_page = get_option ( 'regular_board_postsper' );
			$roll = get_option ( 'regular_board_roll' );
			$id_display = get_option ( 'regular_board_ids' );
			$user_create = get_option ( 'regular_board_usercreate' );
			$formatting = get_option ( 'regular_board_formatting' );
			$auto_url = get_option ( 'regular_board_autourl' );
			$announcements = get_option ( 'regular_board_announcements' );
			$max_links = get_option ( 'regular_board_maxlinks' );
			$search_enabled = get_option ( 'regular_board_search' );
			$enable_url = get_option ( 'regular_board_enableurl' );
			$enable_rep = get_option ( 'regular_board_enablerep' );
			$max_body = get_option ( 'regular_board_maxbody' );
			$max_replies = get_option ( 'regular_board_maxreplies' );
			$max_text = get_option ( 'regular_board_maxtext' );
			$these_boards = get_option ( 'regular_board_boards' );		
			$regular_board_footer = get_option ( 'regular_board_footer' );
			$registration_open = get_option ( 'regular_board_registration' );
			$enable_blog = get_option ( 'regular_board_enableblog' );
			$display_wipe = get_option ( 'regular_board_wipedisplay' );
			$banned_image = get_option ( 'regular_board_bannedimage' );
			$board_banner = get_option ( 'regular_board_boardbanner' );
			$accounts_per_ip = get_option ( 'regular_board_accountsper' );
			$boards_or_tags = get_option ( 'regular_board_useboards' );

			// determine protocol to use (#tags or ?boards)
			if ( $boards_or_tags == strtolower ( 'boards' ) ) {
				$protocol = 'boards';
			} elseif ( $boards_or_tags == strtolower ( 'tags' ) ) {
				$protocol = 'tags';
			} else {
				$protocol = 'boards';
			}
			
			// get blog title
			$blog_title = get_bloginfo();
			
			// get wipe settings
			$board_wipe_every = get_option ( 'regular_board_wipeall' );
			$board_wipe_per = get_option ( 'regular_board_wipeper' );
			$board_wipe_date = strtotime ( get_option ( 'regular_board_wipealldate' ) );

			// @get wipe settings || time strings
			$date = $current_timestamp = date ( 'Y-m-d H:i:s' );		
			$ten_minutes_from_now = date ( "Y-m-d H:i:s", strtotime ( '+10 minutes') );
			$ten_minutes_ago = date ( "Y-m-d H:i:s", strtotime ( '-10 minutes') );
			$two_hours_from_now = date ( "Y-m-d H:i:s", strtotime ( '+2 hours' ) );
			$two_hours_ago = date ( "Y-m-d H:i:s", strtotime ( '-2 hours' ) );
			$twelve_hours_from_now = date ( "Y-m-d H:i:s", strtotime ( '+12 hours' ) );
			$twelve_hours_ago = date ( "Y-m-d H:i:s", strtotime ( '-12 hours' ) );
			$one_day_from_now = date ( "Y-m-d H:i:s", strtotime ( '+1 day' ) );
			$one_day_ago = date ( "Y-m-d H:i:s", strtotime ( '-1 day' ) );
			$one_month_from_now = date ( "Y-m-d H:i:s", strtotime ( '+1 month' ) );
			$one_month_ago = date ( "Y-m-d H:i:s", strtotime ( '-1 month' ) );
		
			// determine the boards to show on front
			if ( $these_boards ) {
				$these_boards = explode ( ',', $these_boards );
				$these_boards = array_map ( 'regular_board_apply_quotes',  $these_boards );
			}
			
			$mod_code = '<strong>' . get_option ( 'regular_board_modcode', '##MOD' ) . '</strong>';
			$user_mod_code = '<strong>' . get_option ( 'regular_board_usermodcode', '##JRMOD' ) . '</strong>';
			$current_page = protocol_relative_url_dangit( get_permalink() );
			
			// IP utilities for posts
			$the_ip = $ipaddress;
			$user_ip = sanitize_text_field ( wp_hash ( $the_ip ) );
			$count_user_total = $wpdb->get_var ( "SELECT COUNT(user_id) FROM $regular_board_users" );
			if ( $user_total_allowed ) {
				if ( $user_total_allowed <= $count_users_total ) {
					$registration_open = 0;
				} else {
					$registration_open = 1;
				}
			}
			$check_this_ip = sanitize_text_field ( $the_ip );
		
			// URL queries for determining location
			$query = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
			if ( $query ) {
				if ( isset ( $_GET['b'] ) ) {
					$the_board = sanitize_text_field ( $_GET['b'] );
				}
				if ( isset ( $_GET['ht'] ) ) {
					$the_tag = sanitize_text_field ( $_GET['ht'] );
				}
				if ( isset ( $_GET['a'] ) ) {
					$this_area = sanitize_text_field ( strtolower( $_GET['a'] ) );
				}
				if ( isset ( $_GET['u'] ) ) {
					$this_user = sanitize_text_field ( strtolower( $_GET['u'] ) );
				}
				if ( isset ( $_GET['t'] ) ) {
					$this_thread = intval ( $_GET['t'] );
				}
			}
		
			// determine board from thread id || determine if we're on the front page
			if ( $this_thread ) {
				$the_board = $wpdb->get_var( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
			}
			if ( !$this_area && !$the_board && !$this_user && !$this_thread && !$the_tag ) {
				$nothing_is_here = 1;
			}
			
			$is_user_mod = false;
			$is_user = true;

		// Variables used throughout ( end )

		
		
		
		
		
		
		
		
		
		// User information ( begin )
			
			// get current ( logged in ) IP
			$myinformation = $wpdb->get_results ( 
				$wpdb->prepare ( 
					"SELECT $regular_board_users_select FROM $regular_board_users WHERE user_logged_in_from = %s AND user_logged_in = 1 LIMIT 1", 
					$user_ip 
				) 
			);
			
			if ( count ( $myinformation ) ) {
				foreach ( $myinformation as $results ) {
					$chanmode = intval ( $results->user_chanmode   );
					if ( $chanmode == 1 || $chanmode == 0 ){
						$style = 'tiny';
					}
					if ( $chanmode == 2 ) {
						$style = 'expanded';
					}
					
					$daynight = intval ( $results->user_colormode );
					if ( $daynight == 1 || $daynight == 0 ) {
						$mode = 'day';
					}
					if ( $daynight == 2 ) {
						$mode = 'night';
					}
					$profileavatar = sanitize_text_field ( $results->user_avatar );
					$profileslogan = sanitize_text_field ( str_replace ( '\\', '', $results->user_slogan ) );
					$profileid = intval ( $results->user_id );
					$profileheaven = intval ( $results->user_heaven );
					$profile_email = sanitize_text_field ( $results->user_email );
					$profile_name = sanitize_text_field ( $results->user_name );
					if ( !$profile_name ) {
						$profile_name = 'null';
					}
					$profilepassword = sanitize_text_field ( $results->user_password );
					$profilefollow = sanitize_text_field ( $results->user_follow );
					$following = sanitize_text_field ( $results->user_follow );
					$boards = sanitize_text_field ( $results->user_boards );
					$profileboards = sanitize_text_field ( $results->user_boards );
					$following = sanitize_text_field ( $profilefollow );
					if ( !$results->user_logged_in ) {
						$user_exists = 0;
					}
					if ( $results->user_logged_in ) {
						$user_exists = 1;
					}
					if ( $profileboards ) {
						$profileboards = explode ( ',', $profileboards );
						$profileboards = array_map ( 'regular_board_apply_quotes', $profileboards );
					}
					if( $following ) {
						$following = explode ( ',', $following );
						$following = array_map ( 'regular_board_apply_quotes', $following );
					}
					$profile_strikes = intval ( $results->user_strikes );
					$profile_strikes_up = intval ( $results->user_strikes + 1 );
					$profile_level = intval ( $results->user_level );
					$profile_level_up = intval ( $results->user_level + 1 );
					$profile_posts = intval ( $results->user_posts );
					$profile_posts_up = intval ( $results->user_posts + 1 );
					$i_am_logged_in = intval ( $results->user_logged_in );
					if ( !$profile_strikes ) {
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
					if ( $this_area == 'messages' ) {
						if ( !isset ( $_GET['message'] ) ) {
							$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) ORDER BY messages_id DESC", $profile_name, $profile_name ) );
						}
						if ( isset ( $_GET['message'] ) ) {
							$message_id = intval ( $_GET['message'] );
							$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) AND messages_id = %d LIMIT 1", $profile_name, $profile_name, $message_id ) );
						}
					}
					if ( $this_area == 'stuff' ) {
						$my_unread = $wpdb->get_var ( 
							"SELECT COUNT(*) FROM $regular_board_messages WHERE messages_read = 0 AND messages_to = '$profile_name'" 
						);
						$my_unread = intval ( $my_unread );
						$my_waiting = $wpdb->get_results ( 
							$wpdb->prepare ( 
								"SELECT $regular_board_friends_select FROM $regular_board_friends WHERE friends_connectee = %s AND friends_mutual = %d", 
								$profile_name, 
								0 
							) 
						);
						$my_waitings = $wpdb->get_var ( 
							"SELECT COUNT(*) FROM $regular_board_friends WHERE friends_connectee = '$profile_name' AND friends_mutual = 0" 
						);
					}
					if ( $this_area == 'history' || $this_user ) {
						$my_friends = $wpdb->get_results ( 
							$wpdb->prepare ( 
								"SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( 
									friends_connector = %s OR friends_connectee = %s 
								) AND friends_mutual = %d", 
								$profile_name, 
								$profile_name, 
								1 
							) 
						);
					}
				}
			}	
		// User information ( end )







		// Board information ( begin )
			if ( $protocol == 'boards' ) {
				$getboards = $wpdb->get_results ( "SELECT $regular_board_boards_select FROM $regular_board_boards WHERE board_shortname != '' ORDER BY board_postcount DESC, board_name ASC" );
			}
			if ( $the_board ) {
				$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_boards_select FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
			}
			if ( isset ( $_REQUEST['board'] ) ) {
				$the_board = sanitize_text_field ( strtolower ( $_REQUEST['board'] ) );
				$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_boards_select FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
			}
			if ( $protocol == 'boards' ) {
				if ( count ( $getboards ) == 1 ) {
					foreach ( $getboards as $board ) {
						$thisboard = $board->board_shortname;
					}
				}
			}
		
			if ( !$the_board && $thisboard ) {
				$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_boards_select FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thisboard ) );
			}
			
			if ( $this_thread ) {
				$thread_board = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
				if ( $thread_board ) {
					$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_boards_select FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thread_board ) );
				}
			}		
			if ( $thisboard || $the_board ) {
				if ( count ( $get_current_board ) && $protocol == 'boards' ) {
					foreach ( $get_current_board as $current_board_information ) {
						$lock = intval ( $current_board_information->board_locked );
						$board_id = intval ( $current_board_information->board_id );
						$board_name = $current_board_information->board_name;
						$board_short = $current_board_information->board_shortname;
						$board_description = $current_board_information->board_description;
						$board_rules = $current_board_information->board_rules;
						$board_mods = $current_board_information->board_mods;
						$board_jans = $current_board_information->board_janitors;
						$board_posts = intval ( $current_board_information->board_postcount );
						$require_logged = intval ( $current_board_information->board_logged );
						$boardwipe = $current_board_information->board_wipe;
						$boarddate = $current_board_information->board_date;
						if ( !$board_wipe_every ) {
							if( $boardwipe && $boardwipe != strtolower ( 'never' ) ) {
								$board_date = strtotime ( $boarddate );
								$today_is = strtotime ( $current_timestamp );
								if ( strpos ( strtolower ( $boardwipe ), 'minute' ) ) {
									$uptime = intval ( $boardwipe ) * 60;
									$interval = ' every minute';
								} elseif ( strpos ( strtolower ( $boardwipe ), 'hour' ) ) {
									$uptime = intval ( $boardwipe ) * 3600;
									$interval = ' hourly';
								} elseif ( strpos ( strtolower ( $boardwipe ), 'day' ) ) {
									$uptime = intval ( $boardwipe ) * 86400;
									$interval = ' daily';
								} elseif ( strpos ( strtolower ( $boardwipe ), 'week' ) ) {
									$uptime = intval ( $boardwipe ) * 604800;
									$interval = ' weekly';
								} elseif ( strpos ( strtolower ( $boardwipe ), 'month' ) ) {
									$uptime = intval ( $boardwipe ) * 2628000;
									$interval = ' monthly';
								} elseif ( strpos ( strtolower ( $boardwipe ), 'year' ) ) {
									$uptime = intval ( $boardwipe ) * 31536000;
									$interval = ' yearly';
								} else {
									$uptime = intval ( $boardwipe ) * 60;
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
					}
				} else {
					$boardheader = '';
				}
			}
		// Board information ( end ) 









		// Determine mod ( begin )
			$current_user = wp_get_current_user();
			$current_user_login = $current_user->user_login;

			if ( current_user_can ( 'manage_options' ) ) {
				$is_moderator = true;
			}

			if ( $board_mods ) {
				$usermods = explode ( ',', $board_mods );
				if ( in_array ( $current_user_login, $usermods ) || in_array ( $profileid, $usermods ) ) {
					$is_user_mod = true;
					$user_logged_in = 1;
				}
			}

			if ( $board_jans ) {
				$userjanitors = explode ( ',', $board_jans );
				if ( in_array ( $current_user_login, $userjanitors ) || in_array ( $profileid, $userjanitors ) ) {
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

			if ( $is_user_mod ) {
				$is_user = false;
			}

			if ( $is_user_janitor ) {
				$is_user = false;
			}

			if ( $is_moderator || $is_user_mod ) {
				if ( $this_area == 'queue' ) { 
					$get_queue = $wpdb->get_results ( 
						$wpdb->prepare ( 
							"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( 
								post_reportcount > %d OR post_public > %d 
							)", 
							0, 
							1 
						) 
					); 
				}
			}

			if ( $lock == 1 ) {
				if ( $is_user ) {
					$posting = 0;
				}
				if ( $is_user !== true ) {
					$posting = 1;
				}
			}
		// Determine mod ( end )









		// Loop queries ( begin )
			$total_posts = $wpdb->get_var ( "SELECT SUM(board_postcount) FROM $regular_board_boards" );
			$getuser = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_bans_select FROM $regular_board_bans WHERE banned_ip = %s LIMIT 1", $user_ip  ) );
			$recentposts = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_public = 1 ORDER BY post_date DESC LIMIT 10" );

			if ( count ( $getuser ) > 0 ) {
				$userisbanned = 1;
			}

			if ( $search_enabled && isset ( $_POST['regular_board_search_submit'] ) && $_REQUEST['regular_board_search'] ) {
				$search = sanitize_text_field ( str_replace ( '\'', '\\\'', $_REQUEST['regular_board_search'] ) );
			}
			$use_this = 0;
			$order_by = "post_id DESC";
			if ( $search_enabled && $search ) {
				$use_this++;
				$where_by = "WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' )";
			} else {
				if ( $the_tag ) {
					$use_this++;
					$where_by = "WHERE post_comment LIKE '%#$the_tag%'";
				}
				if ( $this_area == 'videos' ) {
					$use_this++;
					if ( $the_board ) {
						$where_by = "WHERE post_type = 'youtube' AND post_board = '$the_board'";
					} else {
						$where_by = "WHERE post_type = 'youtube'";
					}
					
				}	
				if ( $the_board && !$the_tag ) {
					$use_this++;
					if ( $protocol == 'boards' ) {
						$where_by = "WHERE post_parent = 0 AND post_board = '$the_board'";
					}
					if ( $protocol == 'tags' ) {
						$where_by = "WHERE post_comment LIKE '%#$the_board%'";
					}
					$order_by = "post_sticky DESC, post_last DESC";
				}		
				if ( $this_area == 'topics' || $this_area == 'topics' && $the_board || !$this_area && !$this_user && !$the_tag ) {
					$use_this++;
					if ( $the_board ) {
						$where_by = "WHERE post_parent = 0 AND post_board = '$the_board' AND post_public = 1";
						$order_by = "post_sticky DESC, post_last DESC";
					} else {
						$where_by = "WHERE post_parent = 0 AND post_public = 1";
						$order_by = "post_sticky DESC, post_last DESC";		
					}
				}
				if ( !$the_board && $this_area == 'replies' && !$this_thread && !$this_user ){
					$use_this++;
					$where_by = "WHERE post_parent != 0 AND post_public = 1";
					$order_by = "post_sticky DESC, post_last DESC";
				}

				if ( $nothing_is_here ) {
					$use_this++;
					if ( $profileboards ) {
						$profileboards = " post_board IN ( " . join (',', $profileboards ) . ") AND post_public = 1";
					} else {
						$profileboards = '';
					}
					if ( $following  ) {
						$following = " ( post_userid IN (" . join (',', $following ) . ") OR post_name IN (" . join (',', $following ) . ") ) AND post_public = 1";
					} else {
						$following = '';
					}

					if ( $following || $profileboards ) {
						$where_by = "WHERE post_parent = 0 AND $following $profileboards ";
					} else {
						if ( $these_boards ) {
							$where_by = "WHERE post_parent = 0 AND post_board IN ( " . join (',', $these_boards ) . ") AND post_public = 1";
						} elseif ( !$these_boards ) {
							$where_by = "WHERE post_parent = 0 AND post_public = 1";
						}
					}
					$order_by = "post_date DESC";
				}

				if ( $this_area == 'all' ) {
					$use_this++;
					$where_by = "WHERE post_parent = 0 AND post_public = 1";
					$order_by = "post_date DESC";
				}	
				
				
				if ( $this_area == 'gallery' && !$this_thread && !$this_user ) {
					$use_this++;
					if ( !$the_board ) {
						$where_by = "WHERE post_url != '' AND post_type = 'image'";
					} 
					if ( $the_board ) {
						$where_by = "WHERE post_url != '' AND post_type = 'image' AND post_board = '$the_board'";
					}
				}		
				if ( $this_thread && !$this_user ) {
					$use_this++;
					if ( $is_moderator ) {
						$where_by = "WHERE post_id = $this_thread";
					} else {
						$where_by = "WHERE post_id = $this_thread AND post_public = 1";
					}
					if ( $search_enabled && $search ) {
						$countParentReplies = "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_parent = $this_thread";
					} else {
						$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d", $this_thread ) );
					}
					$this_title = $wpdb->get_var ( "SELECT post_title FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
					if ( $this_title ) {
						$this_title = htmlentities ( $this_title );
					} else {
						$this_title = '(Untitled)';
					}
				}
				if ( $this_area == 'history' ) {
					$use_this++;
					$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid, $this_user ) );
					$where_by = "WHERE post_userid = $profileid";
					$order_by = "post_date DESC";		
				}
				if ( $this_area == 'mod' ) {
					$mod_logs = $wpdb->get_results ( "SELECT * FROM $regular_board_logs ORDER BY logs_id DESC LIMIT 50 " );
				}
				if ( $this_user ) {
					$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $this_user, $this_user, 1 ) );
					$use_this++;
					$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_name = %s LIMIT 1", $profileid, $this_user ) );
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
					if ( isset ( $_GET['n'] ) ) {
						$results = intval ( $_GET['n'] );
					}
					if ( $results ) {
						$start = ( $results - 1 ) * $posts_per_page;
					} else {
						$start = 0;
					}
					$getposts = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
				}
			}	
		// Loop queries ( end ) 
		
		// Board wipe ( begin )
			$board_wipe_true = '';
			if ( $board_wipe_every && $board_wipe_every != strtolower ( 'never' ) && $board_wipe_per == strtolower ( 'board' ) ) {
				$board_wipe_true = 1;
				$today_is = strtotime ( $current_timestamp );
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

			if ( $protocol == 'boards' ) {
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
			}	
		// Board wipe ( end ) 

	
	
	
	
	
	
	
	
		// Navigation elements ( begin ) 
			$reports_link = $deleted_link = $queue_link = $video_link = $video_link_class = $all_link_class = $stuff_link_class = $home_link_class = $topics_link_class = $gallery_link_class = $history_link_class = $logout_link_class = $gallery_link = $all_link = $history_link = $logout_link = $options_link = $options_link_class = '';
			
			if ( $this_area == 'all' ) { $all_link_class = ' class="active" '; }
			if ( $this_area == 'stuff' ) { $stuff_link_class = ' class="active" '; }
			if ( $this_area == 'messages' ) { $stuff_link_class = ' class="active" '; }
			if ( $this_area == 'options' ) { $options_link_class = ' class="active" '; }
			if ( $this_area == 'blog' ) { $stuff_link_class = ' class="active" '; }
			if ( $this_area == 'news' ) { $stuff_link_class = ' class="active" '; }
			if ( $this_area == 'stats' ) { $stuff_link_class = ' class="active" '; }
			if ( $this_area == 'mod' ) { $stuff_link_class = ' class="active" '; }
			
			if ( $nothing_is_here ) { 
				if ( !$this_area ) {
					$home_link_class = ' class="active" '; 
				}
			}
			if ( $this_area == 'topics' || $the_board ) { 
				$topics_link_class  = ' class="active" '; 
			}
			if ( $the_board && $this_area == 'topics') { 
				$topics_link_class  = ' class="active" '; 
			}
			if ( $this_area == 'gallery' ) { 
				$gallery_link_class = ' class="active" '; 
			}
			if ( $this_area == 'history' ) { 
				$history_link_class = ' class="active" '; 
			}
			if ( $this_area == 'logout' ) { 
				$logout_link_class  = ' class="active"'; 
			}
			if ( $enable_rep || $enable_url || $imgurid ) {
				if ( $the_board ) {
					$gallery_link = '<a title="all images" href="' . $current_page . '?b=' . $the_board . '&amp;a=gallery"' . $gallery_link_class . '>gallery</a>';
				}
				if ( !$the_board ) {
					$gallery_link = '<a title="all images" href="' . $current_page . '?a=gallery"' . $gallery_link_class . '>gallery</a>';
				}
			}
			if ( $user_exists ) {
				$history_link = '<a title="my profile" href="' . $current_page . '?a=history"' . $history_link_class . '>me</a>';
			}

			if ( $user_exists && $profile_name && $profilepassword) {
				$logout_link =  '<a id="logout-link" title="logout" href="' . $current_page . '?a=logout"' . $logout_link_class . '>logout</a>';
			}


			if ( $this_area == 'videos' ) { $video_link_class = ' class="active" '; }
			if ( $enable_rep || $enable_url ) {
				if ( $the_board ) {
					$video_link = '<a title="all videos" href="' . $current_page . '?b=' . $the_board . '&amp;a=videos"' . $video_link_class . '>videos</a>';
				} else {
					$video_link = '<a title="all videos" href="' . $current_page . '?a=videos"' . $video_link_class . '>videos</a>';
				}
			}

			if ( $is_moderator || $is_user_mod ) {
				$queue_link   = '<a title="awaiting approval" href="' . $current_page . '?a=queue">moderation</a>';
			}

			$blog_link = '<a title="home" href="' . $current_page . '">front</a>';
			$all_link = '<a title="All (unfiltered)" href="' . $current_page. '?a=all"' . $all_link_class . '>all</a>';

			if ( $the_board ) {
				$topics_link = '<a title="all topics" href="' . $current_page . '?b=' . $the_board . '&amp;a=topics"' . $topics_link_class . '>topics</a>';
			} else {
				$topics_link = '<a title="all topics" href="' . $current_page . '?a=topics"' . $topics_link_class . '>topics</a>';
			}

			$stuff_link = '<a title="options and other misc. stuff of importance" href="' . $current_page . '?a=stuff"' . $stuff_link_class . '>stuff</a>';
			if ( $user_exists ) {
				$options_link = '<a id="settings-link" title="my personal account settings" href="' . $current_page . '?a=options"' . $options_link_class . '>settings</a>';
			}

			$navigation   =  '<div class="navi">'
				. $board_current 
				. '<div class="navigation">'
				. $topics_link 
				. $gallery_link 
				. $video_link 
				. '<span class="nav-right">'
				. $history_link 
				. $stuff_link 
				. $reports_link 
				. $deleted_link 
				. $queue_link 
				. $options_link 
				. $logout_link 
				. '</span></div>'
				. '</div>';	
		// Navigation elements ( end ) 









		// Regular Board ( begin )
			echo '<div class="regular_board_board_all">';
			
			if ( $protocol == 'boards' ) {
				echo '<div class="top_nav">' .  $blog_link . ' <span>-</span> ' . $all_link . ' <span>|</span> ';
				$board_nom = 0;
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
					if ( ++$board_nom > 1 && $board_nom <= count ( $getboards ) ) {
					 echo '<span>-</span>';
					}
					echo '<a href="' . $current_page . '?b=' . $gotboards->board_shortname . '"'; 
					if ( $the_board && $the_board == $gotboards->board_shortname ) { 
						echo ' class="active"'; 
					} 
					echo '>';
					echo $gotboards->board_name . '</a>';
				}
				echo '</div>';
			}		
			
			echo '<div class="spacer">' . $banner . $navigation;
			
			if ( $this_thread ) {
				echo '<p class="nav_tools">';
				if ( !$the_board ) {
					echo '<a class="load_link" href="' . $current_page . '">Return</a> | ';
				} elseif ( $the_board ) {
					echo '<a class="load_link" href="' . $current_page . '?b=' . $the_board . '">Return</a> | ';
				} elseif ( $thread_board ) {
					echo '<a class="load_link" href="' . $current_page . '?b=' . $thread_board . '">Return</a> | ';
				} else {
					echo '<a class="load_link" href="' . $current_page . '">Return</a> | ';
				}
				if ( $this_thread && $this_area != 'media' ) {
					echo '<a href="' . $current_page . '?t=' . $this_thread . '&amp;a=media">Expand all media</a> | ';
				} else {
					echo '<a href="' . $current_page . '?t=' . $this_thread . '">Hide all media</a> | ';
				}
				echo '<a href="#top">Top</a> | <a class="reload" xdata="' . $this_thread .'" data="' . $current_page . '?t=' . $this_thread . '">Refresh thread</a>
				</p>';
			} else {
				echo '<p class="nav_tools hidden"></p>';
			}		


			if ( !$this_thread ) {
				echo '<div class="piece_form"><div class="form_form">';
				if ( $this_area == 'editpost' && $user_exists && $this_thread ) { 
					include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php'    ); 
				} else {
					if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
						include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
					} else {
						include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
					}	
				}
				echo '</div>';
			}		
			
			echo '<div id="regular_board"><div class="right-half">';
			
			if ( $userisbanned ) { 
				include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' ); 
			}

			if ( $nothing_is_here ) {
				echo '<div id="threadthread">';
				if ( $getposts ) {
					echo '<div class="thread_container">';
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
					';
				}
				echo '</div></div>';
			}










			// Post actions ( begin )
				// @post actions || ban the user
				if ( $this_area == 'ban' ) {
					echo '<div id="post_action">';
						if ( $is_moderator ) {
						echo '<form class="regularboard_form" method="post" name="form" action="' . $current_page . '?a=ban&t=' . $this_thread . '">';
						wp_nonce_field('form');
						echo '<label>Reason for ban</label><input type="text" name="reason" placeholder="Reason for ban">';
						echo '<label>Length of ban (permanent for permanent)</label><input type="text" name="length" placeholder="Length of ban">';
						echo '</select><input type="submit" name="confirm" value="Reason" /></form>';

						if ( isset ( $_POST['confirm'] ) ) {
							if ( $_REQUEST['length'] ) {
								$ban_length_minutes = sanitize_text_field ( $_REQUEST['length'] );
							}
							$get_id = $wpdb->get_var( "SELECT post_userid FROM $regular_board_posts WHERE post_id = $this_thread" );
							$get_guest = $wpdb->get_var( "SELECT post_guestip FROM $regular_board_posts WHERE post_id = $this_thread" );
							$get_id = intval ( $get_id );
							$get_ip = $wpdb->get_var( "SELECT user_ip FROM $regular_board_users WHERE user_id = $get_id" );
							if ( $get_guest ) {
								$get_ip = sanitize_text_field ( $get_guest );
							} else {
								$get_ip = sanitize_text_field ( $get_ip );
							}
							
							$get_content = $wpdb->get_results( "SELECT post_url, post_comment FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
							foreach ( $get_content as $posts ) {
								$banned_content = '[ ' . $posts->post_url . ' ] ' . ' [ ' . $posts->post_comment . ' ] ';
							}
							
							if ( isset ( $_REQUEST['reason'] ) && $_REQUEST['reason'] ) {
								$banned_message = sanitize_text_field ( $_REQUEST['reason'] );
							} else {
								$banned_message = 'No reason given.';
							}
							
							$wpdb->query (
								$wpdb->prepare (
									"INSERT INTO $regular_board_bans
									(
										banned_id,
										banned_date,
										banned_ip,
										banned_banned,
										banned_message,
										banned_length
									) 
									VALUES (
										%d,
										%s,
										%s,
										%d,
										%s,
										%s
									)",
									'',
									$current_timestamp,
									$get_ip,
									1,
									$banned_message,
									$ban_length_minutes
								)
							);
							$wpdb->query (
								$wpdb->prepare (
									"INSERT INTO $regular_board_logs 
									( 
										logs_id, logs_date, logs_ip, logs_thread, logs_parent, logs_board, logs_message, logs_content
									) 
									VALUES ( 
										%d, %s, %s, %d, %d, %s, %s, %s
									)",
								'', $current_timestamp, $user_ip, 0, 0, '', $banned_message, $banned_content
								)
							);	
						}
					} else {
						echo 'You can\'t access that.';
					}
					echo '</div>';
				}

				// @post actions || approve a thread/reply in the moderation queue
				if ( $this_area == 'approve' ) {
					if ( $is_moderator || $is_user_mod || $is_user_janitor ) {
						echo '<div id="post_action">';
							$post_status = 0;
							$post_status = $wpdb->get_var ( "SELECT post_public FROM $regular_board_posts WHERE post_id = $this_thread AND post_public = 666" );
							$post_userid = $wpdb->get_var ( "SELECT post_userid FROM $regular_board_posts WHERE post_id = $this_thread AND post_public = 666" );
							$user_posts = $wpdb->get_var ( "SELECT user_postcount FROM $regular_board_users WHERE user_id = $post_userid" );
							$user_level = $wpdb->get_var ( "SELECT user_level FROM $regular_board_users WHERE user_id = $post_userid" );
							$user_posts_plus_one = ( $user_posts + 1 );
							if ( !$user_level ) {
								$user_level_plus_one = 1;
							} elseif ( $user_level == 1 ) {
								$user_level_plus_one = $user_level;
							}
							if ( $post_status ) {
								$wpdb->update (
									$regular_board_posts,
									array( 
										'post_public' => 1,
										'post_last'   => $current_timestamp,
										'post_date'   => $current_timestamp
									),
									array( 
										'post_id' => $this_thread
									),
									array( 
										'%d',
										'%s',
										'%s',
										'%d'
									)
								);
								$wpdb->update (
									$regular_board_users,
									array( 
										'user_posts' => $user_posts_plus_one,
										'user_level' => $user_level_plus_one
									),
									array( 
										'user_id' => $post_userid
									),
									array( 
										'%d',
										'%d',
										'%d'
									)
								);
								echo '<p>Post approved.</p>';
							}
						echo '</div>';
					}
				}

				// @post actions || confirmation for 'deletion'
				if ( $this_area == 'delete' ) {
					echo '<div id="post_action">';
					$comparedpass = $profilepassword;
					$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
					if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
						echo '<p>Are you sure? <a data="' . $posts->post_id . '" href="' . $current_page . '?a=destroy&amp;t=' . $this_thread . '"> Yes </a> / <a href="' . $current_page . '?t=' . $this_thread . '"> No </a></p>';
					} else {
						echo '<p>You can\'t do that.</p>';
					}
					echo '</div>';
				}
				// @post actions || 'delete' a thread/reply
				if ( $this_area == 'destroy' ) {
					echo '<div id="post_action">';
					$comparedpass = $profilepassword;
					$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
					if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
						$grab_board = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
						$grab_board = sanitize_text_field ( $grab_board );
						if ( $grab_board ) {
							$postcount  = $wpdb->get_var ( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$grab_board'" );
							$postcount  = $postcount - 1;
							$wpdb->update (
								$regular_board_boards,
								array( 
									'board_postcount' => $postcount
								),
								array( 
									'board_shortname' => $grab_board
								),
								array( 
									'%d',
									'%s'
								)
							);			
						}
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_title' => '[deleted]',
								'post_name' => 'null',
								'post_comment' => '[deleted]',
								'post_userid' => 0,
								'post_public' => 0,
								'post_type'   => 'post',
								'post_url'    => '',
								'post_locked' => 1,
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%s',
								'%s',
								'%s',
								'%d',
								'%d',
								'%s',
								'%d',
								'%d'
							)
						);		
					} else {
						echo '<p>You can\'t do that.</p>';
					}
					echo '</div>';
				}

				// @post actions || set post_public to 1 of a post that doesn't have post_public set to 1
				if ( $this_area == 'undelete' ) {
					echo '<div id="post_action">';
					$comparedpass = $profilepassword;
					$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
					if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_public' => 1
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post restored.</p>';
					} else {
						echo '<p>You can\'t do that.</p>';
					}
					echo '</div>';
				}

				// @post actions || move a thread / reply to another board
				elseif ( $this_area == 'move' && $protocol == 'boards' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						if ( !isset ( $_POST['confirm'] ) ) {
							if ( count ( $getboards ) > 1 ) {
								echo '<form class="regularboard_form" method="post" name="form" action="' . $current_page . '?a=move&t=' . $this_thread . '">';
								wp_nonce_field('form');
								echo '<select name="move_to" id="move_to">';
								foreach($getboards as $gotBoard){
									if ( $gotBoard->board_shortname ) {
										$board = esc_sql($gotBoard->board_shortname);
										$name = esc_sql($gotBoard->board_name);
										echo '<option value="'.$board.'">/'.$board.'/ - '.$name.'</option>';
									}
								}
								echo '</select><input type="submit" name="confirm" value="Move" /></form>';
							}
						}
						if ( isset ( $_POST['confirm'] ) ) {
							$get_board = $wpdb->get_var( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
							if ( $get_board ) {
								$new_board = sanitize_text_field ( $_REQUEST['move_to'] );
								$get_board_count = $wpdb->get_var( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$get_board'" );
								$get_new_count = $wpdb->get_var( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$new_board'" );
								$get_new_count = ( $get_new_count + 1 );
								$wpdb->update (
									$regular_board_boards,
									array( 
										'board_postcount' => $get_new_count
									),
									array( 
										'board_shortname' => $new_board
									),
									array( 
										'%d',
										'%s'
									)
								);
								if ( $get_board_count ) {
									$new_postcount = ( $get_board_count - 1 );
									$wpdb->update (
										$regular_board_boards,
										array( 
											'board_postcount' => $new_postcount
										),
										array( 
											'board_shortname' => $get_board
										),
										array( 
											'%d',
											'%s'
										)
									);
								}
							}		
							$wpdb->update (
								$regular_board_posts,
								array( 
									'post_board' => $_REQUEST['move_to']
								),
								array( 
									'post_id' => $this_thread
								),
								array( 
									'%s',
									'%d'
								)
							);
							$wpdb->update (
								$regular_board_posts,
								array( 
									'post_board' => $_REQUEST['move_to']
								),
								array( 
									'post_parent' => $this_thread
								),
								array( 
									'%s',
									'%d'
								)
							);
							echo '<p>Post moved.</p>';
						}
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || mark a thread or reply as spam
				elseif ( $this_area == 'spam' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_public' => 2
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post marked as spam.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || remove spam status of a thread or reply
				elseif ( $this_area == 'unspam' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_public' => 1
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post no longer marked as spam.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || lock a thread
				elseif ( $this_area == 'lock' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_locked' => 1
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post locked.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || unlock a thread
				elseif ( $this_area == 'unlock' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_locked' => 0
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post unlocked.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}
				
				// @post actions || sticky a thread
				elseif ( $this_area == 'sticky' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$wpdb->update (
							$regular_board_posts,
							array( 
								'post_sticky' => 1
							),
							array( 
								'post_id' => $this_thread
							),
							array( 
								'%d',
								'%d'
							)
						);
						echo '<p>Post stickied.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || unsticky a thread 
				elseif ( $this_area == 'unsticky' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
							$wpdb->update (
								$regular_board_posts,
								array( 
									'post_sticky' => 0
								),
								array( 
									'post_id' => $this_thread
								),
								array( 
									'%d',
									'%d'
								)
							);
							echo '<p>Post unstickied.</p>';
					} else {
						echo '<p>You can\'t access that.</p>';
					}
					echo '</div>';
				}

				// @post actions || thread/reply reporting
				elseif ( $this_area == 'report' ) {
					echo '<div id="post_action">
					<form class="regularboard_form" name="delete" name="form" method="post" action="' . $current_page . '?a=report&t=' . $this_thread . '" >';
					wp_nonce_field('form');
					echo '<input type="text" name="reason" placeholder="Reason for reporting..." />
					<input type="submit" name="report" value="Report thread" />
					</form>';
					if ( isset ( $_POST['report'] ) && $_REQUEST['reason'] ) {
						$REPORTMESSAGE = esc_sql( $_REQUEST['reason'] );
						$reportthread = $wpdb->get_results ( 
							$wpdb->prepare ( 
								"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", 
								$this_thread 
							)
						);
						if ( count ( $reportthread ) ) {
							foreach ( $reportthread as $reported ) {
								$grabexistingreport = $wpdb->get_results ( 
									$wpdb->prepare ( 
										"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", 
										$this_thread 
									) 
								);
								if ( count ( $grabexistingreport ) ) {
									foreach ( $grabexistingreport as $existing ) {
										$wpdb->update (
											$regular_board_posts,
											array( 
												'post_report' => $REPORTMESSAGE,
												'post_reportcount' => $existing->post_reportcount + 1
											),
											array( 
												'post_id' => $this_thread
											),
											array( 
												'%s',
												'%d',
												'%d'
											)
										);
										echo '<p>Post reported.</p>';
									}
								}
							}
						}
					}
					echo '</div>';
				}

				// @post actions || dismiss a report
				elseif ( $this_area == 'dismiss' ) {
					echo '<div id="post_action">';
					if ( $is_moderator || $is_user_mod ) {
						$grabexistingreport = $wpdb->get_results ( 
							$wpdb->prepare ( 
								"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", 
								$this_thread 
							) 
						);
						if ( count ( $grabexistingreport ) ) {
							foreach ( $grabexistingreport as $existing ) {
								$wpdb->update (
									$regular_board_posts,
									array( 
										'post_report' => '',
										'post_reportcount' => 0
									),
									array( 
										'post_id' => $this_thread
									),
									array( 
										'%s',
										'%d',
										'%d'
									)
								);
								echo '<p>Report dimissed.</p>';
							}
						}
					}
					echo '</div>';
				}
			// Post actions ( end )










			if ( $this_area == 'videos' ) {
				if ( $getposts ) {
					echo '<div class="thread">
						<h1>Video Feed';
							if ( $the_board ) {
								echo ' for /' . $the_board . '/';
							}
						echo '</h1>
						<iframe src="//www.youtube.com/embed/?playlist=';
					foreach ( $getposts as $posts ) {
						if ( $posts->post_type == 'youtube' ) {
							$post_count++;
							if ( $post_count < count ( $getposts ) ) {
								echo $posts->post_url . ',';
							} else {
								echo $posts->post_url;
							}
						}
					}
					echo '&amp;controls=1&amp;showinfo=1&amp;autohide=1" width="600" height="338" frameborder="0" allowfullscreen></iframe>
					</div>';
				} else {
					echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
				}
			} elseif ( $this_area == 'create' && $user_create == 1 ) {
				if ( $user_exists && $profile_level > 2 ) {
					echo '<h1>Create a new board</h1>';
					$board_name = $board_shortname = $board_description = $board_moderators = $board_janitors = $board_locked = $board_logged = $board_wipe = $board_rules = '';
					if ( isset ( $_POST['save_newboard'] ) && $_REQUEST['board_shortname'] ) {
						$board_name = sanitize_text_field ( preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['board_name'] ) );
						$board_shortname = sanitize_text_field ( preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['board_shortname'] ) );
						$board_rules = sanitize_text_field ( $_REQUEST['board_rules'] );
						$board_description = sanitize_text_field ( $_REQUEST['board_description'] );
						if ( $board_shortname ) {
							$regular_board_board = $wpdb->get_var( 
								"SELECT COUNT(*) FROM $regular_board_boards WHERE board_shortname = '$board_shortname'" 
							);
						}
						if ( !$regular_board_board ) {
							$wpdb->query ( 
								$wpdb->prepare(
									"INSERT INTO $regular_board_boards 
										( 
											board_id,  
											board_date,  
											board_name,  
											board_shortname,  
											board_description,  
											board_rules,
											board_mods,  
											board_janitors,  
											board_postcount, 
											board_locked, 
											board_logged, 
											board_wipe 
										) VALUES ( 
											%d, 
											%s, 
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
										) ", 
										'', 
										$date, 
										$board_name, 
										$board_shortname, 
										$board_description, 
										$board_rules,
										$profileid, 
										'', 
										0, 
										0, 
										0, 
										''
									) 
								);
							echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?b=' . $board_shortname . '"></p>';
						} else {
							echo '<p class="information">board already exists.</p>';
						}
					}
					
					$data = '';
					if ( $the_board  ) { $data = $current_page . '?b=' . $the_board; }
					elseif ( $this_thread ) { $data = $current_page . '?t=' . $this_thread; }
					else { $data = $current_page; }				
					
					echo '<div id="reply" class="reply">
						<form id="regularboard" data="' . $data . '" method="post" name="createboard" action="' . $current_page . '?a=create">
						' . wp_nonce_field( 'createboard' ) . '
							<section class="profile-section"><label class="small-left"><u>board name</u><hr />the extended name for this board</label><input name="board_name" type="text" value="' . $board_name . '"/></section>
							<section class="profile-section"><label class="small-left"><u>board shortname</u><hr />the name used in the url</label><input name="board_shortname" type="text" value="' . $board_shortname . '"/></section>
							<section class="profile-section"><label class="small-left"><u>board description</u><hr />a little something about this board</label><input name="board_description" type="text" value="' . $board_description . '"/></section>
							<section class="profile-section"><label class="small-left"><u>board rules</u><hr />rules to go in the sidebar<br />use comment formatting</label><textarea name="board_rules"></textarea></section>
							<section><input type="submit" name="save_newboard" value="'; if ( $board_name ) { echo 'Edit'; } else { echo 'Create'; } echo ' this board" /></section>
						</form>
					</div>';
				} else {
					echo '<p class="information">You need to have an account of least level 2 or above to create 
					your own boards.</p>';
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
			} 
			elseif ( $this_area == 'options' && $user_exists ) { 
				include ( plugin_dir_path(__FILE__) . '/regular_board_user_options.php' ); 
			} elseif ( $this_area == 'history' && $user_exists || $this_user ) { 
				include ( plugin_dir_path(__FILE__) . '/regular_board_profile_loop.php' ); 
			} elseif ( $this_area == 'stats' ) { 
				include ( plugin_dir_path(__FILE__) . '/regular_board_board_stats.php'  ); 
			}				

			elseif ( $the_board || $this_thread || $the_tag ) {
				if ( $the_tag || $the_board ) {
					if ( $the_tag ) { $the_board = $the_tag; }
					if ( $this_area != 'gallery' ) {
						echo '<div class="omitted' . htmlentities($the_board) . '">';
					}
				}
				if ( $the_tag ) {
					echo '<div class="thread"><p>All posts tagged <strong> #' . $the_tag . '</strong></p></div>';
				}
				
				if ( count ( $get_current_board ) > 0 && $protocol == 'boards' || $protocol == 'tags' && $the_board || $this_thread || $the_tag && $protocol == 'boards' ) {
					if ( !$user_logged_in && $require_logged == 1 ) {
						echo '<div class="thread"><p>You are not logged in.</p></div>';
					} elseif ( !$user_logged_in && !$require_logged || $user_logged_in ) {
						if ( $this_thread ) {
							$currentCountNomber = count ( $countParentReplies );
						}
						if ( !isset ( $_POST['FORMSUBMIT'] ) ) {
							if ( $this_area == 'editpost' ) {
								if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
									include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
								} else {
									include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
								}
							}
							if ( $correct != 3 ) {
								if ( $the_board && !$this_thread ) {
									$website_url = $current_page . '?b=' . $the_board; 
								} elseif ( $this_thread ) { 
									$website_url = $current_page . 't=' . $this_thread; 
								}

								if ( $totalpages > 0 ) {

									include ( plugin_dir_path(__FILE__) . '/regular_board_board_loop.php' );

							} else {
									$thread_div_id = '';
									if ( $nothing_is_here ) {
										$thread_div_id= 'thread';
									} elseif ( $the_board ) {
										$thread_div_id = $the_board;
									}
									echo '<div id="thread' . $thread_div_id . '"><div class="thread"><p><strong>Nothing to see here.</strong></p></div></div>';
								}
							}
						}
					}
				}
				if ( $the_tag || $the_board ) {
					if ( $this_area != 'gallery' ) {
						echo '</div>';
					}
				}			
			}
			
			if ( $this_area == 'post' ) { 
			
			
			include ( plugin_dir_path(__FILE__) . '/regular_board_area_post.php' ); 
			
			
			} elseif ( $this_area == 'gallery' && !$the_board || $this_area == 'replies' || $this_area == 'topics' && !$the_board || $this_area == 'all' ) {
				echo '<div class="omitted' . $this_area . '">';
				echo '<div class="thread_container">';
				if ( $getposts ) {
					if ( count ( $getposts ) > 0 ) {
						if ( $this_area == 'gallery' ) {
							echo '<div id="masonry">';
						}
						foreach ( $getposts as $posts ) {
							if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
								include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
							} else {
								include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
							}
						}
						if ( $this_area == 'gallery' ) {
							echo '</div>';
						}
							
						include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
					}
				} else {
					echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
				}
				echo '</div></div>';
			}
			elseif ( $this_area == 'news'                     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_news.php'  ); }
			elseif ( $enable_blog && $this_area == 'blog'     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_blog.php'  ); }
			elseif ( $this_area == 'mod'                      ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_mod.php'   ); }
			elseif ( $this_area == 'stuff'                    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_stuff.php' ); }
			elseif ( $this_area == 'messages' && $user_exists ) { include ( plugin_dir_path(__FILE__) . '/regular_board_messages.php'   ); } 
			elseif ( $this_area == 'logout' && $user_exists   ) { include ( plugin_dir_path(__FILE__) . '/regular_board_logout.php'     ); }

			if ( $this_thread ) {
				echo '<div class="piece_form thread_form"><div class="form_form">';
				if ( $this_area == 'editpost' && $user_exists && $this_thread ) { 
					include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php'    ); 
				} else {
					if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
						include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
					} else {
						include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
					}	
				}
				echo '</div></div>';
			}
			echo '</div>';
			
			
			
			
			
			
			
			
			
			
			// Sidebar ( begin )
				echo '<div class="left-half">';
				$banner = '';
				if ( $board_banner != '' ) {
					$banner  = '<div class="banner piece text"><img src="' . $board_banner . '" alt="Banner" /></div>';
				}
				echo $banner;
				if ( !$user_exists && !$userisbanned ) {










					if ( isset ( $_REQUEST['password'] ) && $_REQUEST['password'] ) { 
						$password = sanitize_text_field ( wp_hash ( $_REQUEST['password'] ) ); 
					}
					if ( isset ( $_REQUEST['email'] ) && $_REQUEST['email'] ) { 
						$username = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) ); 
					}
					echo '<div class="reply">';

						$data = '';
						if ( $the_board  ) { 
							$data = $current_page . '?b=' . $the_board; 
						} elseif ( $this_thread ) { 
							$data = $current_page . '?t=' . $this_thread; 
						} else { 
							$data = $current_page; 
						}
						
						echo '<form enctype="multipart/form-data" data="' . $data . '" id="regularboard" name="i_want_to_log_in" method="post" action="' . $current_page . '">';
						wp_nonce_field('i_want_to_log_in');
						echo '<label for="email">username (will not be displayed)</label><input type="text" id="email" name="email" />';
						echo '<label for="password">password</label><input type="password" id="password" name="password"  />';
						if ( $registration_open ) {
							echo '<input type="submit" name="i_want_to_log_in" value="Sign-in" />
							<input type="submit" name="i_dont_want_to_sign_up" value="Create account" />';
						} else {
							echo '<input type="submit" name="i_want_to_log_in" value="Sign-in" />';
						}

						
						echo '</form>';
						if ( isset ( $_POST['i_dont_want_to_sign_up'] ) ) {
								if ( $check_ammount < $accounts_per_ip ) {
								$username = '';
								$password = '';
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
													user_logged_in_from,
													user_colormode,
													user_chanmode
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
													%s,
													%d,
													%d
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
											$user_ip,
											1,
											1
										) 
									);
									echo '<meta http-equiv="refresh" content="0;' . $this_page . '?a=options">';
								}
							} else {
								echo '<hr /><p class="information">You have too many accounts associated with this IP address.</p>';
							}
						}
						if ( isset ( $_POST['i_want_to_log_in'] ) && isset ( $_REQUEST['password'] ) && $_REQUEST['email'] && isset ( $_REQUEST['email'] ) && $_REQUEST['password'] ) {
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
									$login_limit = $wpdb->get_results ( "SELECT $regular_board_bans_select FROM $regular_board_bans WHERE banned_ip = '$user_ip' AND banned_message = 'bad password' LIMIT 1 " );
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
							}
						} 
					echo '</div>';				









					
				}
				if ( $this_area == 'history' && $user_exists || $this_user ) {
					echo '<div class="piece text">';
					echo '<strong>';
						if ( $the_profile_name ) { 
							echo $the_profile_name;
						} else {
							echo 'anonymous';
						}
					echo '</strong>';
					echo $the_profile_avatar . $the_profile_slogan . $the_profile_details . $connect_with;
					if ( count ( $my_friends ) > 0 ) {
						echo '<div class="text"><p>';
						echo 'Connections: ';
						foreach ( $my_friends as $friends ) {
							if ( $friends->friends_connector != $the_profile_name ) {
								$friend_name = sanitize_text_field ( $friends->friends_connector );
							}
							if ( $friends->friends_connectee != $the_profile_name ) {
								$friend_name = sanitize_text_field ( $friends->friends_connectee );
							}
							echo ' <a class="load_link" href="' . $this_page . '?u=' . $friend_name . '">' . $friend_name . '</a> ';
						}
						echo '</p></div>';
					}
					$check_friend = 0;
					$check_friend = $wpdb->get_var ( 
						"SELECT COUNT(*) FROM $regular_board_friends WHERE ( 
							friends_connector = '$profile_name' AND friends_connectee = '$the_profile_name' OR friends_connector = '$the_profile_name' AND friends_connectee = '$profile_name'
						)" 
					);
					if ( $user_exists) {
						if ( $the_profile_name ) {
							if ( $profile_name != $the_profile_name ) {
								if ( !$check_friend ) {
									if ( strtolower ( $_REQUEST['request_id'] ) != strtolower ( $profile_name ) ) {
										if ( isset ( $_POST['request_friendship'] ) ) {
											$wpdb->query ( 
												$wpdb->prepare ( 
													"INSERT INTO $regular_board_friends 
													( 
														friends_id, 
														friends_connector, 
														friends_connectee, 
														friends_mutual
													) VALUES ( 
														%d,
														%s,
														%s,
														%d
													)", 
													'', 
													$profile_name,
													$the_profile_name,
													0
												) 
											);
										}
									}
									if ( $the_profile_name ) {
										$connect_with = '
										<form method="post" name="friend_request" class="friendship" action="' . $current_page . '?u=' . $the_profile_name . '">'
										. wp_nonce_field( 'friend_request' ) . 
										'<section><input type="submit" name="request_friendship" id="request_friendship" value="Connect with this user" /></section>
										</form>';
									}
								}
							}
						}
					}
					echo '</div>';
				}
				$url_data = '';
				if ( $the_board && !$this_thread  ) { 
					$url_data = $current_page . '?b=' . $the_board; 
				} elseif ( $this_thread ) { 
					$url_data = $current_page . '?t=' . $this_thread; 
				} elseif ( $this_area ) { 
					$url_data = $current_page . '?a=' . $this_area; 
				} else { 
					$url_data = $current_page; 
				}
				if ( $user_exists ) {
					if ( isset ( $_POST['daymode_activate'] ) ) {
						$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 1 WHERE user_id = $profileid" );
						echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
					}
					if ( isset ( $_POST['nightmode_activate'] ) ) {
						$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 2 WHERE user_id = $profileid" );
						echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
					}
					if ( isset ( $_POST['tinymode_activate'] ) ) {
						$wpdb->query ( "UPDATE $regular_board_users SET user_chanmode = 1 WHERE user_id = $profileid" );
						echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
					}
					if ( isset ( $_POST['expandedmode_activate'] ) ) {
						$wpdb->query ( "UPDATE $regular_board_users SET user_chanmode = 2 WHERE user_id = $profileid" );
						echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
					}
					echo '<div class="piece text"><form class="modes" name="user_mode" method="post" action="' . $current_page . '">';
					wp_nonce_field( 'user_mode' );
					if ( $mode == 'night' ) {
						echo '<input type="submit" value="activate day mode" name="daymode_activate" />';
					}
					if ( $mode == 'day' ) {
						echo '<input type="submit" value="activate night mode" name="nightmode_activate" />';
					}
					if ( $style == 'tiny' ) {
						echo '<input type="submit" value="activate expanded mode" name="expandedmode_activate" />';
					}

					if ( $style == 'expanded' ) {
						echo '<input type="submit" value="activate tiny mode" name="tinymode_activate" />';
					}
					echo '</form></div>';
				}
				if ( $search_enabled ) {
					$search_action = $current_page;
					echo '<div class="piece text"><form name="regular_board_search" class="modes" method="post" action="' . $search_action . '">';
						wp_nonce_field('regular_board_search');
						echo '
						<input type="text" name="regular_board_search" id="regular_board_search" placeholder="Search" />
						<input type="submit" class="hidden" id="regular_board_search_submit" name="regular_board_search_submit" value="Search" />
					</form></div>';
				}

				if ( $user_exists && !$userisbanned ) { 
					if ( $user_create == 1 ) {
						echo '<span><a href="' . $current_page . '?a=create">[ <i class="fa fa-book"></i> ] Create a new board</a></span>';
					}
				}
				if ( $board_name ) {
					echo '<div class="piece text"><div class="text">' . $board_description . '</div>';
				}
				if ( $board_rules ) {
					echo '<div class="text">' . regular_board_format ( $board_rules ) . '</div>';
				}
				if ( $board_name ) {
					echo '</div>';
				}
				if ( get_option ( 'regular_board_frontpage' ) ) {
					echo '<div class="piece text"><div class="text">' . regular_board_format ( wpautop ( get_option ( 'regular_board_frontpage' ) ) ) . '</div></div>';
				}

				echo '<div class="piece text"><div class="tag_cloud"><span><a href="#">navigation</a></span>';
				if ( $protocol == 'boards' ) {
					foreach ( $getboards as $gotboards ) {
						if ( $gotboards->board_postcount > 0 ) {
							$percent = regular_board_percent ( $gotboards->board_postcount, $total_posts );
						} else {
							$percent = 0;
						}
						if ( !$percent ) { 
							$percent = 10; 
						} elseif ( $percent >= 1 && $percent <= 10 ) { 
							$percent = 11; 
						} elseif ( $percent >= 11 && $percent <= 20 ) { 
							$percent = 12;
						} elseif ( $percent >= 21 && $percent <= 30 ) {
							$percent = 13; 
						} elseif ( $percent >= 31 && $percent <= 40 ) {
							$percent = 14; 
						} elseif ( $percent >= 41 && $percent <= 50 ) {
							$percent = 15; 
						} elseif ( $percent >= 51 && $percent <= 60 ) {
							$percent = 16; 
						} elseif ( $percent >= 61 && $percent <= 70 ) { 
							$percent = 17; 
						} elseif ( $percent >= 71 && $percent <= 80 ) { 
							$percent = 18; 
						} elseif ( $percent >= 81 && $percent <= 90 ) { 
							$percent = 19; 
						} elseif ( $percent >= 91 && $percent <= 100 ) { 
							$percent = 20; 
						}
						echo '<span '; 
						if ( $percent == 10 ) { 
							echo 'class="nothing" '; 
						} 
						echo 'style="font-size:' . $percent . 'px;"><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '"'; 
						if ( $the_board && $the_board == $gotboards->board_shortname ) { 
							echo ' class="active"'; 
						} 
						echo '>' . $gotboards->board_name . '</a></span>';
					}
				}
				echo '<span><a href="' . $current_page . '?a=replies"'; 
				if ( $this_area == 'replies' ) { 
					echo ' class="active"'; 
				} 
				echo '>all replies</a></span>';
				echo '</div></div>';
				if ( dynamic_sidebar('Regular Board Widget') ) : else : endif;
				echo '</div>';			
			// Sidebar ( end )
			
			
			
			
			
			
			
			
			
			
			echo '</div></div>';
			
			if ( !$this_thread ) {
				echo '</div>';
			}
		
			if ( $regular_board_footer ) {
				echo '<div class="footer">' . $regular_board_footer . '</div>';
			}
		
			echo '<div class="bg-left"></div><div class="bg-right"></div>';
			echo '</div>';
		// Regular Board ( end )









		
	}










	// set a page title with js
	if ( $this_thread ) { 
		$page_title = $this_title; 
	} elseif ( $the_board ) { 
		$page_title = $the_board; 
	} elseif ( $the_tag ) { 
		$page_title = $the_tag; 
	} elseif ( $this_area ) { 
		$page_title = $this_area; 
	} elseif ( $this_user ) { 
		$page_title = $this_user; 
	} elseif ( $post_title ) {
		$page_title = $post_title;
	}
	if ( $page_title ) {
		$page_title = str_replace ( array( '\\', '"' ), '', $page_title );
		echo '<script type="text/javascript">
		document.title = "' . htmlentities($page_title) . '";
		</script>';
	}

	
	
	
	
	
	
	
	
	
}