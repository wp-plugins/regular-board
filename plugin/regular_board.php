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
		include ( plugin_dir_path(__FILE__) . '/regular_board_navigation_elements.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_information.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_loop_queries.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_board_wipe.php' );
		include ( plugin_dir_path(__FILE__) . '/regular_board_determine_mod.php' );

		echo '<div class="boardAll"><div class="spacer">' . $banner . $navigation;
		
		if ( $userisbanned ) { include ( plugin_dir_path(__FILE__) . '/regular_board_posting_userbanned.php' ); }
		
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
		
		include ( plugin_dir_path(__FILE__) . '/regular_board_template_sidebar.php' );
		
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
		}
		
		elseif ( $this_area == 'post' ) { include ( plugin_dir_path(__FILE__) . '/regular_board_area_post.php' ); }
		elseif ( $this_area == 'gallery' || $this_area == 'replies' || $this_area == 'topics' || $this_area == 'subscribed' || $this_area == 'following' ) {
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
	echo '</div></div></div>';
	}
}