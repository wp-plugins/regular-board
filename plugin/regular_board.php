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
	if ( $ipaddress !== false ) {
		include ( plugin_dir_path(__FILE__) . '/regular_board_strings.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_user_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_loop_queries.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_wipe.php' );

		
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
		if ( $lock == 1 ) {
			if ( $is_user ) {
				$posting = 0;
			}
			if ( $is_user !== true ) {
				$posting = 1;
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
		echo '<small class="clear smallstats">
		
		<i class="fa fa-user" title="You are using ' . $check_ammount . ' of ' . $accounts_per_ip . ' user slots available to you."> ' . $check_ammount . ' / ' . $accounts_per_ip . '</i>
		 &mdash; 
		<i class="fa fa-users" title="Accounts total"> ' . $count_users_total;
		if ( $user_total_allowed ) {
			echo ' / ' . $user_total_allowed . ' / ' . $count_logged_total; 
		}
		echo '</i>
		&mdash; 
		<i class="fa fa-pencil" title="Active posts / total posts created (overall)"> ' . $posts_active_total . ' / ' . $posts_users_total . '</i>
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
				echo '<label for="email">email</label><input type="text" id="email" name="email" />';
				echo '<label for="password">password</label><input type="password" id="password" name="password"  />';
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
		echo '<div class="tag_cloud"><span><a href="#">navigation</a></span>';
		
		if ( $protocol == 'boards' ) {
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
			if ( $protocol == 'boards' ) {
				if ( count ( $getboards ) == 1 ) {
					foreach ( $getboards as $board ) {
						$the_board = $board->board_shortname;
					}
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
		} elseif ( $the_board || $this_thread || $the_tag ) {
			if ( $the_tag ) {
				echo '<div class="thread">All posts tagged <strong> #' . $the_tag . '</strong></div>';
			}
			include ( plugin_dir_path(__FILE__) . '/regular_board_posting_checkflood.php' );
				if ( count ( $get_current_board ) > 0 && $protocol == 'boards' || $protocol == 'tags' && $the_board || $this_thread || $the_tag && $protocol == 'boards' ) {
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
			</div>';
			if ( $protocol == 'boards' ) {
				echo '<div class="container_half">
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