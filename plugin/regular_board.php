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
		 
		$user_logged_in        = 0;
		if ( is_user_logged_in() ) {
			$user_logged_in    = 1;
		}
		$user_exists           = 0;
		$require_logged        = 0;
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
		$blog_title            = get_bloginfo();
		$announcements         = get_option ( 'regular_board_announcements' );
		$max_links             = get_option ( 'regular_board_maxlinks' );
		$posting_options       = get_option ( 'regular_board_postingoptions' );
		$search_enabled        = get_option ( 'regular_board_search' );
		$display_boards        = get_option ( 'regular_board_displayboards' );
		$display_menu          = get_option ( 'regular_board_displaymenu' );
		$display_wipe          = get_option ( 'regular_board_wipedisplay' );
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
		$the_ip                 = $ipaddress;
		$user_ip               = sanitize_text_field ( wp_hash ( $the_ip ) );
		$check_this_ip         = sanitize_text_field ( $the_ip );
		$current_timestamp     = date ( 'Y-m-d H:i:s' );
		$regular_board_posts   = $wpdb->prefix . 'regular_board_posts';
		$regular_board_boards  = $wpdb->prefix . 'regular_board_boards';
		$regular_board_users   = $wpdb->prefix . 'regular_board_users';
		$regular_board_bans    = $wpdb->prefix . 'regular_board_bans';
		$regular_board_logs    = $wpdb->prefix . 'regular_board_logs';
		$query                 = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
		$the_board             = sanitize_text_field ( strtolower( $_GET['b'] ) );
		$this_area             = sanitize_text_field ( strtolower( $_GET['a'] ) );
		$this_user             = sanitize_text_field ( strtolower( $_GET['u'] ) );
		$this_thread           = intval ( $_GET['t'] );
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
				$profileid        = intval ( $myinfo->user_id );
				$profileheaven    = intval ( $myinfo->user_heaven );
				$profile_email    = sanitize_text_field ( $myinfo->user_email );
				$profile_name     = sanitize_text_field ( $myinfo->user_name );
				if ( !$profile_name ) {
					$profile_name = 'anonymous';
				}
				$profilepassword  = sanitize_text_field ( $myinfo->user_password );
				$profilefollow    = sanitize_text_field ( $myinfo->user_follow );
				$following        = sanitize_text_field ( $myinfo->user_follow );
				$boards           = sanitize_text_field ( $myinfo->user_boards );
				$profileboards    = sanitize_text_field ( $myinfo->user_boards );
				$following        = sanitize_text_field ( $profilefollow );
				$user_exists      = 1;
				if ( $profileboards ) {
					$profileboards       = explode   ( ',', $profileboards );
					$profileboards       = array_map ( 'regular_board_apply_quotes', $profileboards );
				}
				if( $following ) {
					$following    = explode   ( ',', $following );
					$following    = array_map ( 'regular_board_apply_quotes', $following );
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
		 
		if( $the_board ) {
			$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
		}
		
		/**
		 * If there is only one board, get results as if it's the current board.
		 */
		 
		if(!$the_board && $thisboard ){
			$get_current_board  = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thisboard ) );
		}
		
		/**
		 * Our search input
		 */
		 
		if ( $search_enabled && isset ( $_POST['regular_board_search_submit'] ) && $_REQUEST['regular_board_search'] ) {
			$search = esc_sql ( $_REQUEST['regular_board_search'] );
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
				$wipe_countdown    = '';
				if( $boardwipe && $boardwipe != 'never' ) {
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
					$next_clean = date($boarddate, time() + $nextwipe);

					if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
						$wipe_countdown = '<i class="fa fa-clock-o"> ' . $next_clean . ' ( ' . $next_wipe . ' seconds ) </i>';
					}
				}
				if( $board_description ) {
					$boardheader      = '<p class="boardheader"><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . '</a> &mdash; [ ' . $board_short . ' ] &mdash; ' . $board_description . ' &mdash; ' . $wipe_countdown . '</p>';
				}
				if( !$board_description ) {
					$boardheader      = '<p class="boardheader"><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . '</a> &mdash; [ ' . $board_short . ' ] &mdash; ' . $wipe_countdown;
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
		if( !$the_board && $this_area == 'all' && !$this_thread && !$this_user ) {
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
		if ( $the_board && $this_thread && !$this_user ) {
			$use_this++;
			$where_by = "WHERE post_board = '$the_board' AND post_id = $this_thread AND post_parent = 0";
			$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_board = %s AND post_parent = %d", $the_board, $this_thread ) );
		}
		if( $this_area == 'history' || $this_user ) {
			$use_this++;
			$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE ( user_id = %d OR user_name = %s ) LIMIT 1", $profileid, $this_user ) );
			$where_by = "WHERE ( post_userid = $profileid OR post_name = '$this_user' )";
			$order_by = "post_date DESC";
		}
		if ( $use_this > 0 ) {
			$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by" );
			$results    = intval ( $_GET['n'] );
			if( $results ) {
				$start = ( $results - 1 ) * $posts_per_page;
			} else {
				$start = 0;
			}
			$getposts = $wpdb->get_results( "SELECT * FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
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
		
		if ( count ( $myinformation ) == 0 ) {
			
			/**
			 * If the associated IP has no infromation in the _user table, automatically 
			 * create an entry for it and refresh (whatever) page the user is on.
			 */
			 
			echo '<div class="registration">';
			
				echo '<form class="i_am_a_human" enctype="multipart/form-data" name="i_am_a_human" method="post" action="' . $current_page . '">
				<p>New here?  Just click the button to begin using the boards (information optional).</p>';
				wp_nonce_field('i_am_a_human');
				
	echo '<label>E-mail (doesn\'t have to be real.)</label>
	<input type="text" name="email" id="email" placeholder="you@there.com" value="' . $profile_email . '" />
	<label>Desired username (can change later)</label>
	<input type="text" name="USERNAME" id="USERNAME" placeholder="Desired username" value="' . $profile_name . '" />
	<label>Password</label>
	<input type="password" name="password" id="password" placeholder="' . $random_password . '" />';
					
				
				echo '<input type="submit" name="i_am_a_human" value="Finish automatic registration" />
				</form>';
				if ( isset ( $_POST['i_am_a_human'] ) ) {
					$name     = sanitize_text_field ( $_REQUEST['USERNAME'] );
					$email    = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) );
					$password = sanitize_text_field ( wp_hash ( $_REQUEST['password'] ) );
					$wpdb->query( $wpdb->prepare ( "INSERT INTO $regular_board_users ( user_id, user_date, user_ip, user_name, user_email, user_password, user_heaven, user_boards, user_follow ) VALUES ( %d, %s, %s, %s, %s, %s, %d, %s, %s )", '', $current_timestamp, $user_ip, $name, $email, $password, 0, '', '' ) );
					echo '<meta http-equiv="refresh" content="0">';			
				}

				if ( isset ( $_POST['restore'] ) ) {
					if ( $_REQUEST['oldinternalid'] && $_REQUEST['userpassword'] ) {
						$userpassword = sanitize_text_field ( wp_hash ( $_REQUEST['userpassword'] ) );
						$userip       = sanitize_text_field ( $_REQUEST['oldinternalid'] );
						$email        = sanitize_text_field ( wp_hash ( $_REQUEST['oldemail'] ) );
						$check_this = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_password = %s AND user_ip = %s", $userpassword, $userip ) );
						if ( count ( $check_this ) > 0 ) {
							$wpdb->query ( "UPDATE $regular_board_users SET user_ip = '$user_ip' WHERE user_ip = $userip AND user_password = '$userpassword' AND user_email = '$email'" );
							echo '<p>User ID restored.</p>';
						}
					}
				}				
				echo '<form method="post" name="restoreid" action="' . $current_page . '?a=options">
				<p>Already had an account you wish to restore?  Enter your old credentials here:</p>';
				wp_nonce_field( 'restoreid' );
				echo '
					<label for="oldinternalid">Previous internal ID</label><input type="text" id="oldinternalid" name="oldinternalid" placeholder="Your (old) internal ID" />
					<label for="userpassword">Previous password</label><input type="text" id="userpassword" name="userpassword" placeholder="Password" />
					<label for="email">Associated e-mail</label><input type="text" id="oldemail" name="oldemail" placeholder="you@there.com" />
					<input type="submit" name="restore" id="restore" value="Restore your ID" /></p>
				</form>';
			echo '</div>';
		}
		
		if ( $announcements ) {
			
			$cat_args=array(
			'include' => intval ( $announcements )
			);
			$categories=get_categories($cat_args);
			foreach($categories as $category) {
				$args=array(
				'showposts' => 3,
				'category__in' => array ( $category->term_id ),
				'caller_get_posts'=> 1
				);
				$posts = get_posts ( $args );
				if ( $posts ) {
					echo '<span class="announcement">Latest announcement(s): ';
					foreach($posts as $post) {
						setup_postdata($post); 
							echo '<a href="' . protocol_relative_url_dangit ( get_permalink($post->ID) ) . '" rel="bookmark" title="Permanent Link to '; the_title_attribute(); echo '">'; the_title(); echo '</a>';
					}
					
					echo '</span>';
				}
			}
		}
		
		if ( $display_menu ) {
			echo '<p class="boardList">';
			echo '<span class="navi">';
			if ( $user_exists && $profileboards ) {
				echo ' <a href="' . $current_page . '?a=subscribed">subscribed</a> ';
			}
			if ( $user_exists && $profilefollow ) {
				echo ' <a href="' . $current_page . '?a=following">following</a> ';
			}
			echo ' <a href="' . $current_page . '?a=topics">topics</a> 
			<a href="' . $current_page . '?a=replies">replies</a> 
			<a href="' . $current_page . '?a=all">all</a> 
			<a href="' . $current_page . '?a=gallery">gallery</a> 
			<a href="' . $current_page . '?a=stats">stats</a> ';
			if ( $user_exists ) {
				echo ' <a href="' . $current_page . '?a=history">history</a> ';
				echo ' <a href="' . $current_page . '?a=options">options</a> ';
			}
			if ( $is_moderator && count($get_reports) > 0 ) {
				echo '<a href="' . $current_page . '?a=reports">reports ( ' . count ( $get_reports ) . ' )</a>';
			}
			if ( $is_moderator && count($get_deleted) > 0 ) {
				echo '<a href="' . $current_page . '?a=deleted">deleted ( ' . count ( $get_deleted ) . ' )</a>';
			}		
			if ( $this_area != 'newtopic' && $user_exists ) {
				if ( $the_board && $posting == 1 && $user_exists && !$this_area || $thisboard ) {
					if ( $thisboard ) {
						echo ' <a class="newtopic" href="' . $current_page . '?a=newtopic">new</a> ';
					} else {
						echo ' <a class="newtopic" href="' . $current_page . '?b=' . $the_board . '&amp;a=newtopic">new</a> ';
					}
					echo '<span class="hidden notopic">cancel</span>';
				}
			}
			echo '</span>';
			echo '</p>';
			echo '<p class="newtopic"></p>';
		}
		if ( count ( $getboards ) > 0 ) {
			if ( $display_boards ) {
				echo '<span class="boards">';
			}
			foreach ( $getboards as $gotboards ) {
					if( $gotboards->board_wipe && $gotboards->board_wipe != 'never' ) {
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
				if ( $display_boards ) {
					echo ' <a href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_shortname . ' ( ' . $gotboards->board_name . ' ) </a> ';
				}
			}
			if ( $display_boards ) {
				echo '</span>';
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
		} elseif ( $the_board ) {
			include ( plugin_dir_path(__FILE__) . '/regular_board_posting_checkflood.php' );
				if ( count ( $get_current_board ) > 0 ) {
					if ( !$user_logged_in && $require_logged == 1 ) {
						echo '<div class="thread"><p>You are not logged in.</p></div></div>';
					} elseif ( !$user_logged_in && $require_logged == 0 || $user_logged_in ) {
						foreach ( $get_current_board as $gotCurrentBoard ) {
							$boardName = $gotCurrentBoard->board_name;
							$boardShort = $gotCurrentBoard->board_shortname;
							$boardDescription = regular_board_format($boardDescription);
							if ( $DNSBL ) {
								$wpdb->query( $wpdb->prepare( "INSERT INTO $regular_board_bans ( banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length ) VALUES ( %d, %s, %s, %d, %s, %s )", '', $current_timestamp, $user_ip, 1, 'DNSBL', 0 ) );
							} elseif ( count ( $getuser ) > 0 ) {
								include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' );
							} else {
								if ( $userisbanned == 0 ) {
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
											} elseif ( $the_board && $this_thread ) { 
												$website_url = $current_page . '?b=' . $the_board .'&amp;t=' . $this_thread; 
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
												echo '<p>No results.</p>';
											}
										}
										if ( $this_thread && $threadexists == 1 ) {
											echo '<p>';
											if ( $thisboard ) {
												echo '<a href="' . $current_page . '">Return</a>';
											} else {
												echo '<a href="' . $current_page . '?b=' . $the_board . '">Return</a>';
											}
											echo '<a href="#top">Top</a><a class="reload" data="' . $current_page . '?b=' . $the_board . '&amp;t=' . $this_thread . '">Update</a>
											</p>';
										}
									}
								}
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
			echo '</div></div>';
		} elseif ( $this_area == 'gallery' || $this_area == 'all' || $this_area == 'replies' || $this_area == 'topics' || !$this_area ) {
			foreach ( $getposts as $posts ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
				}
			}
		}
	echo '</div>';
	}
}