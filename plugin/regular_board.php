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
			$regular_board_posts_select;
	
	if ( $ipaddress !== false ) { 
	
		include ( plugin_dir_path(__FILE__) . '/regular_board_strings.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_user_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_loop_queries.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_wipe.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_determine_mod.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_navigation_elements.php' );

		echo '<div class="boardAll"><div class="spacer">' . $banner . $navigation;
		
		if ( $userisbanned ) { include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' ); }
		
		echo '<div class="right-half">';
		
		if ( $nothing_is_here ) {
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
		} elseif ( $this_area == 'create' ) {
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
					$regular_board_board = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_boards WHERE board_shortname = '$board_shortname'" );
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
				if     ( $this_board  ) { $data = $current_page . '?b=' . $the_board; }
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
		} elseif ( $this_area == 'submit' ) {
			if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
				include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
			} else {
				include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
			}
		} elseif ( $this_area == 'deleted' ) {
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
		} 
		
		elseif ( $this_area == 'editpost' && $user_exists && $this_thread ) { include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php'    ); }
		elseif ( $this_area == 'options' && $user_exists                  ) { include ( plugin_dir_path(__FILE__) . '/regular_board_user_options.php' ); } 
		elseif ( $this_area == 'history' && $user_exists || $this_user    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_profile_loop.php' ); }
		elseif ( $this_area == 'stats'                                    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_board_stats.php'  ); } 

		elseif ( $the_board || $this_thread || $the_tag ) {
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
							echo '<p>';
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
		}
		
		if ( $this_area == 'post' ) { 
		
		
		include ( plugin_dir_path(__FILE__) . '/regular_board_area_post.php' ); 
		
		
		} elseif ( $this_area == 'gallery' && !$the_board || $this_area == 'replies' || $this_area == 'topics' || $this_area == 'subscribed' || $this_area == 'following' ) {
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
			echo '</div>';
		}
		elseif ( $this_area == 'news'                     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_news.php'  ); }
		elseif ( $enable_blog && $this_area == 'blog'     ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_blog.php'  ); }
		elseif ( $this_area == 'mod'                      ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_mod.php'   ); }
		elseif ( $this_area == 'stuff'                    ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_stuff.php' ); }
		elseif ( $this_area == 'messages' && $user_exists ) { include ( plugin_dir_path(__FILE__) . '/regular_board_messages.php'   ); } 
		elseif ( $this_area == 'logout' && $user_exists   ) { include ( plugin_dir_path(__FILE__) . '/regular_board_logout.php'     ); }
	echo '</div>';
	include ( plugin_dir_path(__FILE__) . '/regular_board_template_sidebar.php' );
	echo '</div></div>';
	}
}