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
		$the_board   = '';
		$this_thread = '';
		$getres      = '';

		$query       = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
		if ( $query ) {
			if ( isset ( $_GET['b'] ) ) {
				$the_board   = esc_sql ( strtolower ( $_GET['b'] ) );
			}
			if ( isset ( $_GET['t'] ) ) {
				$this_thread = intval ( $_GET['t'] );
			}
			if( $this_thread ) {
				$getres = $wpdb->get_results ( 
							$wpdb->prepare ( 
								"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE 
								post_id = %d LIMIT 1", 
								$this_thread 
							) 
						  );
				if ( count ( $getres ) ) {
					foreach ( $getres as $meta ) {
						if ( $meta->post_board ) {
							$the_board = $meta->post_board;
						}
						
						$canonical = $author = $title = $site = $locale = $published = $last = $image = $video = $description = '';
						
						$locale       = get_locale();
						$site         = get_bloginfo( 'name' );
						$current_page = home_url('/');
						$pretty       = esc_attr ( get_option ( 'mommaincontrol_prettycanon' ) );
						$the_board    = esc_sql ( strtolower ( $_GET['b'] ) );
						if ( !$meta->post_parent ) {
							$this_thread  = intval ( $_GET['t'] );
						}
						if ( $meta->post_parent ) {
							$this_thread  = intval ( $meta->post_parent ) . '#' . intval ( $_GET['t'] );
						}
						$canonical   = $current_page . '?t=' . $this_thread;
						$author      = $meta->post_moderator;
						$title       = str_replace ( '\\', '', $meta->post_title );
						if ( !$title ) {
							$title   = 'No subject';
						}

						$published   = $meta->post_date;
						$last        = $meta->post_last;
						$type        = $meta->post_type;
						if ( $type == 'image' ) {
							$image   = $meta->post_url;
						}
						if ( $type == 'youtube' ) {
							$video   = '//youtube.com/watch?v=' . $meta->post_url;
						}
						$description = str_replace ( array ( '||||', '||', '*', '{{', '}}', '>>', ' >', '~~', ' - ', '----', '::', '`', '    '), '', ( str_replace ( '\\', '', $description ) ) );
						$description = substr ( $description,0,150 );
						echo "\n";
						if ( $canonical ) {
							echo '<meta property="og:url" content="' . $canonical . '" /> ' . "\n";
						}
						if ( $title ) {
							echo '<meta property="og:title" content="' . $title . '" /> ' . "\n";
						}
						if ( $site ) {
							echo '<meta property="og:site_name" content="' . $site . '" /> ' . "\n";
						}
						if ( $locale ) {
							echo '<meta property="og:locale" content="' . $locale . '" /> ' . "\n";
						}
						if ( $image ) {
							echo '<meta property="og:image" content="' . $image . '" /> ' . "\n";
						}
						if ( $video ) {
							echo '<meta property="og:video" content="//www.youtube.com/v/' . $meta->post_url . '?autohide=1&amp;version=3" /> ' . "\n" . 
							'<meta property="og:video:type" content="application/x-shockwave-flash" /> ' . "\n" . 
							'<meta property="og:video:height" content="720" /> ' . "\n" . 
							'<meta property="og:video:width" content="1280" /> ' . "\n" . 
							'<meta property="og:type" content="video" /> ' . "\n" . 
							'<meta property="og:image" content="//img.youtube.com/vi/' . $meta->post_url . '/0.jpg" /> ' . "\n";
						} else {
							if ( $published ) {
								echo '<meta property="og:published_time" content="' . $published . '" /> ' . "\n";
							}
							if ( $published ) {
								echo '<meta property="og:modified_time" content="' . $published . '" /> ' . "\n";
							}
							if ( $last ) {
								echo '<meta property="og:updated" content="' . $last . '" /> ' . "\n";
							}
							echo '<meta property="og:type" content="article" /> ' . "\n";
						}
						if ( $description ) {
							echo '<meta property="og:description" content="' . $description . '" /> ' . "\n\n";
						}
					}
				}
			}
		}
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
			$front_link_class = $reports_link = $deleted_link = $queue_link = $video_link = $video_link_class = $all_link_class = $stuff_link_class = $home_link_class = $topics_link_class = $gallery_link_class = $history_link_class = $logout_link_class = $gallery_link = $all_link = $history_link = $logout_link = $options_link = $options_link_class = '';
			
			if ( $nothing_is_here ) { $front_link_class = ' class="active tip" '; }
			if ( $this_area == 'all' ) { $all_link_class = ' class="active tip" '; }
			if ( $this_area == 'stuff' ) { $stuff_link_class = ' class="active tip" '; }
			if ( $this_area == 'messages' ) { $stuff_link_class = ' class="active tip" '; }
			if ( $this_area == 'options' ) { $options_link_class = ' class="active tip" '; }
			if ( $this_area == 'blog' ) { $stuff_link_class = ' class="active tip" '; }
			if ( $this_area == 'news' ) { $stuff_link_class = ' class="active tip" '; }
			if ( $this_area == 'stats' ) { $stuff_link_class = ' class="active tip" '; }
			if ( $this_area == 'mod' ) { $stuff_link_class = ' class="active tip" '; }
			
			if ( $nothing_is_here ) { 
				if ( !$this_area ) {
					$home_link_class = ' class="active tip" '; 
				}
			}
			if ( $this_area == 'topics' || $the_board ) { 
				$topics_link_class  = ' class="active tip" '; 
			}
			if ( $the_board && $this_area == 'topics') { 
				$topics_link_class  = ' class="active tip" '; 
			}
			if ( $this_area == 'gallery' ) { 
				$gallery_link_class = ' class="active tip" '; 
			}
			if ( $this_area == 'history' ) { 
				$history_link_class = ' class="active tip" '; 
			}
			if ( $this_area == 'logout' ) { 
				$logout_link_class  = ' class="active tip"'; 
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

			$blog_link = '<a title="home" href="' . $current_page . '"' . $front_link_class . '>front</a>';
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

			$menu_toggle = ' <span>|</span> <a class="menutoggle_on" href="#">More</a><a class="menutoggle_off hidden" href="#">Less</a>';
			
			$navigation =  $board_current . $topics_link . $gallery_link . $video_link . $history_link . $stuff_link . $reports_link . $deleted_link . $queue_link . $options_link . $logout_link . $menu_toggle;
		// Navigation elements ( end ) 









		// Regular Board ( begin )
			echo '<div class="regular_board_board_all">';
			
			if ( $protocol == 'boards' ) {
				echo '<div class="top_nav">' .  $blog_link . ' <span>-</span> ' . $all_link . ' <span>|</span> ' . $navigation . ' </div>';
			}		
			
			echo '<div class="spacer">' . $banner;
			
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
				echo '<div class="threadcontainer"><div class="thread">';
				if ( count ( $getuser ) ) {
					foreach ( $getuser as $banneddetails ) {
						$LENGTH = $banneddetails->banned_length;
						$FILED = $banneddetails->banned_date;
						if ( strtolower ( $LENGTH ) != 'permanent' ) {
							$DATEFILED   = strtotime ( $banneddetails->banned_date );
							$CURRENTDATE = strtotime ( $current_timestamp );

							if ( strpos ( strtolower ( $LENGTH ), 'minute' ) ) {
								$bantime = intval ( $LENGTH ) * 60;
							} elseif (strpos ( strtolower ( $LENGTH ), 'hour' ) ) {
								$bantime = intval ( $LENGTH ) * 3600;
							} elseif (strpos ( strtolower ( $LENGTH ), 'day' ) ) {
								$bantime = intval ( $LENGTH ) * 86400;
							} elseif (strpos ( strtolower ( $LENGTH ), 'week' ) ) {
								$bantime = intval ( $LENGTH ) * 604800;
							} elseif (strpos ( strtolower ( $LENGTH ), 'month' ) ) {
								$bantime = intval ( $LENGTH ) * 2628000;
							} elseif (strpos ( strtolower ( $LENGTH ), 'year' ) ) {
								$bantime = intval ( $LENGTH ) * 31536000;
							} else {
								$bantime = intval ( $LENGTH ) * 60;
							}
							$banIsActiveFor = ( $DATEFILED + $bantime );
						} else {
							$LENGTH = 'permanent';
						}
						if ( $LENGTH != 'permanent' ) {
							if ( $CURRENTDATE > $banIsActiveFor ) { 
								$banLifted = 1;
							} else {
								$banLifted = 0;
							}
						} else {
							$banLifted = 0;
						}
						
						if ( $banned_image ) {
							echo '<img src="' . $banned_image . '" alt="Banned" class="imageFULL" />';
						}
						echo '<p>You are banned.</p>';
						foreach ( $getuser as $gotUser ) {
							$BANID   = intval ( $gotUser->banned_id );
							$BANNED  = intval ( $gotUser->banned_banned );
							$IP      = $gotUser->banned_ip;
							$MESSAGE = $gotUser->banned_message;
							$MESSAGE = regular_board_format ( $MESSAGE );
							if ( !$MESSAGE ) {
								$MESSAGE = '<em>No reason given</em>';
							}
							$filed_on = strtotime ( $FILED );
							$today_is = strtotime ( $current_timestamp );
							$unbanned = ( intval ( $bantime ) - ( intval ( $today_is ) - intval ( $filed_on ) ) );
							
							
							if ( $LENGTH != 'permanent' ) {
								echo '<p>Ban length: ' . $LENGTH . ' &mdash; ' . $unbanned . ' seconds until unbanned.</p>';
							} else {
								echo '<p>Ban length: PERMANENT</p>';
							}
							echo $MESSAGE . '</div>';
						}
						
						if ( $LENGTH != 'permanent' ) {
							if ( $unbanned <= 0 ) {
								$wpdb->delete ( $regular_board_bans, array('banned_id' => $BANID ), array ( '%d' ) );
							}
						}
						
						echo '</div>';
					}
				}
			}










			if ( $nothing_is_here ) {
				echo '<div id="threadthread">';
				if ( $getposts ) {
					echo '<div class="thread_container">';
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
					echo '&amp;controls=1&amp;showinfo=1&amp;autohide=1" width="100%" height="338" frameborder="0" allowfullscreen></iframe>
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
					if ( count ( $get_queue ) ) {
						foreach ( $get_queue as $posts ) {
							if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
								include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
							} else {
								include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
							}
							include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
						}
					} else {
						echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
					}
				} else {
					echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';				
				}
			} 
			elseif ( $this_area == 'options' && $user_exists ) { 
				if ( isset ( $_POST['options'] ) ) {
					include ( plugin_dir_path(__FILE__) . '/regular_board_user_options_form_action.php' );
				} ?>
				<?php 
				/** Begin User Options Form
				 ** This form will allow the user to set certain aspects of their account.
				 */ ?>
				<div class="thread_container">
					<div id="reply" class="reply">
						<form method="post" name="regularboard" action="<?php echo $current_page; ?>?a=options">
						<?php echo wp_nonce_field( 'regularboard' ); ?>
						<?php 
						/** Begin User Options Form Elements
						 ** Allows the user to set certain options for their account.
						 ** (1) User Avatar
						 ** (2) User Username
						 ** (3) User Password
						 ** (4) User Board Subscription
						 ** (5) User Following
						 ** (6) User Slogan
						 ** (7) User Always Anonymous
						 */
						/** (1) Begin User avatar
						 ** Allow the user to set an avatar image to display on their public profile.
						 ** ( maybe set up a thumbnailing ability to grab the image and create a smaller 
						 ** ( version of it server-side for faster loading for larger images? )
						 */
						if ( $profile_email ) {
						?>
						<section class="profile-section">
							<label class="small-left" for="avatar">
								<u>user photo</u>
								<hr />
								( .jpg, .png, .gif )
							</label>
							<?php if ( $profileavatar ) { ?>
								<img class="thumb right" src="<?php echo $profileavatar; ?>" alt="profile image" />
							<?php } else { ?>
								<i class="fa fa-picture-o"></i>
							<?php }?>
							<input type="text" name="avatar" id="avatar" value="<?php echo $profileavatar; ?>" />
						</section>
						<?php 
						}
						/** End User Avatar
						 */
						/** Begin User Username
						 ** To allow the user to log back in, the user must have a username and password
						 ** If the user neglected to sign up with the traditional method (using the quick
						 ** button, then they will need to be able to set their username at some point should 
						 ** they want to continue using their existing account.
						 */
						if ( !$profile_email ) { ?>
							<section class="profile-section">
								<label class="small-left" for="email">
									<u>username</u>
								</label>
								<i class="fa fa-lock"></i>
								<input type="text" name="email" id="email" />
							</section>
						<?php }
						/** End User Username
						 */
						/** Begin User Password
						 ** Allow the user to set a password.  If the user has already set a password, 
						 ** require that they enter their previous password as well as a new password 
						 ** to update the password that is already set.
						 */
						/** If no password has been set before
						 */
						if ( !$profilepassword ) { ?>
							<section class="profile-section">
								<label class="small-left" for="password">
									<u>password</u>
								</label>
								<i class="fa fa-key"></i>
								<input type="text" name="password" id="password" placeholder="<?php echo $random_password; ?>" />
							</section>
						<?php }
						/** If a password has been set
						 */
						if ( $profilepassword ) { ?>
							<section class="profile-section">
								<label class="small-left">
									<u>change password</u>
								</label>
								<i class="fa fa-key"></i>
								<input type="text" name="oldpassword" id="oldpassword" placeholder="Enter current password" />
								<input type="text" name="newpassword" id="newpassword" placeholder="Enter new password" />
							</section>
						<?php }
						/** End User Password
						 */
						/** Begin User Display Name
						 ** Allow the user to set a name that they wish to be displayed with their posts,
						 ** and that they wish to be publicly known by (all connection requests and follows
						 ** will depend on this name, however, should the user change it, all occurrences of
						 ** the name in the database will also be changed to reflect that.
						 */ 
						if ( $profile_email ) { ?>	
						<section class="profile-section">
							<label class="small-left" for="username">
								<u>display name</u>
							</label>
							<i class="fa fa-user"></i>
							<input type="text" name="username" id="username" placeholder="Your memorable name" 
							<?php if ( $profile_name != 'null' && $profile_name ) { ?>
								value = "<?php echo $profile_name; ?>"
							<?php } ?>
							/>
						</section>
						<?php 
						}
						/** End User Display Name
						 */
						/** Begin User Board Subscription
						 ** If use boards is set as such, and there are boards created, 
						 ** this option will be available to the user, allowing them to 
						 ** designate a comma-separated list of boards to which they wish 
						 ** to be subscribed.
						 */
						if ( $profile_email ) { 
							if ( $protocol == 'boards' ) {
								if ( count ( $getboards ) > 0 ) {
									if ( !$thisboard ) { ?>
									<section class="profile-section">
											<label class="small-left" for="boards">
												<u>subscribe to boards</u>
												<hr />
												comma-separated list of boards<br />
											</label>
											<i class="fa fa-sitemap"></i>
											<input type="text" name="boards" id="boards" value="<?php echo $boards; ?>" placeholder="Boards" />
										</section>
									<?php }
								}
							}
						}
						/** End User Board Subscription
						 */
						/** Begin User Following
						 ** Allow the user to designate a comma-separated list of usernames that they wish to 
						 ** follow, which acts like the subscribed list, but instead outputs a feed of 
						 ** specific user-generated content.
						 */ 
						if ( $profile_email ) { ?>
						<section class="profile-section">
							<label class="small-left" for="follow">
								<u>follow other users</u>
								<hr />
								comma-separated list of usernames<br />
							</label>
							<i class="fa fa-group"></i>
							<input type="text" name="follow" id="follow" value="<?php echo $profilefollow; ?>" placeholder="Usernames" />
						</section>
						<?php 
						}
						/** End User Following
						 */
						/** Begin User Slogan
						 ** Allow the user to affix a line of text to their public profile.
						 */ 
						if ( $profile_email ) {  ?>
						<section class="profile-section">
							<label class="small-left" for="slogan">
								<u>profile slogan</u>
								<hr />
								A quick introduction
							</label>
							<i class="fa fa-microphone"></i>
							<input type="text" name="slogan" id="slogan" value="<?php echo $profileslogan; ?>" />
						</section>
						<?php 
						}
						/** End User Slogan
						 */
						/** Begin User Always Anonymous
						 ** Allow the user to determine whether or not they always wish to post anonymously, 
						 ** which will prevent any of their posts from being publicly tied to their profile.
						 */ 
						if ( $profile_email ) {  ?>
						<section class="profile-section">
							<label class="small-left">
								<u>always anonymous</u>
								<hr />
								Always post anonymously<br />
							</label>
							<i class="fa fa-volume-off"></i>
							<select name="heaven" id="heaven">
								<option <?php if ( !$profileheaven ){ ?> selected="selected" <?php } ?> value="0">no</option>
								<option <?php if ( $profileheaven ){ ?> selected="selected" <?php } ?> value="1">yes</option>
							</select>
						</section>
						<?php 
						}
						/** End User Always Anonymous
						 */ ?>
							<input type="submit" name="options" id="options" value="Save these options" />
						</form>
						<?php 
						/** End User Options Form
						 */ ?>
							
						<?php 
						/** Begin Connections
						 ** If the user has incoming connections, they will be displayed here.
						 ** They can then decide to either decline or accept the connection invitation.
						 */
						if ( count ( $my_waiting ) > 0 ) {
							foreach ( $my_waiting as $waiting ) {
								$this_form = $waiting->friends_id;
								if ( isset ( $_POST['accept' . $this_form . ''] ) ) {
									$wpdb->query ( "UPDATE $regular_board_friends SET friends_mutual = 1 WHERE friends_id = $this_form" );
									echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=options"></p>';
								}
								if ( isset ( $_POST['decline' . $this_form . ''] ) ) {
									$wpdb->delete ( $regular_board_friends, array ( 'friends_id' => $this_form ), array ( '%d' ) );
									echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=options"></p>';
								} ?>
								<form class="friend_request" method="post" action="<?php echo $current_page; ?>?a=options">
								<section>
									<label>
										<?php echo sanitize_text_field ( $waiting->friends_connector ); ?> wants to connect
									</label>
									<input type="submit" name="decline<?php echo $waiting->friends_id; ?>" value="Decline" />
									<input type="submit" name="accept<?php echo $waiting->friends_id; ?>" value="Accept" />
								</section>
								</form>
							<?php }
						} ?>
						<?php 
						/** End Connections
						 */ ?>
					</div>
				</div>
			
			
			
			
			
			
			
			
			
			<?php 
			} elseif ( $this_area == 'history' && $user_exists || $this_user ) { 
				if ( $this_area == 'history' ) {
					$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid ) );
				} elseif ( $this_user ) {
					$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_name = %s LIMIT 1", $this_user ) );
				}

				$the_profile_name = $the_profile_avatar = $the_profile_slogan = $the_profile_details = $connect_with = '';
				if ( count ( $usprofile ) ) {
					foreach ( $usprofile as $theprofile ) {
							$this_user_exists = 1;
							if ( $theprofile->user_name ) {
								$the_profile_name = sanitize_text_field ( $theprofile->user_name );
							}
							if ( $theprofile->user_avatar ) {
								if ( $theprofile->user_avatar != 'NULL' ) {
									$the_profile_avatar = '<img src="' . $theprofile->user_avatar . '" class="imageFULL" />';
								}
							}
							if ( $theprofile->user_slogan ) {
								if ( $theprofile->user_slogan != 'NULL' ) {
									$the_profile_slogan = '<div class="text"><p><em>' . str_replace ( '\\', '', $theprofile->user_slogan ) . '</em></p></div>';
								}
							}
							$the_profile_details = '<div class="text"><p>level ' . $theprofile->user_level . '<br />active posts: ' . $totalpages . ' /
							total posts: ' . $theprofile->user_posts . ' <br />
							 member for ' . str_replace ( 'ago', '', regular_board_timesince ( $theprofile->user_date ) ) . ' </p></div>';
							
							
						if ( $totalpages ) {
							foreach ( $getposts as $posts ) {
								if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
									include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
								} else {
									include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
								}
							}
						} else {
							echo '<div class="thread"><center><em>nothing to see here.</em></center></div>';
						}
					}
				} else {
					$this_user_exists = 0;
					echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
				}

				include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );				
			
			
			
			
			
			
			
			
			
			
			} elseif ( $this_area == 'stats' ) { 
				$thread_count = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_parent = 0" );
				$reply_count = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_parent > 0" );
				$ten_minutes = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$ten_minutes_ago' AND '$current_timestamp'" );
				$two_hours = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$two_hours_ago' AND '$current_timestamp'" );
				$twelve_hours = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$twelve_hours_ago' AND '$current_timestamp'" );
				$month = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$one_month_ago' AND '$current_timestamp'" );
				$day = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$one_day_ago' AND '$current_timestamp'" );
				$count_users = ( $wpdb->get_var( "SELECT COUNT(Distinct user_id) FROM $regular_board_users WHERE user_posts > 0 " ) + $wpdb->get_var( "SELECT COUNT(Distinct post_guestip) FROM $regular_board_posts" ) );
				$count_boards = $wpdb->get_var( "SELECT COUNT(Distinct post_board) FROM $regular_board_posts" );


				echo '<div class="thread"><h1>Installation statistics</h1>

				<p>
					Statistics are based on <strong>active</strong> content.<br />
					This page does not take into account posts that have been deleted or marked as spam.
				</p>

				<p>
					<strong>Post statistics</strong>:<br />
					There are <strong>' . $thread_count . '</strong> active threads with <strong>' . $reply_count . '</strong> active comments.<br />
					Within the last ten minutes, <strong>' . $ten_minutes . '</strong> posts were made.<br />
					<strong>' . $two_hours . '</strong> within the last two hours, and <strong>' . $twelve_hours . '</strong> within the last 12 hours.<br />
					<strong>' . $month . '</strong> within the last month.<br />
					Within the last day, there have been <strong>' . $day . '</strong> posts created.
				</p>

				<p>
					<strong>User statistics</strong>:<br />
					There have been ' . $count_users . ' unique posters.
				</p>

				<p>
					<strong>Board statistics</strong>:<br />
					There are currently ' . $count_boards . ' active boards.
				</p>
				</div>';
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
									$nothing = 1;
									echo '<div id="thread' . $thread_div_id . '"><div class="thread clear"><p><strong>Nothing to see here.</strong></p></div></div>';
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
				echo '<div id="post" class="thread">';
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
					} else {
						$URL = sanitize_text_field ( wp_strip_all_tags( $_REQUEST['URL'] ) );
					}
					include ( plugin_dir_path(__FILE__) . '/regular_board_post_action.php' );
				} else { 
					echo '<p>Nothing submitted.';
				}
				echo '</div>';










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
					echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
				}
				echo '</div></div>';
			}










			elseif ( $this_area == 'news' || $enable_blog && $this_area == 'blog' ) { 
				if ( isset ( $_GET['post'] ) ) {
					$postno = intval ( $_GET['post'] );
				}
				if ( $this_area == 'news' ) {
					if ( $postno ) {
						$args = array (
						'p' => $postno,
						);					
					} else {
						$args = array (
							'showposts' => -1,
							'category__in' => array ( $announcements ),
						);
					}
				} else {
					if ( $postno ) {
						$args = array (
						'p' => $postno,
						);					
					} else {
						$args = array (
							'showposts' => -1,
							'category__not_in' => array ( $announcements ),
						);
					}				
				}
				$posts = get_posts ( $args );
				if ( $posts ) {
					if ( $postno ) {
						echo '<div class="thread clear"><p><a class="load_link" href="' . $this_page . '?a=' . $this_area . '">More site announcements</a></p></div>';
					}
					foreach ( $posts as $post ) {
						setup_postdata ( $post );
							echo '<div class="thread clear">
							<strong class="left"><a class="load_link" href="' . $this_page . '?a=' . $this_area . '&amp;post=' . $post->ID . '">' . $post->post_title . '</a></strong>
							<span class="right">' . regular_board_timesince( $post->post_date ) . '</span>
							</div>
							<div class="thread clear">';
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










			elseif ( $this_area == 'mod' ) { 
				if ( count ( $mod_logs ) ) {
					echo '<div class="thread"><div class="right">Age</div><div class="left">Message</div></div>';
					foreach ( $mod_logs as $logs ) {
						echo '<div class="thread"><div class="right">' . regular_board_timesince( $logs->logs_date ) . '</div><div class="left">' . $logs->logs_message . '</div></div>';
					}
				} else { 
					echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
				}
			}










			elseif ( $this_area == 'stuff' ) { 
				echo '<div class="thread_container">
				<div class="container_half">
					<em>Tools/info</em>:
					<ul>';
					if ( $user_exists ) {
						echo '<li><a class="load_link" href="' . $current_page . '?a=messages">messages</a> &mdash; you have ' . $my_unread . ' unread messages.</li>
						<li><a class="load_link" href="' . $current_page . '?a=options">options</a> &mdash; your personal settings / you have ' . $my_waitings . ' connections pending.</li>';
					}
					if ( $enable_blog ) {
						echo '<li><a class="load_link" href="' . $current_page . '?a=blog">blog</a> &mdash; words and thoughts</li>';
					}
					if ( $announcements ) {
						echo '<li><a class="load_link" href="' . $current_page . '?a=news">news</a> &mdash; announcements and site news</li>';
					}
					echo '
					<li><a class="load_link" href="' . $current_page . '?a=stats">stats</a> &mdash; board statistics</li>
					<li><a class="load_link" href="' . $current_page . '?a=mod">moderation log</a></li>
					</ul>
				</div>';
				if ( $protocol == 'boards' ) {
					if ( count ( $getboards ) > 0 ) {
						echo '<div class="container_half">
						<em>Active boards</em>:
						<ul>';
							foreach ( $getboards as $gotboards ) {
								if ( $gotboards->board_postcount > 0 ) {
									echo '<li><a class="load_link" href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_shortname . '</a></li>';
								}
							}
						echo '</ul>
						</div>';
					}
				}
				echo '</div>';
			}










			elseif ( $this_area == 'messages' && $user_exists ) { 
				echo '<div class="thread_container">';
				if ( count ( $my_messages ) ) {
					foreach ( $my_messages as $messages ) {
					
						$messages->messages_id      = intval ( $messages->messages_id );
						$messages->messages_to      = sanitize_text_field ( $messages->messages_to );
						$messages->messages_from    = sanitize_text_field ( $messages->messages_from );
						$message_to                 = $messages->messages_to;
						$message_from               = $messages->messages_from;
						$message_id                 = $messages->messages_id;
						$messages->messages_read    = intval ( $messages->messages_read );
						$messages->messages_date    = sanitize_text_field ( $messages->messages_date );
						$messages->messages_subject = sanitize_text_field ( $messages->messages_subject );
						if ( !$messages->messages_subject ) {
							$messages->messages_subject = 'No subject';
						}
						$messages->messages_subject = '<a class="load_link" href="' . $this_page . '?a=messages&message=' . $messages->messages_id . '">' . $messages->messages_subject . '</a>';
						
						$messages->messages_content = str_replace ( array ( '\\n', '\\r', '\\'), array( '<br />','<br />','' ), $messages->messages_content );
						if ( $formatting ) {
							$messages->messages_content = regular_board_format( $messages->messages_content );
						} else {
							$messages->messages_content = $messages->messages_content;
						}
						$messages_id = $messages->messages_id;
						if ( $messages->messages_from != $profile_name ) {
							if ( !$messages->messages_read ) {
								$wpdb->query ( "UPDATE $regular_board_messages SET messages_read = 1 WHERE messages_id = $messages_id" );
							}
						}
						
						echo '<div class="thread">
						' . $messages->messages_subject . ' &mdash; 
						To: ' . $messages->messages_to ;
							if ( $messages->messages_to == $profile_name ) {
								echo ' (you) ';
							}
						echo '&mdash; From: ' . $messages->messages_from;
							if ( $messages->messages_from == $profile_name ) {
								echo ' (you) ';
							}
						if ( $messages->messages_from != $profile_name && !$messages->messages_read || isset ( $_GET['message'] ) ) {
							echo '<div class="comment">' . wpautop ( $messages->messages_content ) . '</div>';
						}
						
						if ( isset ( $_POST['delete' . $message_id . ''] ) && $messages->messages_to == $profile_name ) {
							$wpdb->delete ( $regular_board_messages, array ( 'messages_id' => $message_id ), array ( '%d' ) );
							echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=messages"></p>';
						}
						
						if ( $messages->messages_from != $profile_name ) {
							echo '<form class="right" name="message' . $message_id . '" method="post" action="' . $current_page . '?a=messages">';
							wp_nonce_field('regularboard' . $message_id . '');
							echo '<input type="submit" value="Delete" name="delete' . $message_id . '" />
							</form>';
						}
						echo '</div>';
					}
				}
				echo '</div>';
			} 
			
			
			
			
			
			
			
			
			
			
			elseif ( $this_area == 'logout' && $user_exists   ) {
				if ( $profile_name && $profilepassword ) {
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
				}
				echo '<div class="thread clear"><p>You are now logged out.</p></div>';
			}










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
				echo '<div class="left-half hidden">';
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
				
				if ( $this_area == 'history' && $user_exists || $this_user && $this_user_exists ) {
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

				echo '<div class="piece text"><div class="tag_cloud">';
				if ( $protocol == 'boards' ) {
					foreach ( $getboards as $gotboards ) {
						echo '<span><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_name . '</a></span>';
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