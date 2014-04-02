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

function regular_board_head ( $atts ) {
	global	$wp, 
			$post, 
			$wpdb, 
			$regular_board_posts_select;
	
	$content              = $post->post_content;
	$regular_board_posts  = $wpdb->prefix . 'regular_board_posts';
	$regular_board_boards = $wpdb->prefix . 'regular_board_boards';
	
	if ( has_shortcode ( $content, 'regular_board' ) ) {
		include ( plugin_dir_path(__FILE__) . '/regular_board_meta.php' );
		if ( get_option ( 'regular_board_robots' ) ) {
			echo '<meta name="robots" content="noindex,nofollow"/>';
		}
	}
}

function regular_board_shortcode ( $content = null ) {
	
	global	$wpdb,
			$wp,
			$post,
			$ipaddress,
			$random_password,
			$regular_board_version,
			$regular_board_posts_select,
			$regular_board_users_select,
			$regular_board_boards_select,
			$regular_board_bans_select;
	
	if ( $ipaddress !== false ) { 
		include ( plugin_dir_path(__FILE__) . '/regular_board_strings.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_user_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_loop_queries.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_wipe.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_determine_mod.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_navigation_elements.php' );
		
		echo '<div class="boardAll">';
		
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
				$board_nom++;
				if ( $board_nom > 1 && $board_nom <= count ( $getboards ) ) {
				 echo '<span>-</span>';
				}
				echo '<a href="' . $current_page . '?b=' . $gotboards->board_shortname . '"'; if ( $the_board && $the_board == $gotboards->board_shortname ) { echo ' class="active"'; } echo '>';
				echo $gotboards->board_name . '</a>';
			}
			echo '</div>';
		}		
		
		echo '<div class="spacer">' . $banner . $navigation;
		
		if ( $userisbanned ) { include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' ); }
		
		echo '<div class="right-half">';
		
		if ( $nothing_is_here ) {
			echo '<div class="omitted">';
			if ( $getposts ) {
				echo '<div class="thread_container">
					<span class="frontinfo">';
						if ( !$search ) {
							if ( !$profileboards ) {
								echo 'Latest activity';
							} else {
								echo 'Latest activity based on your subscriptions';
							}
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
			echo '</div></div>';
		}


		
		include ( plugin_dir_path(__FILE__) . '/regular_board_posting_deletepost.php' );
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
				echo '&amp;controls=1&amp;showinfo=1&amp;autohide=1" width="480" height="360" frameborder="0" allowfullscreen></iframe>
				</div>';
			} else {
				echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
			}
		} elseif ( $this_area == 'create' && $user_create == 1 ) {
			if ( $user_exists && $profile_level > 2 ) {
				echo '<h1>Create a new board</h1>';
				$board_name         = '';
				$board_shortname    = '';
				$board_description  = '';
				$board_moderators   = '';
				$board_janitors     = '';
				$board_locked       = '';
				$board_logged       = '';
				$board_wipe         = '';
				$board_rules        = '';
				if ( isset ( $_POST['save_newboard'] ) && $_REQUEST['board_shortname'] ) {
					$board_name          = sanitize_text_field ( preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['board_name'] ) );
					$board_shortname     = sanitize_text_field ( preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['board_shortname'] ) );
					$board_rules         = sanitize_text_field ( $_REQUEST['board_rules'] );
					$board_description   = sanitize_text_field ( $_REQUEST['board_description'] );
					if ( $board_shortname ) {
						$regular_board_board = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_boards WHERE board_shortname = '$board_shortname'" );
					}
					if ( $regular_board_board == 0 ) {
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
				if     ( $the_board  ) { $data = $current_page . '?b=' . $the_board; }
				elseif ( $this_thread ) { $data = $current_page . '?t=' . $this_thread; }
				else   {                  $data = $current_page; }				
				
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
		
		elseif ( $this_area == 'editpost' && $user_exists && $this_thread ) { include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php'    ); }
		elseif ( $this_area == 'options' && $user_exists                  ) { include ( plugin_dir_path(__FILE__) . '/regular_board_user_options.php' ); } 
		elseif ( $this_area == 'history' && $user_exists || $this_user    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_profile_loop.php' ); }
		elseif ( $this_area == 'stats'                                    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_board_stats.php'  ); } 

		elseif ( $the_board || $this_thread || $the_tag ) {
			if ( $the_tag || $the_board ) {
				if ( $the_tag ) { $the_board = $the_tag; }
				if ( $the_board ) { $the_board = $the_board; }
				echo '<div class="omitted' . htmlentities($the_board) . '">';
			}
			if ( $the_tag ) {
				echo '<div class="thread">All posts tagged <strong> #' . $the_tag . '</strong></div>';
			}
			include ( plugin_dir_path(__FILE__) . '/regular_board_posting_checkflood.php' );
			if ( count ( $get_current_board ) > 0 && $protocol == 'boards' || $protocol == 'tags' && $the_board || $this_thread || $the_tag && $protocol == 'boards' ) {
				if ( !$user_logged_in && $require_logged == 1 ) {
					echo '<div class="thread"><p>You are not logged in.</p></div>';
				} elseif ( !$user_logged_in && $require_logged == 0 || $user_logged_in ) {
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
								echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
							}
						}
						if ( $this_thread && $threadexists == 1 ) {
							echo '<p class="nav_tools">';
							if ( $thisboard ) {
								echo '<a class="load_link" href="' . $current_page . '">Return</a>';
							} elseif ( $the_board ) {
								echo '<a class="load_link" href="' . $current_page . '?b=' . $the_board . '">Return</a>';
							} elseif ( $thread_board ) {
								echo '<a class="load_link" href="' . $current_page . '?b=' . $thread_board . '">Return</a>';
							} else {
								echo '<a class="load_link" href="' . $current_page . '">Return</a>';
							}								
							echo '<a href="#top">Top</a><a class="reload" xdata="' . $this_thread .'" data="' . $current_page . '?t=' . $this_thread . '">Refresh</a>
							</p>';
						}
					}
				}
			}
			if ( $the_tag || $the_board ) {
				echo '</div>';
			}			
		}
		
		if ( $this_area == 'post' ) { 
		
		
		include ( plugin_dir_path(__FILE__) . '/regular_board_area_post.php' ); 
		
		
		} elseif ( $this_area == 'gallery' && !$the_board || $this_area == 'replies' || $this_area == 'topics' && !$the_board || $this_area == 'all' ) {
			echo '<div class="omitted' . $this_area . '">';
			echo '<div class="thread_container">';
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
			echo '</div></div>';
		}
		elseif ( $this_area == 'news'                     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_news.php'  ); }
		elseif ( $enable_blog && $this_area == 'blog'     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_blog.php'  ); }
		elseif ( $this_area == 'mod'                      ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_mod.php'   ); }
		elseif ( $this_area == 'stuff'                    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_stuff.php' ); }
		elseif ( $this_area == 'messages' && $user_exists ) { include ( plugin_dir_path(__FILE__) . '/regular_board_messages.php'   ); } 
		elseif ( $this_area == 'logout' && $user_exists   ) { include ( plugin_dir_path(__FILE__) . '/regular_board_logout.php'     ); }



	echo '</div>';
	include ( plugin_dir_path(__FILE__) . '/regular_board_template_sidebar.php' );
	echo '</div>';
	if ( $regular_board_footer ) {
		echo '<footer>' . $regular_board_footer . '</footer>';
	}
	
	echo '</div>';
	}

 /**
  ** Page title
  ** (1) Set the title of the page using javascript
  **/
  if ( $the_board ) { 
	$page_title = $the_board; 
  }
  if ( $the_tag ) { 
	$page_title = $the_tag; 
  }
  if ( $this_area ) { 
	$page_title = $this_area; 
  }
  if ( $this_user ) { 
	$page_title = $this_user; 
  }
  if ( $this_thread ) { 
	$page_title = $this_thread; 
  }
  if ( $post_title ) {
	$page_title = $post_title;
  }
  if ( $page_title ) {
	echo '<script type="text/javascript">
	document.title = \'' . $page_title . '\';
	</script>';
  }
  
}